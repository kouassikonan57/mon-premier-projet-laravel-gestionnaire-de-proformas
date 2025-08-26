@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Liste des filiales</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Formulaire de recherche --}}
    <form method="GET" action="{{ route('filiales.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="search" class="form-label">Nom ou Code</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Rechercher...">
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </div>
    </form>

    <a href="{{ route('filiales.create') }}" class="btn btn-success mb-4">
        <i class="fas fa-plus"></i> Créer une filiale
    </a>

    {{-- Affichage conditionnel --}}
    @if($filiales->count())

        {{-- Desktop --}}
        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Logo</th>
                            <th>Nom</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Footer PDF</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filiales as $filiale)
                            <tr>
                                <td>
                                    @if($filiale->logo_path)
                                        <img src="{{ asset($filiale->logo_path) }}" alt="Logo" height="40">
                                    @else
                                        <span class="text-muted">Aucun</span>
                                    @endif
                                </td>
                                <td>{{ $filiale->nom }}</td>
                                <td>{{ $filiale->code }}</td>
                                <td>{{ $filiale->description }}</td>
                                <td style="max-width: 200px;">
                                    <small class="text-muted">{{ Str::limit($filiale->footer_text, 80) }}</small>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('filiales.edit', $filiale->id) }}" class="btn btn-warning btn-sm mb-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('filiales.destroy', $filiale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm mb-1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="d-md-none">
            @foreach($filiales as $filiale)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-2">{{ $filiale->nom }}</h5>

                        @if($filiale->logo_path)
                            <img src="{{ asset($filiale->logo_path) }}" alt="Logo" class="img-fluid mb-2" style="max-height: 50px;">
                        @endif

                        <p class="card-text mb-1"><strong>Code:</strong> {{ $filiale->code }}</p>
                        <p class="card-text mb-2"><strong>Description:</strong> {{ $filiale->description }}</p>
                        @if($filiale->footer_text)
                            <p class="card-text"><strong>Footer PDF:</strong><br><small class="text-muted">{{ Str::limit($filiale->footer_text, 100) }}</small></p>
                        @endif

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('filiales.edit', $filiale->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('filiales.destroy', $filiale->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
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
        <div class="mt-4">
            {{ $filiales->appends(request()->query())->links() }}
        </div>

    @else
        {{-- Aucun résultat --}}
        <div class="alert alert-info">
            Aucune filiale trouvée.
        </div>
    @endif
</div>
@endsection
