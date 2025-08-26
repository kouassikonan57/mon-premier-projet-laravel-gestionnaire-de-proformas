@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Historique des modifications</h2>
    
    @if(auth()->user()->isAdmin())
    <div class="alert alert-info">
        <i class="fas fa-eye"></i> Vue administrateur : Affichage de toutes les filiales
    </div>
    @else
    <div class="alert alert-info">
        <i class="fas fa-building"></i> Affichage des données de votre filiale uniquement
    </div>
    @endif

    @if ($logs->count())
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    @if(auth()->user()->isAdmin())
                    <th>Filiale</th>
                    @endif
                    <th>Entité</th>
                    <th>ID</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        @if(auth()->user()->isAdmin())
                        <td>{{ $log->filiale->nom ?? 'N/A' }}</td>
                        @endif
                        <td>{{ class_basename($log->entity_type) }}</td>
                        <td>{{ $log->entity_id }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $logs->links('pagination::bootstrap-4') }}
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Aucun historique trouvé.
        </div>
    @endif
</div>
@endsection