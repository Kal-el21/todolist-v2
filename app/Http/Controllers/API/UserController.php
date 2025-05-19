<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;


class UserController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function profile()
    {
        $user = auth()->user();
        $user->load(['projects', 'tasks']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = auth()->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The provided current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics()
    {
        $user = auth()->user();

        $totalTasks = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('completed', true)->count();
        $pendingTasks = $totalTasks - $completedTasks;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $totalProjects = $user->projects()->count();

        // Tasks by priority
        $tasksByPriority = [
            'high' => $user->tasks()->where('priority', 'high')->count(),
            'medium' => $user->tasks()->where('priority', 'medium')->count(),
            'low' => $user->tasks()->where('priority', 'low')->count(),
        ];

        // Tasks by project
        $tasksByProject = $user->projects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('completed', true);
            }])
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'color' => $project->color,
                    'total_tasks' => $project->tasks_count,
                    'completed_tasks' => $project->completed_tasks_count,
                    'completion_rate' => $project->tasks_count > 0
                        ? round(($project->completed_tasks_count / $project->tasks_count) * 100)
                        : 0,
                ];
            });

        // Tasks by due date (upcoming week)
        $today = now()->startOfDay();
        $endOfWeek = now()->addDays(7)->endOfDay();
        $upcomingTasks = $user->tasks()
            ->where('completed', false)
            ->whereBetween('due_date', [$today, $endOfWeek])
            ->orderBy('due_date')
            ->with('project')
            ->get();

        return response()->json([
            'statistics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'total_projects' => $totalProjects,
                'tasks_by_priority' => $tasksByPriority,
                'tasks_by_project' => $tasksByProject,
                'upcoming_tasks' => TaskResource::collection($upcomingTasks),
            ],
        ]);
    }
}
