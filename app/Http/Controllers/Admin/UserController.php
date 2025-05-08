<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $users = User::where('role', 'user')
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->orderBy('name')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with(['projects', 'tasks' => function ($query) {
            $query->with('project', 'tags')->latest();
        }])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function tasks($id)
    {
        $user = User::findOrFail($id);
        $tasks = $user->tasks()
            ->with('project', 'tags')
            ->latest()
            ->paginate(10);

        return view('admin.users.tasks', compact('user', 'tasks'));
    }
}
