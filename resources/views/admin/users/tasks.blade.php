<!-- resources/views/admin/users/tasks.blade.php -->
@extends('adminlte::page')

@section('title', 'User Tasks')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Tasks for {{ $user->name }}</h1>
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Back to User
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Tasks</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search" id="taskSearch">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="btn-group mb-3">
                <button type="button" class="btn btn-default task-filter" data-filter="all">All</button>
                <button type="button" class="btn btn-default task-filter" data-filter="pending">Pending</button>
                <button type="button" class="btn btn-default task-filter" data-filter="completed">Completed</button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="tasksTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr data-status="{{ $task->is_completed ? 'completed' : 'pending' }}">
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
                                    @foreach($task->tags as $tag)
                                        <span class="badge badge-secondary">{{ $tag->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if($task->is_completed)
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $task->created_at->format('M d, Y') }}</td>
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
            {{ $tasks->links() }}
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tasks by Project</h3>
                </div>
                <div class="card-body">
                    <canvas id="tasksByProjectChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tasks by Priority</h3>
                </div>
                <div class="card-body">
                    <canvas id="tasksByPriorityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Task search
        $('#taskSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#tasksTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Task filter
        $('.task-filter').on('click', function() {
            var filter = $(this).data('filter');
            $('.task-filter').removeClass('active');
            $(this).addClass('active');

            if (filter === 'all') {
                $('#tasksTable tbody tr').show();
            } else {
                $('#tasksTable tbody tr').hide();
                $('#tasksTable tbody tr[data-status="' + filter + '"]').show();
            }
        });

        // Tasks by Project Chart
        var projectsData = {
            @foreach($user->projects as $project)
                '{{ $project->name }}': {
                    total: {{ $project->tasks->count() }},
                    completed: {{ $project->tasks->where('is_completed', true)->count() }},
                    color: '{{ $project->color }}'
                },
            @endforeach
        };

        var projectLabels = Object.keys(projectsData);
        var projectTotals = projectLabels.map(function(label) { return projectsData[label].total; });
        var projectCompleted = projectLabels.map(function(label) { return projectsData[label].completed; });
        var projectColors = projectLabels.map(function(label) { return projectsData[label].color; });

        var tasksByProjectCtx = document.getElementById('tasksByProjectChart').getContext('2d');
        var tasksByProjectChart = new Chart(tasksByProjectCtx, {
            type: 'bar',
            data: {
                labels: projectLabels,
                datasets: [
                    {
                        label: 'Total Tasks',
                        data: projectTotals,
                        backgroundColor: projectColors,
                    },
                    {
                        label: 'Completed Tasks',
                        data: projectCompleted,
                        backgroundColor: projectColors.map(function(color) {
                            return color + '80'; // Add transparency
                        }),
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Tasks by Priority Chart
        var priorityData = {
            'High': {{ $tasks->where('priority', 'high')->count() }},
            'Medium': {{ $tasks->where('priority', 'medium')->count() }},
            'Low': {{ $tasks->where('priority', 'low')->count() }}
        };

        var priorityColors = {
            'High': '#dc3545',
            'Medium': '#ffc107',
            'Low': '#17a2b8'
        };

        var tasksByPriorityCtx = document.getElementById('tasksByPriorityChart').getContext('2d');
        var tasksByPriorityChart = new Chart(tasksByPriorityCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(priorityData),
                datasets: [{
                    data: Object.values(priorityData),
                    backgroundColor: Object.keys(priorityData).map(function(key) {
                        return priorityColors[key];
                    }),
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
