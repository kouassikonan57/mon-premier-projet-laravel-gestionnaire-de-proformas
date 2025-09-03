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
                <div class="card-header">{{ __('Confirmer mot de passe') }}</div>

                <div class="card-body">
                    {{ __('Veuillez confirmer votre mot de passe avant de continuer.') }}

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Mot de passe') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Confirmer mot de passe') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Mot de passe oublié?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
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
