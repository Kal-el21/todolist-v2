<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:user');
    }

    public function index()
    {
        $user = Auth::user();

        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('is_completed', true)->count();
        $pendingTasks = $user->tasks()->where('is_completed', false)->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $projects = $user->projects()->withCount('tasks')->get();

        // Data untuk chart aktivitas mingguan
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $weeklyActivity = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weeklyActivity[] = [
                'day' => $date->format('D'),
                'date' => $date->format('Y-m-d'),
                'completed' => 0,
                'created' => 0,
            ];
        }

        $createdTasks = $user->tasks()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get();

        $weeklyCompletedTasks = $user->tasks()
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->where('is_completed', true)
            ->select(DB::raw('DATE(completed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->get();

        foreach ($createdTasks as $task) {
            $index = array_search($task->date, array_column($weeklyActivity, 'date'));
            if ($index !== false) {
                $weeklyActivity[$index]['created'] = $task->count;
            }
        }

        foreach ($weeklyCompletedTasks as $task) {
            $index = array_search($task->date, array_column($weeklyActivity, 'date'));
            if ($index !== false) {
                $weeklyActivity[$index]['completed'] = $task->count;
            }
        }

        // Data untuk chart distribusi proyek
        $projectDistribution = $user->projects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->get()
            ->map(function ($project) {
                return [
                    'name' => $project->name,
                    'tasks' => $project->tasks_count,
                    'completed' => $project->completed_tasks_count,
                    'color' => $project->color,
                ];
            })
            ->toArray();

        // Tugas terbaru
        $recentTasks = $user->tasks()
            ->with('project', 'tags')
            ->latest()
            ->limit(5)
            ->get();

        // Tugas yang akan datang
        $upcomingTasks = $user->tasks()
            ->with('project')
            ->where('is_completed', false)
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('user.dashboard', compact(
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'completionRate',
            'projects',
            'weeklyActivity',
            'projectDistribution',
            'recentTasks',
            'upcomingTasks'
        ));
    }
}
