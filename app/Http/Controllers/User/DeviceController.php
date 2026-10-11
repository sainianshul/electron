<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Appliance;
use App\Models\Device;
use App\Models\DevicePin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceController extends Controller
{
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
        $appliances = Appliance::where('status', Appliance::STATUS_ACTIVE)->get();

        return view('user.devices.show', compact('device', 'appliances'));
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

        $request->validate([
            'state' => ['required', 'string'],
        ]);

        $pin->update([
            'current_state' => $request->state,
        ]);

        return response()->json([
            'success' => true,
            'pin' => $pin->pin_name,
            'state' => $pin->current_state,
            'message' => "Pin {$pin->pin_name} state updated."
        ]);
    }

    public function assignPin(Request $request, Device $device, DevicePin $pin)
    {
        abort_if($device->user_id !== auth()->id() || $pin->device_id !== $device->id, 403);

        $request->validate([
            'appliance_id' => ['nullable', 'exists:appliances,id'],
            'custom_name' => ['nullable', 'string', 'max:255'],
        ]);

        $pin->update([
            'appliance_id' => $request->appliance_id,
            'custom_name' => $request->custom_name,
            'is_assigned' => !empty($request->appliance_id),
        ]);

        return redirect()->route('user.devices.show', $device->id)
            ->with('success', "Pin {$pin->pin_name} appliance settings updated.");
    }
}
