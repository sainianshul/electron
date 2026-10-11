<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DevicePin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $devices = $user->devices()->with(['pins.appliance'])->latest()->get();

        $assignedPins = DevicePin::whereHas('device', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('is_assigned', true)
            ->with(['appliance', 'device'])
            ->get();

        $stats = [
            'total_devices' => $devices->count(),
            'online_devices' => $devices->where('is_online', true)->count(),
            'offline_devices' => $devices->where('is_online', false)->count(),
            'active_appliances' => $assignedPins->count(),
            'active_on' => $assignedPins->filter(fn($p) => $p->pin_type === 'PWM' ? (int)$p->current_state > 0 : $p->current_state === '1')->count(),
        ];

        return view('user.dashboard', compact('devices', 'assignedPins', 'stats'));
    }
}
