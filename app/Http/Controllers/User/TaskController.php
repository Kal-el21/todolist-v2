<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function __construct()
     {
         $this->middleware('auth');
         $this->middleware('role:user');
     }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->tasks()->with('project', 'tags');

        // Filter berdasarkan status
        if ($request->has('status')) {
            if ($request->status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_completed', false);
            }
        }

        // Filter berdasarkan proyek
        if ($request->has('project') && $request->project) {
            $query->where('project_id', $request->project);
        }

        // Filter berdasarkan prioritas
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->paginate(10);
        $projects = $user->projects()->get();

        return view('user.tasks.index', compact('tasks', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $projects = $user->projects()->get();
        $tags = $user->tags()->get();

        return view('user.tasks.create', compact('projects', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
        ]);

        $user = Auth::user();

        // Verifikasi bahwa proyek milik user
        $project = Project::findOrFail($request->project_id);
        if ($project->user_id !== $user->id) {
            return redirect()->route('user.tasks.index')
                ->with('error', 'You do not have permission to add tasks to this project.');
        }

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->priority = $request->priority;
        $task->project_id = $request->project_id;
        $task->user_id = $user->id;
        $task->save();

        // Menangani tag
        if ($request->has('tags')) {
            $tagIds = [];
            $tagNames = explode(',', $request->tags);

            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName, 'user_id' => $user->id]
                    );
                    $tagIds[] = $tag->id;
                }
            }

            $task->tags()->sync($tagIds);
        }

        return redirect()->route('user.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $task = $user->tasks()->with('project', 'tags')->findOrFail($id);

        return view('user.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        $projects = $user->projects()->get();
        $tags = $user->tags()->get();

        // Mengambil tag yang sudah ada untuk task ini
        $taskTags = $task->tags->pluck('name')->implode(',');

        return view('user.tasks.edit', compact('task', 'projects', 'tags', 'taskTags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
        ]);

        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);

        // Verifikasi bahwa proyek milik user
        $project = Project::findOrFail($request->project_id);
        if ($project->user_id !== $user->id) {
            return redirect()->route('user.tasks.index')
                ->with('error', 'You do not have permission to add tasks to this project.');
        }

        $task->title = $request->title;
        $task->description = $request->description;
        $task->due_date = $request->due_date;
        $task->priority = $request->priority;
        $task->project_id = $request->project_id;
        $task->save();

        // Menangani tag
        if ($request->has('tags')) {
            $tagIds = [];
            $tagNames = explode(',', $request->tags);

            foreach ($tagNames as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName, 'user_id' => $user->id]
                    );
                    $tagIds[] = $tag->id;
                }
            }

            $task->tags()->sync($tagIds);
        } else {
            $task->tags()->detach();
        }

        return redirect()->route('user.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        $task->delete();

        return redirect()->route('user.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function toggleComplete($id)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);

        if ($task->is_completed) {
            $task->markAsIncomplete();
            $message = 'Task marked as incomplete.';
        } else {
            $task->markAsCompleted();
            $message = 'Task marked as completed.';
        }

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }
}
