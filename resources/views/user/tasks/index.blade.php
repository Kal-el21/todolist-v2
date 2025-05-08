<!-- resources/views/user/tasks/index.blade.php -->
@extends('adminlte::page')

@section('title', 'My Tasks')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>My Tasks</h1>
        <a href="{{ route('user.tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Task
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Tasks</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('user.tasks.index') }}" method="GET" class="row">
                <div class="col-md-3 form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Project</label>
                    <select name="project" class="form-control">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Priority</label>
                    <select name="priority" class="form-control">
                        <option value="">All Priorities</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Search</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search tasks..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Task List</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th style="width: 200px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>{{ $task->id }}</td>
                                <td>
                                    <div>
                                        @if($task->is_completed)
                                            <s>{{ $task->title }}</s>
                                        @else
                                            {{ $task->title }}
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        @foreach($task->tags as $tag)
                                            <span class="badge badge-secondary">{{ $tag->name }}</span>
                                        @endforeach
                                    </small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $task->project->color }}">
                                        {{ $task->project->name }}
                                    </span>
                                </td>
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if(!$task->is_completed && $task->due_date->isPast())
                                            <span class="badge badge-danger">Overdue</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No due date</span>
                                    @endif
                                </td>
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
                                <td>
                                    <form action="{{ route('user.tasks.toggle-complete', $task->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $task->is_completed ? 'btn-warning' : 'btn-success' }}">
                                            @if($task->is_completed)
                                                <i class="fas fa-times"></i> Mark Incomplete
                                            @else
                                                <i class="fas fa-check"></i> Mark Complete
                                            @endif
                                        </button>
                                    </form>
                                    <a href="{{ route('user.tasks.edit', $task->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('user.tasks.destroy', $task->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No tasks found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this task?')) {
                this.submit();
            }
        });
    });
</script>
@stop
