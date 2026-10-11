<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DevicePin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $devices = $user->isAdmin()
            ? Device::with(['pins.appliance', 'user'])->latest()->get()
            : $user->devices()->with(['pins.appliance'])->latest()->get();

        $assignedPins = DevicePin::where('is_assigned', true)
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->whereHas('device', function ($dq) use ($user) {
                    $dq->where('user_id', $user->id);
                });
            })
            ->with(['appliance', 'device.user'])
            ->latest('updated_at')
            ->get();

        $totalAssigned = $assignedPins->count();
        $totalOn = $assignedPins->filter(function ($p) {
            return $p->pin_type === 'PWM' ? ((int)$p->current_state > 0) : ($p->current_state === '1');
        })->count();

        $stats = [
            'total_devices' => $devices->count(),
            'online_devices' => $devices->where('is_online', true)->count(),
            'offline_devices' => $devices->where('is_online', false)->count(),
            'active_appliances' => $totalAssigned,
            'active_on' => $totalOn,
            'active_off' => $totalAssigned - $totalOn,
        ];

        return view('user.dashboard', compact('devices', 'assignedPins', 'stats'));
    }
}
