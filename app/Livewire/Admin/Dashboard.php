<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Church;
use App\Models\User;
use App\Models\Sermon;
use App\Models\CategorySermon;
use App\Models\PreacherProfile;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    //mount
    public function mount() {}
    public function render()
    {
        // Basic stats
        $stats = [
            'churches' => Church::count(),
            'active_churches' => Church::where('is_active', true)->count(),
            'inactive_churches' => Church::where('is_active', false)->count(),
            'users' => User::count(),
            'sermons' => Sermon::count(),
            'active_sermons' => Sermon::where('is_published', true)->count(),
            'categories' => CategorySermon::count(),
            'preachers' => PreacherProfile::count(),
            'active_preachers' => PreacherProfile::where('is_active', true)->count(),
            'inactive_preachers' => PreacherProfile::where('is_active', false)->count(),
        ];

        // Sermon engagement stats
        $stats['total_plays'] = DB::table('sermon_views')->count();
        $stats['total_favorites'] = DB::table('sermon_favorites')->count();
        $stats['avg_plays'] = $stats['sermons'] > 0
            ? round($stats['total_plays'] / $stats['sermons'], 1)
            : 0;

        // Recent churches
        $recent_churches = Church::with('createdBy')
            ->latest()
            ->take(5)
            ->get();

        // Recent preachers
        $recent_preachers = PreacherProfile::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Monthly sermons data for chart (last 6 months)
        $monthly_sermons = Sermon::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Monthly users data for chart (last 6 months)
        $monthly_users = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Top preachers by sermons count
        $top_preachers = PreacherProfile::withCount('sermons')
            ->having('sermons_count', '>', 0)
            ->orderBy('sermons_count', 'desc')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'recent_churches' => $recent_churches,
            'recent_preachers' => $recent_preachers,
            'monthly_sermons' => $monthly_sermons,
            'monthly_users' => $monthly_users,
            'top_preachers' => $top_preachers,
        ]);
    }
}
