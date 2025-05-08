<!-- resources/views/user/projects/show.blade.php -->
@extends('adminlte::page')

@section('title', 'Project Details')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Project: {{ $project->name }}</h1>
        <div>
            <a href="{{ route('user.projects.edit', $project->id) }}" class="btn btn-info">
                <i class="fas fa-edit"></i> Edit Project
            </a>
            <a href="{{ route('user.projects.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header" style="background-color: {{ $project->color }}; color: white;">
                    <h3 class="card-title">Project Information</h3>
                </div>
                <div class="card-body">
                    <p><strong>Description:</strong> {{ $project->description ?: 'No description provided.' }}</p>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Tasks</span>
                        <span>{{ $tasks->total() }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Completed Tasks</span>
                        <span>{{ $tasks->where('is_completed', true)->count() }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Pending Tasks</span>
                        <span>{{ $tasks->where('is_completed', false)->count() }}</span>
                    </div>

                    <div class="progress">
                        @php
                            $total = $tasks->total();
                            $completed = $project->tasks()->where('is_completed', true)->count();
                            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $percentage }}%
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('user.tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Add New Task
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Task Statistics</h3>
                </div>
                <div class="card-body">
                    <canvas id="taskPriorityChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tasks</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-default task-filter" data-filter="all">All</button>
                            <button type="button" class="btn btn-sm btn-default task-filter" data-filter="pending">Pending</button>
                            <button type="button" class="btn btn-sm btn-default task-filter" data-filter="completed">Completed</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Due Date</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                    <tr data-status="{{ $task->is_completed ? 'completed' : 'pending' }}">
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
                                        <td colspan="5" class="text-center">No tasks found for this project</td>
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
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Task filter
        $('.task-filter').on('click', function() {
            var filter = $(this).data('filter');
            $('.task-filter').removeClass('active');
            $(this).addClass('active');

            if (filter === 'all') {
                $('tr[data-status]').show();
            } else {
                $('tr[data-status]').hide();
                $('tr[data-status="' + filter + '"]').show();
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
                        {{ $project->tasks()->where('priority', 'high')->count() }},
                        {{ $project->tasks()->where('priority', 'medium')->count() }},
                        {{ $project->tasks()->where('priority', 'low')->count() }}
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
