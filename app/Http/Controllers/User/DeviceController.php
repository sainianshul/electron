<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\Device;
use App\Models\DevicePin;
use App\Services\ApplianceEngine;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
    protected ApplianceEngine $engine;

    public function __construct(ApplianceEngine $engine)
    {
        $this->engine = $engine;
    }

    public function index()
    {
        $devices = auth()->user()->devices()->with(['pins.appliance'])->latest()->paginate(12);
        return view('user.devices.index', compact('devices'));
    }

    public function create()
    {
        return view('user.devices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mac_address' => ['required', 'string', 'max:50', 'unique:devices,mac_address'],
            'ip_address' => ['nullable', 'ip'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['user_id'] = auth()->id();
        $data['status'] = Device::STATUS_ACTIVE;

        $device = Device::create($data);
        
        // Auto-create standard 4 Relays + 1 PWM pins
        $device->createDefaultPins();

        return redirect()->route('user.devices.show', $device->id)
            ->with('success', 'Smart Device added successfully with 5 ready channels.');
    }

    public function show(Device $device)
    {
        abort_if($device->user_id !== auth()->id(), 403, 'Unauthorized device access.');

        $device->load(['pins.appliance']);
        $appliances = Appliance::where('status', Appliance::STATUS_ACTIVE)->orderBy('name')->get();
        $capacity = $this->engine->getDeviceCapacity($device);

        return view('user.devices.show', compact('device', 'appliances', 'capacity'));
    }

    public function edit(Device $device)
    {
        abort_if($device->user_id !== auth()->id(), 403, 'Unauthorized device access.');

        return view('user.devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        abort_if($device->user_id !== auth()->id(), 403, 'Unauthorized device access.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mac_address' => ['required', 'string', 'max:50', Rule::unique('devices', 'mac_address')->ignore($device->id)],
            'ip_address' => ['nullable', 'ip'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $device->update($data);

        return redirect()->route('user.devices.show', $device->id)
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        abort_if($device->user_id !== auth()->id(), 403, 'Unauthorized device access.');

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully.'
        ]);
    }

    public function togglePin(Request $request, Device $device, DevicePin $pin)
    {
        abort_if($device->user_id !== auth()->id() || $pin->device_id !== $device->id, 403);

        $result = $this->engine->toggleState($pin, $request->input('state'));

        return response()->json($result);
    }

    public function attachAppliance(Request $request, Device $device)
    {
        abort_if($device->user_id !== auth()->id(), 403);

        $request->validate([
            'appliance_id' => ['required', 'exists:appliances,id'],
            'custom_name' => ['nullable', 'string', 'max:255'],
        ]);

        $appliance = Appliance::findOrFail($request->appliance_id);

        try {
            $pin = $this->engine->allocateAppliance($device, $appliance, $request->custom_name);
            return redirect()->route('user.devices.show', $device->id)
                ->with('success', "{$pin->custom_name} added to this device successfully!");
        } catch (Exception $e) {
            return redirect()->route('user.devices.show', $device->id)
                ->with('error', $e->getMessage());
        }
    }

    public function detachAppliance(Device $device, DevicePin $pin)
    {
        abort_if($device->user_id !== auth()->id() || $pin->device_id !== $device->id, 403);

        $name = $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance');
        $this->engine->detachAppliance($pin);

        return redirect()->route('user.devices.show', $device->id)
            ->with('success', "'{$name}' disconnected. The channel is now free.");
    }
}
