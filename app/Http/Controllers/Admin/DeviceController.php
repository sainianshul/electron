<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Devices\DeviceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Device\StoreDeviceRequest;
use App\Http\Requests\Admin\Device\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\User;
use App\Models\Appliance;
use App\Models\DevicePin;
use App\Services\ApplianceEngine;
use Exception;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    protected ApplianceEngine $engine;

    public function __construct(ApplianceEngine $engine)
    {
        $this->engine = $engine;
    }

    public function index(DeviceDataTable $dataTable)
    {
        return $dataTable->render('admin.devices.index');
    }

    public function data(DeviceDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.devices.create', compact('users'));
    }

    public function store(StoreDeviceRequest $request)
    {
        $device = Device::create($request->validated());
        
        // Auto-create standard 4 Relays + 1 PWM pins for NodeMCU
        $device->createDefaultPins();

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device created with default NodeMCU pins successfully.');
    }

    public function show(Device $device)
    {
        $device->load(['user', 'pins.appliance']);
        $appliances = Appliance::where('status', Appliance::STATUS_ACTIVE)->orderBy('name')->get();
        $capacity = $this->engine->getDeviceCapacity($device);

        return view('admin.devices.show', compact('device', 'appliances', 'capacity'));
    }

    public function togglePin(Request $request, Device $device, DevicePin $pin)
    {
        abort_if($pin->device_id !== $device->id, 404);

        $result = $this->engine->toggleState($pin, $request->input('state'));

        return response()->json($result);
    }

    public function attachAppliance(Request $request, Device $device)
    {
        $request->validate([
            'appliance_id' => ['required', 'exists:appliances,id'],
            'custom_name' => ['nullable', 'string', 'max:255'],
        ]);

        $appliance = Appliance::findOrFail($request->appliance_id);

        try {
            $pin = $this->engine->allocateAppliance($device, $appliance, $request->custom_name);
            return redirect()->route('admin.devices.show', $device->id)
                ->with('success', "{$pin->custom_name} added to this device successfully!");
        } catch (Exception $e) {
            return redirect()->route('admin.devices.show', $device->id)
                ->with('error', $e->getMessage());
        }
    }

    public function detachAppliance(Device $device, DevicePin $pin)
    {
        abort_if($pin->device_id !== $device->id, 404);

        $name = $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance');
        $this->engine->detachAppliance($pin);

        return redirect()->route('admin.devices.show', $device->id)
            ->with('success', "'{$name}' disconnected. The channel is now free.");
    }

    public function edit(Device $device)
    {
        $users = User::orderBy('name')->get();
        return view('admin.devices.edit', compact('device', 'users'));
    }

    public function update(UpdateDeviceRequest $request, Device $device)
    {
        $device->update($request->validated());

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Device $device)
    {
        $request->validate([
            'status' => 'required|integer|in:' . implode(',', array_keys(Device::getStatusList())),
        ]);

        $device->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }
}
