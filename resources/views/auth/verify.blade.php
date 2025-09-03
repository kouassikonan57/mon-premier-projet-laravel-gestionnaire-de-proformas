@extends('layouts.app')

@section('content')
<style>
    .highlight-link {
        color: #ea580c;
        text-decoration: none;
        font-weight: bold;
    }

    .highlight-link:hover {
        color: #dc3545;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Vérifiez votre adresse e-mail') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('Un nouveau lien de vérification a été envoyé à votre adresse e-mail.') }}
                        </div>
                    @endif

                    {{ __('Avant de continuer, veuillez vérifier votre e-mail pour un lien de vérification.') }}
                    {{ __('Si vous n'avez pas reçu l'e-mail') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('cliquez ici pour demander un autre') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="text-center  py-3 text-muted">© Yadi-Group. Développé par
    <a href="https://github.com/kouassikonan57/" target="_blank" class="highlight-link">KFernand</a>
</div>
@endsection
