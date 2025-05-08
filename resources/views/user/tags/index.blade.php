<!-- resources/views/user/tags/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Tags')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Tags</h1>
        <a href="{{ route('user.tags.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Tag
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if($tags->isEmpty())
                <div class="text-center py-5">
                    <h4>No tags found</h4>
                    <p>Create your first tag to help organize your tasks.</p>
                    <a href="{{ route('user.tags.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Tag
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Tasks</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tags as $tag)
                                <tr>
                                    <td>
                                        <span class="badge badge-secondary">{{ $tag->name }}</span>
                                    </td>
                                    <td>{{ $tag->tasks_count }}</td>
                                    <td>{{ $tag->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('user.tags.show', $tag->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('user.tags.edit', $tag->id) }}" class="btn btn-sm btn-default">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('user.tags.destroy', $tag->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this tag? The tag will be removed from all associated tasks.')) {
                this.submit();
            }
        });
    });
</script>
@stop
