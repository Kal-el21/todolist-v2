<!-- resources/views/admin/users/show.blade.php -->
@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>User Details: {{ $user->name }}</h1>
        <a href="{{ route('admin.users') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="https://adminlte.io/themes/v3/dist/img/user4-128x128.jpg"
                             alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center">{{ $user->name }}</h3>
                    <p class="text-muted text-center">{{ ucfirst($user->role) }}</p>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ $user->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Projects</b> <a class="float-right">{{ $user->projects->count() }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Tasks</b> <a class="float-right">{{ $user->tasks->count() }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Completed Tasks</b> <a class="float-right">{{ $user->tasks->where('is_completed', true)->count() }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Last Login</b> <a class="float-right">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</a>
                        </li>
                    </ul>
                    <a href="{{ route('admin.user.tasks', $user->id) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-tasks"></i> View Tasks
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Projects</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Tasks</th>
                                <th>Completion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->projects as $project)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $project->color }}">
                                            &nbsp;
                                        </span>
                                        {{ $project->name }}
                                    </td>
                                    <td>{{ Str::limit($project->description, 50) }}</td>
                                    <td>{{ $project->tasks->count() }}</td>
                                    <td>
                                        @php
                                            $total = $project->tasks->count();
                                            $completed = $project->tasks->where('is_completed', true)->count();
                                            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                                        @endphp
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="badge bg-success">{{ $percentage }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No projects found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Tasks</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Due Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->tasks->take(5) as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $task->project->color }}">
                                            {{ $task->project->name }}
                                        </span>
                                    </td>
                                    <td>{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</td>
                                    <td>
                                        @if($task->priority == 'high')
                                            <span class="badge badge-danger">High</span>
                                        @elseif($task->priority == 'medium')
                                            <span class="badge badge-warning">Medium</span>
                                        @else
                                            <span class="badge badge-info">Low</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->is_completed)
                                            <span class="badge badge-success">Completed</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No tasks found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.user.tasks', $user->id) }}" class="btn btn-sm btn-default">View All Tasks</a>
                </div>
            </div>
        </div>
    </div>
@stop
