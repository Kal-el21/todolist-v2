<!-- resources/views/user/projects/create.blade.php -->
@extends('adminlte::page')

@section('title', 'Create Project')

@section('content_header')
    <h1>Create New Project</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.projects.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Project Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="color">Color <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-palette"></i>
                            </span>
                        </div>
                        <input type="color" class="form-control @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', '#3490dc') }}">
                    </div>
                    @error('color')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Project</button>
                    <a href="{{ route('user.projects.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop
