<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Devices\DeviceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Device\StoreDeviceRequest;
use App\Http\Requests\Admin\Device\UpdateDeviceRequest;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
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
        return view('admin.devices.show', compact('device'));
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
