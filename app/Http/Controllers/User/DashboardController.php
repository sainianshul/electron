<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $devices = $user->devices()->with(['pins.appliance'])->latest()->get();

        $stats = [
            'total_devices' => $devices->count(),
            'online_devices' => $devices->where('is_online', true)->count(),
            'offline_devices' => $devices->where('is_online', false)->count(),
            'active_switches' => $devices->pluck('pins')->flatten()->where('is_assigned', true)->count(),
        ];

        return view('user.dashboard', compact('devices', 'stats'));
    }
}
