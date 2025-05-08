<!-- resources/views/user/tasks/show.blade.php -->
@extends('adminlte::page')

@section('title', 'Task Details')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Task Details</h1>
        <a href="{{ route('user.tasks.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to Tasks
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $task->title }}</h3>
                    <div class="card-tools">
                        @if($task->is_completed)
                            <span class="badge badge-success">Completed</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p>{{ $task->description ?: 'No description provided.' }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Project</h5>
                            <p>
                                <span class="badge" style="background-color: {{ $task->project->color }}">
                                    {{ $task->project->name }}
                                </span>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h5>Priority</h5>
                            <p>
                                @if($task->priority == 'high')
                                    <span class="badge badge-danger">High</span>
                                @elseif($task->priority == 'medium')
                                    <span class="badge badge-warning">Medium</span>
                                @else
                                    <span class="badge badge-info">Low</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Due Date</h5>
                            <p>
                                @if($task->due_date)
                                    {{ $task->due_date->format('M d, Y') }}
                                    @if(!$task->is_completed && $task->due_date->isPast())
                                        <span class="badge badge-danger">Overdue</span>
                                    @endif
                                @else
                                    <span class="text-muted">No due date</span>
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h5>Tags</h5>
                            <p>
                                @forelse($task->tags as $tag)
                                    <span class="badge badge-secondary">{{ $tag->name }}</span>
                                @empty
                                    <span class="text-muted">No tags</span>
                                @endforelse
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group">
                        <a href="{{ route('user.tasks.edit', $task->id) }}" class="btn btn-info">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('user.tasks.toggle-complete', $task->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn {{ $task->is_completed ? 'btn-warning' : 'btn-success' }}">
                                @if($task->is_completed)
                                    <i class="fas fa-times"></i> Mark Incomplete
                                @else
                                    <i class="fas fa-check"></i> Mark Complete
                                @endif
                            </button>
                        </form>
                        <form action="{{ route('user.tasks.destroy', $task->id) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Information</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table">
                        <tr>
                            <th>Created</th>
                            <td>{{ $task->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $task->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($task->is_completed)
                        <tr>
                            <th>Completed At</th>
                            <td>{{ $task->completed_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Related Tasks</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @php
                            $relatedTasks = \App\Models\Task::where('project_id', $task->project_id)
                                ->where('id', '!=', $task->id)
                                ->latest()
                                ->limit(5)
                                ->get();
                        @endphp

                        @forelse($relatedTasks as $relatedTask)
                            <li class="list-group-item">
                                <a href="{{ route('user.tasks.show', $relatedTask->id) }}">
                                    {{ $relatedTask->title }}
                                </a>
                                @if($relatedTask->is_completed)
                                    <span class="badge badge-success float-right">Completed</span>
                                @else
                                    <span class="badge badge-warning float-right">Pending</span>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item">No related tasks found</li>
                        @endforelse
                    </ul>
                </div>
            </div>
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
