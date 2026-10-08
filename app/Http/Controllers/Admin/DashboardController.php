<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function stats()
    {
        // Cache the dashboard stats for 5 minutes
        $data = Cache::remember('admin.dashboard.stats', 300, function () {
            
            // Top Level Stats
            $totalUsers = User::count();
            
            // Recent Users for the table
            $recentUsers = User::users()
                ->latest()
                ->take(5)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'initials' => strtoupper(substr($user->name, 0, 2)),
                        'phone' => $user->phone,
                        'joined' => $user->created_at->format('d M Y'),
                        'status' => $user->status,
                        'status_name' => $user->status_name,
                        'status_color' => $user->status === User::STATUS_ACTIVE ? 'success' : ($user->status === User::STATUS_PENDING ? 'warning' : 'danger')
                    ];
                });

            // Chart Data: Users added in the last 30 days
            $chartDates = [];
            $chartCounts = [];
            
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            // Get counts grouped by date
            $userCounts = User::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');
                
            // Fill missing dates with 0
            for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->format('Y-m-d');
                $chartDates[] = $date->format('d M');
                $chartCounts[] = $userCounts[$dateStr] ?? 0;
            }

            return [
                'total_users' => number_format($totalUsers),
                'recent_users' => $recentUsers,
                'chart' => [
                    'dates' => $chartDates,
                    'counts' => $chartCounts
                ]
            ];
        });

        return response()->json($data);
    }
}
