@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Journal des actions</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Proforma</th>
                <th>Facture</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr @class([
                    'table-success' => strtolower($log->action) === 'création',
                    'table-warning' => strtolower($log->action) === 'modification',
                    'table-danger' => strtolower($log->action) === 'suppression',
                ])>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $log->user->name ?? 'N/A' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>
                        @if($log->proforma)
                            <a href="{{ route('proformas.show', $log->proforma) }}">{{ $log->proforma->reference }}</a>
                        @endif
                    </td>
                    <td>
                        @if($log->facture)
                            <a href="{{ route('factures.show', $log->facture) }}">{{ $log->facture->reference }}</a>
                        @endif
                    </td>
                    <td>{{ $log->description }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Aucune action enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $logs->links('pagination::bootstrap-4') }}
</div>
@endsection
