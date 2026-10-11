<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Appliances\ApplianceDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Appliance\StoreApplianceRequest;
use App\Http\Requests\Admin\Appliance\UpdateApplianceRequest;
use App\Models\Appliance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplianceController extends Controller
{
    public function index(ApplianceDataTable $dataTable)
    {
        return $dataTable->render('admin.appliances.index');
    }

    public function data(ApplianceDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function create()
    {
        return view('admin.appliances.create');
    }

    public function store(StoreApplianceRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('appliances', 'public');
        }

        Appliance::create($data);

        return redirect()->route('admin.appliances.index')
            ->with('success', 'Appliance item added to catalog successfully.');
    }

    public function show(Appliance $appliance)
    {
        $appliance->load(['creator', 'pins.device.user']);
        return view('admin.appliances.show', compact('appliance'));
    }

    public function edit(Appliance $appliance)
    {
        return view('admin.appliances.edit', compact('appliance'));
    }

    public function update(UpdateApplianceRequest $request, Appliance $appliance)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($appliance->image && Storage::disk('public')->exists($appliance->image)) {
                Storage::disk('public')->delete($appliance->image);
            }
            $data['image'] = $request->file('image')->store('appliances', 'public');
        }

        $appliance->update($data);

        return redirect()->route('admin.appliances.index')
            ->with('success', 'Appliance item updated successfully.');
    }

    public function destroy(Appliance $appliance)
    {
        if ($appliance->image && Storage::disk('public')->exists($appliance->image)) {
            Storage::disk('public')->delete($appliance->image);
        }

        $appliance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appliance deleted successfully.'
        ]);
    }

    public function updateStatus(Request $request, Appliance $appliance)
    {
        $request->validate([
            'status' => 'required|integer|in:' . implode(',', array_keys(Appliance::getStatusList())),
        ]);

        $appliance->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }
}
