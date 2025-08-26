@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Liste des clients</h1>

    <!-- Formulaire de recherche -->
    <form method="GET" action="{{ route('clients.index') }}" class="mb-3 row g-2 align-items-center">
        <div class="col-md-3 col-12">
            <input type="text" name="search" class="form-control" placeholder="Recherche..." value="{{ request('search') }}">
        </div>

        @if(auth()->user()->isAdmin())
            <div class="col-md-3 col-12">
                <select name="filiale_id" class="form-select">
                    <option value="">— Toutes les filiales —</option>
                    @foreach($filiales as $filiale)
                        <option value="{{ $filiale->id }}" {{ request('filiale_id') == $filiale->id ? 'selected' : '' }}>
                            {{ $filiale->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
        </div>
    </form>

    <!-- Bouton ajouter -->
    <a href="{{ route('clients.create') }}" class="btn btn-primary mb-4"><i class="fas fa-plus"></i> Ajouter un client</a>

    <!-- Message de succès -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Vue desktop -->
    <div class="d-none d-md-block table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Responsable</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>RCCM</th>
                    @if(auth()->user()->isAdmin())
                        <th>Filiale</th>
                    @endif
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->responsable }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->address }}</td>
                        <td>{{ $client->rccm }}</td>
                        @if(auth()->user()->isAdmin())
                            <td>{{ $client->filiale->nom ?? '—' }}</td>
                        @endif
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-info btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning btn-sm" title="Modifier"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('clients.show', $client) }}#proformas" class="btn btn-secondary btn-sm" title="Proformas"><i class="fas fa-file-invoice"></i></a>
                                <a href="{{ route('clients.show', $client) }}#factures" class="btn btn-secondary btn-sm" title="Factures"><i class="fas fa-file-invoice-dollar"></i></a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer ce client ?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="text-center">Aucun client trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Vue mobile -->
    <div class="d-md-none">
        @forelse($clients as $client)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ $client->name }}</h5>
                    <p class="mb-1"><strong>Responsable:</strong> {{ $client->responsable }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $client->email }}</p>
                    <p class="mb-1"><strong>Téléphone:</strong> {{ $client->phone }}</p>
                    <p class="mb-1"><strong>Adresse:</strong> {{ $client->address }}</p>
                    <p class="mb-1"><strong>RCCM:</strong> {{ $client->rccm }}</p>
                    @if(auth()->user()->isAdmin())
                        <p class="mb-1"><strong>Filiale:</strong> {{ $client->filiale->nom ?? '—' }}</p>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <a href="{{ route('clients.show', $client) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('clients.show', $client) }}#proformas" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i></a>
                        <a href="{{ route('clients.show', $client) }}#factures" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice-dollar"></i></a>
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer ce client ?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p>Aucun client trouvé.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $clients->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
