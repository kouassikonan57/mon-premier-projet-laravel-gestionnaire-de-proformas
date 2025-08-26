@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Journal des actions</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Action</th>
                <th>Type</th>
                <th>ID</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr @class([
                    'table-success' => strtolower($log->action) === 'création',
                    'table-warning' => strtolower($log->action) === 'modification',
                    'table-danger'  => strtolower($log->action) === 'suppression',
                ])>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->entity_type }}</td>
                    <td>{{ $log->entity_id }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $logs->links('pagination::bootstrap-4') }}
</div>
@endsection
