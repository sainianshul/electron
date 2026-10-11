<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\Device;
use App\Models\DevicePin;
use App\Services\ApplianceEngine;
use Exception;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    protected ApplianceEngine $engine;

    public function __construct(ApplianceEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Display the appliances dashboard / Box UI grid.
     */
    public function index()
    {
        $user = auth()->user();
        $devices = $user->devices()->with(['pins.appliance'])->latest()->get();
        $hasDevices = $devices->isNotEmpty();

        // If user has no devices, return the view immediately showing the empty state
        if (!$hasDevices) {
            return view('user.appliances.index', [
                'hasDevices' => false,
                'devices' => collect(),
                'assignedPins' => collect(),
                'catalogAppliances' => collect(),
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'offline' => 0,
                ],
            ]);
        }

        // Fetch all assigned appliances across all user's devices
        $assignedPins = DevicePin::whereHas('device', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('is_assigned', true)
            ->with(['appliance', 'device'])
            ->latest('updated_at')
            ->get();

        // Catalog of available appliances for the modal
        $catalogAppliances = Appliance::where('status', Appliance::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        // Stats summary
        $totalAssigned = $assignedPins->count();
        $totalOn = $assignedPins->filter(function ($pin) {
            return $pin->pin_type === 'PWM' ? ((int)$pin->current_state > 0) : ($pin->current_state === '1');
        })->count();

        $stats = [
            'total' => $totalAssigned,
            'on' => $totalOn,
            'off' => $totalAssigned - $totalOn,
            'devices_count' => $devices->count(),
        ];

        return view('user.appliances.index', compact(
            'hasDevices',
            'devices',
            'assignedPins',
            'catalogAppliances',
            'stats'
        ));
    }

    /**
     * AJAX endpoint to check if selected device has free slots for selected appliance.
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'integer'],
            'appliance_id' => ['required', 'integer'],
        ]);

        $device = auth()->user()->devices()->findOrFail($request->device_id);
        $appliance = Appliance::where('status', Appliance::STATUS_ACTIVE)->findOrFail($request->appliance_id);

        $result = $this->engine->checkSlotAvailability($device, $appliance);

        return response()->json($result);
    }

    /**
     * Smartly attach an appliance to a device pin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'integer'],
            'appliance_id' => ['required', 'integer', 'exists:appliances,id'],
            'custom_name' => ['nullable', 'string', 'max:255'],
        ]);

        $device = auth()->user()->devices()->findOrFail($request->device_id);
        $appliance = Appliance::findOrFail($request->appliance_id);

        try {
            $pin = $this->engine->allocateAppliance($device, $appliance, $request->custom_name);

            $message = "🎉 {$pin->custom_name} added to {$device->name} successfully!";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'pin' => $pin,
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle the ON/OFF or PWM state of an appliance.
     */
    public function toggle(Request $request, DevicePin $pin)
    {
        abort_if($pin->device->user_id !== auth()->id(), 403, 'Unauthorized.');

        $result = $this->engine->toggleState($pin, $request->input('state'));

        return response()->json($result);
    }

    /**
     * Detach/remove an appliance from a device to free up the channel.
     */
    public function detach(Request $request, DevicePin $pin)
    {
        abort_if($pin->device->user_id !== auth()->id(), 403, 'Unauthorized.');

        $name = $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance');
        $deviceName = $pin->device->name;

        $this->engine->detachAppliance($pin);

        $message = "'{$name}' has been disconnected from {$deviceName}. The channel is now free.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
