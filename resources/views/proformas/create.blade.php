@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter une Proforma</h1>

    @include('proformas._form', [
        'route' => route('proformas.store'),
        'method' => 'POST',
        'proforma' => null,
        'clients' => $clients,
        'catalogArticles' => $catalogArticles, // ✅ AJOUTER CECI
    ])
</div>
@endsection
