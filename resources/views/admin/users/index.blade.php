<!-- resources/views/admin/users/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Users Management')

@section('content_header')
    <h1>Users Management</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Users</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search" id="userSearch">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Tasks</th>
                        <th>Completion Rate</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $user->tasks_count }}</span>
                        </td>
                        <td>
                            @if($user->tasks_count > 0)
                                {{ round(($user->completed_tasks_count / $user->tasks_count) * 100) }}%
                            @else
                                0%
                            @endif
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
        <div class="card-footer clearfix">
            {{ $users->links() }}
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        $('#userSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#usersTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
@stop
