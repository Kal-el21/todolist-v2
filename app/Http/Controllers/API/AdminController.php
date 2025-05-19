<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Constructor to apply admin middleware
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Get all users.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Sort by name, email, or created_at
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $users = $query->withCount(['tasks', 'projects'])->paginate($request->per_page ?? 15);

        return UserResource::collection($users);
    }

    /**
     * Get a specific user.
     */
    public function showUser(User $user)
    {
        $user->loadCount(['tasks', 'projects']);
        $user->load(['projects', 'tasks' => function ($query) {
            $query->with('project');
        }]);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update a user.
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['sometimes', 'required', 'in:admin,user'],
        ]);

        $user->update($request->only(['name', 'email', 'role']));

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroyUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account',
            ], 400);
        }

        // Delete user's tasks and projects
        $user->tasks()->delete();
        $user->projects()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get user tasks.
     */
    public function userTasks(User $user, Request $request)
    {
        $query = $user->tasks();

        // Filter by completed status
        if ($request->has('completed')) {
            $query->where('completed', $request->completed);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Sort by due date, created_at, or priority
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $tasks = $query->with('project')->paginate($request->per_page ?? 15);

        return TaskResource::collection($tasks);
    }

    /**
     * Get system statistics.
     */
    public function statistics()
    {
        $totalUsers = User::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('completed', true)->count();
        $pendingTasks = $totalTasks - $completedTasks;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Users registered per month
        $usersPerMonth = User::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Tasks created per month
        $tasksPerMonth = Task::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Tasks completed per month
        $tasksCompletedPerMonth = Task::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('completed', true)
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Most active users
        $mostActiveUsers = User::withCount('tasks')
            ->orderBy('tasks_count', 'desc')
            ->limit(5)
            ->get();

        // Users with highest completion rate
        $usersWithHighestCompletionRate = User::withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
            $query->where('completed', true);
        }])
            ->having('tasks_count', '>', 0)
            ->orderByRaw('completed_tasks_count / tasks_count DESC')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $user->completion_rate = round(($user->completed_tasks_count / $user->tasks_count) * 100);
                return $user;
            });

        return response()->json([
            'statistics' => [
                'total_users' => $totalUsers,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'users_per_month' => $usersPerMonth,
                'tasks_per_month' => $tasksPerMonth,
                'tasks_completed_per_month' => $tasksCompletedPerMonth,
                'most_active_users' => UserResource::collection($mostActiveUsers),
                'users_with_highest_completion_rate' => UserResource::collection($usersWithHighestCompletionRate),
            ],
        ]);
    }
}
