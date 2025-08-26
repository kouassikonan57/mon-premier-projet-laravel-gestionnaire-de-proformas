<form action="{{ $route }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    @php
        $user = auth()->user();
        $isAdmin = $user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin());
    @endphp

    <div class="form-group">
        <label for="filiale_id">Filiale *</label>

        @if($isAdmin)
            <select name="filiale_id" id="filiale_id" class="form-control" required>
                @foreach($filiales as $filiale)
                    <option value="{{ $filiale->id }}" {{ $client->filiale_id == $filiale->id ? 'selected' : '' }}>
                        {{ $filiale->nom }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="filiale_id" value="{{ $user->filiale_id }}">
            <input type="text" class="form-control" value="{{ $user->filiale->nom }}" disabled>
        @endif
    </div>

    <div class="mb-3">
        <label>Nom *</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $client->name ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label>Responsable</label>
        <input type="text" name="responsable" class="form-control" value="{{ old('responsable', $client->responsable ?? '') }}">
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $client->email ?? '') }}">
    </div>

    <div class="mb-3">
        <label>Téléphone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone ?? '') }}">
    </div>

    <div class="mb-3">
        <label>Adresse</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $client->address ?? '') }}">
    </div>

    <div class="mb-3">
        <label>Numéro RCCM</label>
        <input type="text" name="rccm" class="form-control" value="{{ old('rccm', $client->rccm ?? '') }}">
    </div>

    <button type="submit" class="btn btn-success">
        {{ $method === 'PUT' ? 'Mettre à jour' : 'Créer le client' }}
    </button>
</form>
