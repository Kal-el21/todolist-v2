<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function __construct()
     {
         $this->middleware('auth');
         $this->middleware('role:user');
     }

    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->get();

        return view('user.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
        ]);

        $user = Auth::user();

        $project = new Project();
        $project->name = $request->name;
        $project->description = $request->description;
        $project->color = $request->color;
        $project->user_id = $user->id;
        $project->save();

        return redirect()->route('user.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $project = $user->projects()->findOrFail($id);
        $tasks = $project->tasks()->with('tags')->latest()->paginate(10);

        return view('user.projects.show', compact('project', 'tasks'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $project = $user->projects()->findOrFail($id);

        return view('user.projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $project = $user->projects()->findOrFail($id);

        $project->name = $request->name;
        $project->description = $request->description;
        $project->color = $request->color;
        $project->save();

        return redirect()->route('user.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $project = $user->projects()->findOrFail($id);

        // Periksa apakah proyek memiliki tugas
        if ($project->tasks()->count() > 0) {
            return redirect()->route('user.projects.index')
                ->with('error', 'Cannot delete project with tasks. Please delete the tasks first.');
        }

        $project->delete();

        return redirect()->route('user.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
