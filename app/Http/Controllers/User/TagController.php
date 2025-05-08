<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
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
        $tags = $user->tags()->withCount('tasks')->get();

        return view('user.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        $tag = new Tag();
        $tag->name = $request->name;
        $tag->user_id = $user->id;
        $tag->save();

        return redirect()->route('user.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth::user();
        $tag = $user->tags()->findOrFail($id);
        $tasks = $tag->tasks()->where('user_id', $user->id)->with('project')->latest()->paginate(10);

        return view('user.tags.show', compact('tag', 'tasks'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $tag = $user->tags()->findOrFail($id);

        return view('user.tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $tag = $user->tags()->findOrFail($id);

        $tag->name = $request->name;
        $tag->save();

        return redirect()->route('user.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $tag = $user->tags()->findOrFail($id);

        // Hapus relasi dengan task
        $tag->tasks()->detach();

        $tag->delete();

        return redirect()->route('user.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
