<?php

namespace App\Services;

use App\Models\Appliance;
use App\Models\Device;
use App\Models\DevicePin;
use Exception;

class ApplianceEngine
{
    /**
     * Check if a device has an available/empty slot that supports the given appliance.
     *
     * @param Device $device
     * @param Appliance $appliance
     * @return array
     */
    public function checkSlotAvailability(Device $device, Appliance $appliance): array
    {
        $supportedPinTypes = $this->getApplianceRequiredPinTypes($appliance);

        // Find compatible pins on this device
        $compatiblePins = $device->pins()
            ->whereIn('pin_type', $supportedPinTypes)
            ->get();

        if ($compatiblePins->isEmpty()) {
            return [
                'available' => false,
                'compatible' => false,
                'required_types' => $supportedPinTypes,
                'total_compatible' => 0,
                'free_count' => 0,
                'message' => "Device '{$device->name}' does not have hardware support for {$appliance->name} (" . implode('/', $supportedPinTypes) . " pins).",
            ];
        }

        $freePins = $compatiblePins->where('is_assigned', false);
        $freeCount = $freePins->count();

        if ($freeCount === 0) {
            $typeStr = implode('/', $supportedPinTypes);
            return [
                'available' => false,
                'compatible' => true,
                'required_types' => $supportedPinTypes,
                'total_compatible' => $compatiblePins->count(),
                'free_count' => 0,
                'message' => "No empty {$typeStr} slot available on '{$device->name}'. All {$compatiblePins->count()} channels are currently occupied.",
            ];
        }

        $nextPin = $freePins->first();

        return [
            'available' => true,
            'compatible' => true,
            'required_types' => $supportedPinTypes,
            'total_compatible' => $compatiblePins->count(),
            'free_count' => $freeCount,
            'next_pin_id' => $nextPin->id,
            'next_pin_name' => $nextPin->pin_name,
            'message' => "Slot available! {$freeCount} of {$compatiblePins->count()} compatible channels free.",
        ];
    }

    /**
     * Smartly allocate an appliance to the first available compatible pin on the device.
     *
     * @param Device $device
     * @param Appliance $appliance
     * @param string|null $customName
     * @return DevicePin
     * @throws Exception
     */
    public function allocateAppliance(Device $device, Appliance $appliance, ?string $customName = null): DevicePin
    {
        $check = $this->checkSlotAvailability($device, $appliance);

        if (!$check['available']) {
            throw new Exception($check['message']);
        }

        $pin = $device->pins()->find($check['next_pin_id']);

        if (!$pin) {
            throw new Exception("Unable to lock an available hardware pin. Please try again.");
        }

        $pin->update([
            'appliance_id' => $appliance->id,
            'custom_name' => !empty(trim((string)$customName)) ? trim($customName) : $appliance->name,
            'current_state' => $appliance->default_state ?? '0',
            'is_assigned' => true,
        ]);

        return $pin->fresh(['appliance', 'device']);
    }

    /**
     * Detach/remove an appliance from a pin, freeing up the hardware channel.
     *
     * @param DevicePin $pin
     * @return DevicePin
     */
    public function detachAppliance(DevicePin $pin): DevicePin
    {
        $pin->update([
            'appliance_id' => null,
            'custom_name' => null,
            'current_state' => '0',
            'is_assigned' => false,
        ]);

        return $pin->fresh();
    }

    /**
     * Toggle or set the state of an appliance pin.
     *
     * @param DevicePin $pin
     * @param string|null $desiredState
     * @return array
     */
    public function toggleState(DevicePin $pin, ?string $desiredState = null): array
    {
        if ($desiredState !== null) {
            $newState = $desiredState;
        } else {
            // If PWM and state is 0, default ON is 255. If state > 0, OFF is 0.
            if ($pin->pin_type === 'PWM') {
                $newState = ((int)$pin->current_state > 0) ? '0' : '255';
            } else {
                $newState = ($pin->current_state === '1') ? '0' : '1';
            }
        }

        $pin->update([
            'current_state' => $newState,
        ]);

        $applianceName = $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance');
        $isOn = ($pin->pin_type === 'PWM') ? ((int)$newState > 0) : ($newState === '1');
        $statusText = $isOn ? 'ON' : 'OFF';

        return [
            'success' => true,
            'pin_id' => $pin->id,
            'state' => $newState,
            'is_on' => $isOn,
            'appliance_name' => $applianceName,
            'message' => "{$applianceName} is now turned {$statusText}.",
        ];
    }

    /**
     * Get capacity and slot breakdown for a device.
     *
     * @param Device $device
     * @return array
     */
    public function getDeviceCapacity(Device $device): array
    {
        $pins = $device->pins;
        $total = $pins->count();
        $assigned = $pins->where('is_assigned', true)->count();
        $free = $total - $assigned;

        $digitalTotal = $pins->where('pin_type', 'DIGITAL')->count();
        $digitalFree = $pins->where('pin_type', 'DIGITAL')->where('is_assigned', false)->count();

        $pwmTotal = $pins->where('pin_type', 'PWM')->count();
        $pwmFree = $pins->where('pin_type', 'PWM')->where('is_assigned', false)->count();

        return [
            'total_slots' => $total,
            'assigned_slots' => $assigned,
            'free_slots' => $free,
            'usage_percentage' => $total > 0 ? round(($assigned / $total) * 100) : 0,
            'digital' => [
                'total' => $digitalTotal,
                'free' => $digitalFree,
                'assigned' => $digitalTotal - $digitalFree,
            ],
            'pwm' => [
                'total' => $pwmTotal,
                'free' => $pwmFree,
                'assigned' => $pwmTotal - $pwmFree,
            ],
        ];
    }

    /**
     * Resolve required pin types for an appliance.
     *
     * @param Appliance $appliance
     * @return array
     */
    protected function getApplianceRequiredPinTypes(Appliance $appliance): array
    {
        if (!empty($appliance->supported_pin_types) && is_array($appliance->supported_pin_types)) {
            return $appliance->supported_pin_types;
        }

        if (!empty($appliance->signal_type)) {
            return [$appliance->signal_type];
        }

        return ['DIGITAL'];
    }
}
