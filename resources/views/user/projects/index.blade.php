<!-- resources/views/user/projects/index.blade.php -->
@extends('adminlte::page')

@section('title', 'My Projects')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>My Projects</h1>
        <a href="{{ route('user.projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Project
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        @forelse($projects as $project)
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background-color: {{ $project->color }}; color: white;">
                        <h3 class="card-title">{{ $project->name }}</h3>
                        <div class="card-tools">
                            <div class="dropdown">
                                <button class="btn btn-tool" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="{{ route('user.projects.show', $project->id) }}" class="dropdown-item">
                                        <i class="fas fa-eye mr-2"></i> View
                                    </a>
                                    <a href="{{ route('user.projects.edit', $project->id) }}" class="dropdown-item">
                                        <i class="fas fa-edit mr-2"></i> Edit
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('user.projects.destroy', $project->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>{{ Str::limit($project->description, 100) ?: 'No description provided.' }}</p>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Tasks</span>
                            <span>{{ $project->tasks_count }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Completed</span>
                            <span>{{ $project->completed_tasks_count }}</span>
                        </div>

                        <div class="progress">
                            @php
                                $percentage = $project->tasks_count > 0
                                    ? round(($project->completed_tasks_count / $project->tasks_count) * 100)
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $percentage }}%
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('user.tasks.index', ['project' => $project->id]) }}" class="btn btn-sm btn-default">
                            <i class="fas fa-tasks"></i> View Tasks
                        </a>
                        <a href="{{ route('user.tasks.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Task
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h4>No projects found</h4>
                        <p>Create your first project to start organizing your tasks.</p>
                        <a href="{{ route('user.projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Project
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@stop

@section('js')
<script>
    $(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this project? All associated tasks will also be deleted.')) {
                this.submit();
            }
        });
    });
</script>
@stop
