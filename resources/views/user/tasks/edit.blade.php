<!-- resources/views/user/tasks/edit.blade.php -->
@extends('adminlte::page')

@section('title', 'Edit Task')

@section('content_header')
    <h1>Edit Task</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.tasks.update', $task->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $task->title) }}" required>
                    @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="project_id">Project <span class="text-danger">*</span></label>
                            <select class="form-control @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                            @error('due_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Priority <span class="text-danger">*</span></label>
                    <div class="d-flex">
                        <div class="custom-control custom-radio mr-4">
                            <input class="custom-control-input" type="radio" id="priority_low" name="priority" value="low" {{ old('priority', $task->priority) == 'low' ? 'checked' : '' }}>
                            <label for="priority_low" class="custom-control-label">Low</label>
                        </div>
                        <div class="custom-control custom-radio mr-4">
                            <input class="custom-control-input" type="radio" id="priority_medium" name="priority" value="medium" {{ old('priority', $task->priority) == 'medium' ? 'checked' : '' }}>
                            <label for="priority_medium" class="custom-control-label">Medium</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="priority_high" name="priority" value="high" {{ old('priority', $task->priority) == 'high' ? 'checked' : '' }}>
                            <label for="priority_high" class="custom-control-label">High</label>
                        </div>
                    </div>
                    @error('priority')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags', $taskTags) }}" placeholder="Enter tags separated by commas">
                    @error('tags')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        Enter tags separated by commas (e.g., work, important, meeting)
                    </small>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="is_completed" name="is_completed" value="1" {{ old('is_completed', $task->is_completed) ? 'checked' : '' }}>
                        <label for="is_completed" class="custom-control-label">Mark as completed</label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Task</button>
                    <a href="{{ route('user.tasks.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        // Auto-refresh project dropdown if a new project is created
        if (window.opener && window.opener.document.getElementById('project_id')) {
            var projectSelect = document.getElementById('project_id');
            var openerProjectSelect = window.opener.document.getElementById('project_id');

            if (projectSelect.options.length !== openerProjectSelect.options.length) {
                window.location.reload();
            }
        }
    });
</script>
@stop
