@extends('layouts.app')

@section('title', 'Mot de passe oublié — SIGE UCAO')

@section('content')
<div class="auth-wrapper position-relative">
    <span class="theme-toggle fs-4 text-white position-absolute top-0 end-0 m-3" onclick="ucaoToggleTheme()" title="Changer de thème">
        <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
    </span>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <a href="{{ route('login') }}" class="d-inline-flex align-items-center gap-2 text-white text-decoration-none mb-3 opacity-75">
                    <i class="bi bi-arrow-left"></i> Retour à la connexion
                </a>
                <div class="card auth-card">
                    <div class="row g-0">
                        <div class="col-md-5 auth-brand d-flex flex-column justify-content-center">
                            <div class="px-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-key-fill fs-2"></i>
                                    <span class="fs-5 fw-semibold">UCAO Saint Michel</span>
                                </div>
                                <h1 class="h3 fw-bold mb-3">Mot de passe oublié ?</h1>
                                <p class="mb-0 opacity-75">
                                    Indiquez votre login : si un email est associé à votre compte,
                                    un lien de réinitialisation vous sera envoyé.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-7 auth-form-panel p-4 p-md-5">
                            <h2 class="h4 mb-1">Réinitialiser mon mot de passe</h2>
                            <p class="text-muted mb-4">Saisissez votre identifiant de connexion</p>

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}" novalidate>
                                @csrf

                                <div class="mb-3">
                                    <label for="login" class="form-label">Login</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input
                                            type="text"
                                            class="form-control @error('login') is-invalid @enderror"
                                            id="login"
                                            name="login"
                                            value="{{ old('login') }}"
                                            placeholder="Ex : etudiant1"
                                            required
                                            autofocus
                                        >
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-ucao w-100 py-2">
                                    <i class="bi bi-send me-2"></i>Envoyer le lien de réinitialisation
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
