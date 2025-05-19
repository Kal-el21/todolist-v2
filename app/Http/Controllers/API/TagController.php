<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Http\Resources\TaskResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tag::query();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Sort by name or created_at
        $sortBy = $request->sort_by ?? 'name';
        $sortDirection = $request->sort_direction ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);

        $tags = $query->withCount(['tasks' => function ($query) {
            $query->where('user_id', Auth::id());
        }])->paginate($request->per_page ?? 15);

        return TagResource::collection($tags);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        $tag->loadCount(['tasks' => function ($query) {
            $query->where('user_id', Auth::id());
        }]);

        $tag->load(['tasks' => function ($query) {
            $query->where('user_id', Auth::id())->with('project');
        }]);

        return response()->json([
            'tag' => new TagResource($tag),
        ]);
    }

    /**
     * Get tasks by tag.
     */
    public function tasks(Tag $tag, Request $request)
    {
        $query = $tag->tasks()->where('user_id', Auth::id());

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
}
