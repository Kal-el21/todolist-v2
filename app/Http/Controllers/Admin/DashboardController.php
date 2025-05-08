<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users = User::where('role', 'user')->withCount('tasks')->get();
        $totalUsers = $users->count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('is_completed', true)->count();
        $pendingTasks = Task::where('is_completed', false)->count();

        // Data untuk chart aktivitas bulanan
        $monthlyActivity = DB::table('tasks')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();

        $monthlyCompleted = DB::table('tasks')
            ->select(DB::raw('MONTH(completed_at) as month'), DB::raw('COUNT(*) as count'))
            ->whereYear('completed_at', date('Y'))
            ->where('is_completed', true)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();

        // Memastikan semua bulan ada dalam data
        $months = range(1, 12);
        $chartData = [];

        foreach ($months as $month) {
            $chartData[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'created' => $monthlyActivity[$month] ?? 0,
                'completed' => $monthlyCompleted[$month] ?? 0,
            ];
        }

        // Data untuk chart produktivitas user
        $userProductivity = User::where('role', 'user')
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->orderBy('tasks_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'users',
            'totalUsers',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'chartData',
            'userProductivity'
        ));
    }
}
