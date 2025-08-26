@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier la Proforma</h1>

    @include('proformas._form', [
        'route' => route('proformas.update', $proforma),
        'method' => 'PUT',
        'proforma' => $proforma,
        'clients' => $clients,
        'catalogArticles' => $catalogArticles, // ✅ AJOUTER CECI
    ])
</div>
@endsection
