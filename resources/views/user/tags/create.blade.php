<!-- resources/views/user/tags/create.blade.php -->
@extends('adminlte::page')

@section('title', 'Create Tag')

@section('content_header')
    <h1>Create New Tag</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('user.tags.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Tag Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Tag</button>
                    <a href="{{ route('user.tags.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop
