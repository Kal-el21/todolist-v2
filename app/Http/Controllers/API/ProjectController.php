<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::where('user_id', Auth::id());

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Sort by name or created_at
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $projects = $query->withCount('tasks')->get();

        return response()->json(ProjectResource::collection($projects));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000', // ✅ tambahkan ini

        ]);

        $project = Project::create([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description, // ✅ simpan juga
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Project created successfully',
            'project' => new ProjectResource($project),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // Check if the project belongs to the authenticated user
        if ($project->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to view this project',
            ], 403);
        }

        $project->loadCount('tasks');
        $project->load(['tasks' => function ($query) {
            $query->with('tags');
        }]);

        return response()->json([
            'project' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        // Check if the project belongs to the authenticated user
        if ($project->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to update this project',
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string|max:1000', // ✅ tambahkan ini
        ]);

        $project->update($request->only(['name', 'color']));

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => new ProjectResource($project),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // Check if the project belongs to the authenticated user
        if ($project->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to delete this project',
            ], 403);
        }

        // Check if the project has tasks
        if ($project->tasks()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete project with tasks. Please delete or move the tasks first.',
            ], 400);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }

    /**
     * Get project statistics.
     */
    public function statistics(Project $project)
    {
        // Check if the project belongs to the authenticated user
        if ($project->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to view this project',
            ], 403);
        }

        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('completed', true)->count();
        $pendingTasks = $totalTasks - $completedTasks;
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Tasks by priority
        $tasksByPriority = [
            'high' => $project->tasks()->where('priority', 'high')->count(),
            'medium' => $project->tasks()->where('priority', 'medium')->count(),
            'low' => $project->tasks()->where('priority', 'low')->count(),
        ];

        // Tasks by due date (upcoming week)
        $today = now()->startOfDay();
        $endOfWeek = now()->addDays(7)->endOfDay();
        $upcomingTasks = $project->tasks()
            ->where('completed', false)
            ->whereBetween('due_date', [$today, $endOfWeek])
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'statistics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'tasks_by_priority' => $tasksByPriority,
                'upcoming_tasks' => TaskResource::collection($upcomingTasks),
            ],
        ]);
    }
}
