<!-- resources/views/admin/statistics.blade.php -->
@extends('adminlte::page')

@section('title', 'Statistics')

@section('content_header')
    <h1>Statistics</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-1"></i>
                        Monthly Task Activity
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        User Productivity
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="userProductivityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
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
                    <h3 class="card-title">
                        <i class="fas fa-tasks mr-1"></i>
                        Task Completion Rate
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="taskCompletionRateChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Task Creation by Day of Week
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="taskCreationByDayChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Monthly Activity Chart
        var monthlyActivityCtx = document.getElementById('monthlyActivityChart').getContext('2d');
        var monthlyActivityChart = new Chart(monthlyActivityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($monthlyData, 'month')) !!},
                datasets: [
                    {
                        label: 'Created Tasks',
                        data: {!! json_encode(array_column($monthlyData, 'created')) !!},
                        borderColor: '#3490dc',
                        backgroundColor: 'rgba(52, 144, 220, 0.1)',
                        pointBackgroundColor: '#3490dc',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#3490dc',
                        borderWidth: 2,
                        fill: true
                    },
                    {
                        label: 'Completed Tasks',
                        data: {!! json_encode(array_column($monthlyData, 'completed')) !!},
                        borderColor: '#38c172',
                        backgroundColor: 'rgba(56, 193, 114, 0.1)',
                        pointBackgroundColor: '#38c172',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#38c172',
                        borderWidth: 2,
                        fill: true
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

        // User Productivity Chart
        var userProductivityCtx = document.getElementById('userProductivityChart').getContext('2d');
        var userProductivityChart = new Chart(userProductivityCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($userProductivity, 'name')) !!},
                datasets: [
                    {
                        label: 'Total Tasks',
                        data: {!! json_encode(array_column($userProductivity, 'tasks')) !!},
                        backgroundColor: '#3490dc',
                    },
                    {
                        label: 'Completed Tasks',
                        data: {!! json_encode(array_column($userProductivity, 'completed')) !!},
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
        var projectDistributionCtx = document.getElementById('projectDistributionChart').getContext('2d');
        var projectDistributionChart = new Chart(projectDistributionCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_column($projectDistribution, 'name')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($projectDistribution, 'tasks')) !!},
                    backgroundColor: [
                        '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc',
                        '#d2d6de', '#6c757d', '#343a40', '#007bff', '#6610f2'
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Task Completion Rate Chart
        var taskCompletionRateCtx = document.getElementById('taskCompletionRateChart').getContext('2d');
        var taskCompletionRateChart = new Chart(taskCompletionRateCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [
                        {{ \App\Models\Task::where('is_completed', true)->count() }},
                        {{ \App\Models\Task::where('is_completed', false)->count() }}
                    ],
                    backgroundColor: ['#38c172', '#f6993f'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // Task Creation by Day Chart
        var taskCreationByDayCtx = document.getElementById('taskCreationByDayChart').getContext('2d');
        var taskCreationByDayChart = new Chart(taskCreationByDayCtx, {
            type: 'bar',
            data: {
                labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                datasets: [{
                    label: 'Tasks Created',
                    data: [
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 2')->count() }},
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 3')->count() }},
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 4')->count() }},
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 5')->count() }},
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 6')->count() }},
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 7')->count() }},
                        {{ \App\Models\Task::whereRaw('DAYOFWEEK(created_at) = 1')->count() }}
                    ],
                    backgroundColor: '#6f42c1',
                }]
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
    });
</script>
@stop
