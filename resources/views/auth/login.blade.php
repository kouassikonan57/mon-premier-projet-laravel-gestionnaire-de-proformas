@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #f3f4f6; /* gris clair */
    }

    .login-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        background-color: #ffffff;
    }

    .login-header {
        background-color: #f97316; /* orange */
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 30px 20px;
    }

    .login-header img {
        max-height: 80px;
    }

    .login-header h4 {
        margin-top: 10px;
        font-weight: bold;
    }

    .btn-primary {
        background-color: #f97316;
        border-color: #f97316;
    }

    .btn-primary:hover {
        background-color: #ea580c;
        border-color: #ea580c;
    }

    .form-check-label,
    .form-label {
        color: #374151; /* gris foncé */
    }

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
            <div class="card login-card">
                <div class="card-header login-header text-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 80px;">
                    <h4>{{ __('Connexion') }}</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Adresse e-mail') }}</label>
                            <div class="col-md-6">
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password"
                                class="col-md-4 col-form-label text-md-end">{{ __('Mot de passe') }}</label>
                            <div class="col-md-6">
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        {{ __('Souviens-toi de moi') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                @if (Route::has('password.request'))
                                    <a style="margin-top: -20px;" class="btn btn-link text-decoration-none" href="{{ route('password.request') }}">
                                        {{ __('Mot de passe oublié ?') }}
                                    </a>
                                @endif
                                <br>
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Connexion') }}
                                </button>
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
