<!-- resources/views/user/tags/show.blade.php -->
@extends('adminlte::page')

@section('title', 'Tag Details')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Tag: {{ $tag->name }}</h1>
        <div>
            <a href="{{ route('user.tags.edit', $tag->id) }}" class="btn btn-info">
                <i class="fas fa-edit"></i> Edit Tag
            </a>
            <a href="{{ route('user.tags.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Tags
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tasks with this tag</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>
                                    <div>
                                        @if($task->is_completed)
                                            <s>{{ $task->title }}</s>
                                        @else
                                            {{ $task->title }}
                                        @endif
                                    </div>
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
                                    <div class="btn-group">
                                        <a href="{{ route('user.tasks.show', $task->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('user.tasks.edit', $task->id) }}" class="btn btn-sm btn-default">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('user.tasks.toggle-complete', $task->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $task->is_completed ? 'btn-warning' : 'btn-success' }}">
                                                <i class="fas {{ $task->is_completed ? 'fa-times' : 'fa-check' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No tasks found with this tag</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            {{ $tasks->links() }}
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Status</h3>
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Priority</h3>
                </div>
                <div class="card-body">
                    <canvas id="taskPriorityChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Task Status Chart
        var taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
        var taskStatusChart = new Chart(taskStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [
                        {{ $tasks->where('is_completed', true)->count() }},
                        {{ $tasks->where('is_completed', false)->count() }}
                    ],
                    backgroundColor: ['#28a745', '#ffc107'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Task Priority Chart
        var taskPriorityCtx = document.getElementById('taskPriorityChart').getContext('2d');
        var taskPriorityChart = new Chart(taskPriorityCtx, {
            type: 'pie',
            data: {
                labels: ['High', 'Medium', 'Low'],
                datasets: [{
                    data: [
                        {{ $tasks->where('priority', 'high')->count() }},
                        {{ $tasks->where('priority', 'medium')->count() }},
                        {{ $tasks->where('priority', 'low')->count() }}
                    ],
                    backgroundColor: ['#dc3545', '#ffc107', '#17a2b8'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    });
</script>
@stop
