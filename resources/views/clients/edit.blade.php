@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier le client</h1>
    @include('clients._form', [
        'route' => route('clients.update', $client),
        'method' => 'PUT',
        'client' => $client
    ])
</div>
@endsection
