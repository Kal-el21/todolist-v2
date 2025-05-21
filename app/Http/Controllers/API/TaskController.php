<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::where('user_id', Auth::id());

        // Filter by completed status
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by due date
        if ($request->has('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by due date, created_at, or priority
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $tasks = $query->with(['project', 'tags'])->paginate($request->per_page ?? 15);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'project_id' => 'required|exists:projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'is_completed' => 'sometimes|boolean',
        ]);

        // Verify the project belongs to the user
        $project = Project::findOrFail($request->project_id);
        if ($project->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to add tasks to this project',
            ], 403);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'is_completed' => false,
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
        ]);

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $task->tags()->sync($tagIds);
        }

        $task->load(['project', 'tags']);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to view this task',
            ], 403);
        }

        $task->load(['project', 'tags']);

        return response()->json([
            'task' => new TaskResource($task),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to update this task',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high',
            'is_completed' => 'sometimes|boolean',
            'project_id' => 'sometimes|required|exists:projects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        // If project_id is being updated, verify the project belongs to the user
        if ($request->has('project_id') && $request->project_id != $task->project_id) {
            $project = Project::findOrFail($request->project_id);
            if ($project->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'You do not have permission to move tasks to this project',
                ], 403);
            }
        }

        $task->update($request->only([
            'title', 'description', 'due_date', 'priority', 'is_completed', 'project_id'
        ]));

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $task->tags()->sync($tagIds);
        }

        $task->load(['project', 'tags']);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => new TaskResource($task),
        ]);
    }

    /**
     * Toggle the completed status of the task.
     */
    public function toggleComplete(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to update this task',
            ], 403);
        }

        $task->is_completed = !$task->is_completed;
        $task->save();

        return response()->json([
            'message' => $task->completed ? 'Task marked as completed' : 'Task marked as incomplete',
            'task' => new TaskResource($task),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'You do not have permission to delete this task',
            ], 403);
        }

        $task->tags()->detach();
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}
