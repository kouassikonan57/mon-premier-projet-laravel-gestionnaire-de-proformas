@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h2>Gestion des Utilisateurs</h2>

    {{-- Formulaire de recherche --}}
    <form method="GET" action="{{ route('users.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label">Nom ou Email</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher...">
        </div>

        <div class="col-md-3">
            <label for="role" class="form-label">Rôle</label>
            <select name="role" id="role" class="form-select">
                <option value="">Tous</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
            </select>
        </div>

        <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </div>
    </form>

    <!-- {{-- Message résultats --}}
    @if($users->count() > 0)
        <p>{{ $users->total() }} utilisateur{{ $users->total() > 1 ? 's' : '' }} trouvé{{ $users->total() > 1 ? 's' : '' }}.</p>
    @else
        <p class="text-danger">Aucun utilisateur trouvé.</p>
    @endif -->
    {{-- Message résultats --}}
    @if(request()->has('search') || request()->has('role'))
        @if($users->count() > 0)
            <p>{{ $users->total() }} utilisateur{{ $users->total() > 1 ? 's' : '' }} trouvé{{ $users->total() > 1 ? 's' : '' }}.</p>
        @else
            <p class="text-danger">Aucun utilisateur trouvé.</p>
        @endif
    @endif


    {{-- Desktop --}}
    <div class="d-none d-md-block">
        <table class="table table-bordered align-middle table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Filiale</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($users->count() > 0)
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->filiale ? $user->filiale->nom : 'Aucune' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm mb-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Supprimer cet utilisateur ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr><td colspan="5" class="text-center">Aucun utilisateur trouvé.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Mobile --}}
    <div class="d-md-none">
        @foreach($users as $user)
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ $user->name }}</h5>
                <p class="card-text mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                <p class="card-text mb-1">
                    <strong>Rôle:</strong>
                    <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </p>
                <p class="card-text mb-3"><strong>Filiale:</strong> {{ $user->filiale ? $user->filiale->nom : 'Aucune' }}</p>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> 
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $users->appends(request()->query())->links() }}
    </div>
</div>
@endsection
