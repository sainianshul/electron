<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class GestureController extends Controller
{
    /**
     * Display the AI Gesture & Finger Control interface.
     */
    public function index()
    {
        $user = auth()->user();

        // Load devices with pins and appliances
        $devices = $user->isAdmin()
            ? Device::with(['pins.appliance', 'user'])->latest()->get()
            : $user->devices()->with(['pins.appliance'])->latest()->get();

        return view('user.gesture', compact('devices'));
    }
}
