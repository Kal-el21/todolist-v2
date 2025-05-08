<!-- resources/views/admin/dashboard.blade.php -->
@extends('adminlte::page')

@section('title', 'Admin Dashboard')

@section('content_header')
    <h1>Admin Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">
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
                <a href="#" class="small-box-footer">
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
                <a href="#" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalTasks }}</h3>
                    <p>Total Tasks</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
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
                        Monthly Task Activity
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="taskActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
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
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Users</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Tasks</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $user->tasks_count }}</span>
                        </td>
                        <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('admin.user.tasks', $user->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-tasks"></i> Tasks
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Task Activity Chart
        var taskActivityCtx = document.getElementById('taskActivityChart').getContext('2d');
        var taskActivityChart = new Chart(taskActivityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($chartData, 'month')) !!},
                datasets: [
                    {
                        label: 'Created Tasks',
                        data: {!! json_encode(array_column($chartData, 'created')) !!},
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
                        data: {!! json_encode(array_column($chartData, 'completed')) !!},
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
                labels: {!! json_encode($userProductivity->pluck('name')->toArray()) !!},
                datasets: [
                    {
                        label: 'Total Tasks',
                        data: {!! json_encode($userProductivity->pluck('tasks_count')->toArray()) !!},
                        backgroundColor: '#3490dc',
                    },
                    {
                        label: 'Completed Tasks',
                        data: {!! json_encode($userProductivity->pluck('completed_tasks_count')->toArray()) !!},
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
    });
</script>
@stop
