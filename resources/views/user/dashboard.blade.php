<!-- resources/views/user/dashboard.blade.php -->
@extends('adminlte::page')

@section('title', 'User Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalTasks }}</h3>
                    <p>Total Tasks</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <a href="{{ route('user.tasks.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $completedTasks }}</h3>
                    <p>Completed Tasks</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('user.tasks.index', ['status' => 'completed']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingTasks }}</h3>
                    <p>Pending Tasks</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('user.tasks.index', ['status' => 'pending']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $completionRate }}%</h3>
                    <p>Completion Rate</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <a href="#" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Weekly Activity
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="weeklyActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-project-diagram mr-1"></i>
                        Project Distribution
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="projectDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Tasks</h3>
                    <div class="card-tools">
                        <a href="{{ route('user.tasks.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> New Task
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentTasks as $task)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">
                                            @if($task->is_completed)
                                                <s>{{ $task->title }}</s>
                                            @else
                                                {{ $task->title }}
                                            @endif
                                        </h5>
                                        <p class="text-muted mb-0">
                                            <span class="badge" style="background-color: {{ $task->project->color }}">
                                                {{ $task->project->name }}
                                            </span>
                                            @foreach($task->tags as $tag)
                                                <span class="badge badge-secondary">{{ $tag->name }}</span>
                                            @endforeach
                                        </p>
                                    </div>
                                    <div>
                                        @if($task->is_completed)
                                            <span class="badge badge-success">Completed</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">No tasks found</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('user.tasks.index') }}" class="btn btn-sm btn-default">View All Tasks</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upcoming Tasks</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($upcomingTasks as $task)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">{{ $task->title }}</h5>
                                        <p class="text-muted mb-0">
                                            <span class="badge" style="background-color: {{ $task->project->color }}">
                                                {{ $task->project->name }}
                                            </span>
                                            <span class="ml-2">
                                                <i class="far fa-calendar-alt"></i>
                                                {{ $task->due_date->format('M d, Y') }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        @if($task->priority == 'high')
                                            <span class="badge badge-danger">High</span>
                                        @elseif($task->priority == 'medium')
                                            <span class="badge badge-warning">Medium</span>
                                        @else
                                            <span class="badge badge-info">Low</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">No upcoming tasks</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('user.tasks.index') }}" class="btn btn-sm btn-default">View All Tasks</a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Weekly Activity Chart
        var weeklyActivityCtx = document.getElementById('weeklyActivityChart').getContext('2d');
        var weeklyActivityChart = new Chart(weeklyActivityCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($weeklyActivity, 'day')) !!},
                datasets: [
                    {
                        label: 'Created Tasks',
                        data: {!! json_encode(array_column($weeklyActivity, 'created')) !!},
                        backgroundColor: '#3490dc',
                    },
                    {
                        label: 'Completed Tasks',
                        data: {!! json_encode(array_column($weeklyActivity, 'completed')) !!},
                        backgroundColor: '#38c172',
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

        // Project Distribution Chart
        var projectData = {!! json_encode($projectDistribution) !!};
        var projectLabels = projectData.map(function(item) { return item.name; });
        var projectValues = projectData.map(function(item) { return item.tasks; });
        var projectColors = projectData.map(function(item) { return item.color; });

        var projectDistributionCtx = document.getElementById('projectDistributionChart').getContext('2d');
        var projectDistributionChart = new Chart(projectDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: projectLabels,
                datasets: [{
                    data: projectValues,
                    backgroundColor: projectColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@stop
