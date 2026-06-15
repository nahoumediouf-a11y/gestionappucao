@extends('layouts.app')

@section('title', 'Réinitialiser le mot de passe — SIGE UCAO')

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
                                    <i class="bi bi-shield-lock-fill fs-2"></i>
                                    <span class="fs-5 fw-semibold">UCAO Saint Michel</span>
                                </div>
                                <h1 class="h3 fw-bold mb-3">Nouveau mot de passe</h1>
                                <p class="mb-0 opacity-75">
                                    Choisissez un nouveau mot de passe pour votre compte.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-7 auth-form-panel p-4 p-md-5">
                            <h2 class="h4 mb-1">Réinitialiser mon mot de passe</h2>
                            <p class="text-muted mb-4">Le mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre</p>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.update') }}" novalidate>
                                @csrf

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input
                                            type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            id="email"
                                            name="email"
                                            value="{{ old('email', $email) }}"
                                            required
                                            autofocus
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input
                                            type="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            id="password"
                                            name="password"
                                            placeholder="••••••••"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                        <input
                                            type="password"
                                            class="form-control"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            placeholder="••••••••"
                                            required
                                        >
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-ucao w-100 py-2">
                                    <i class="bi bi-check-circle me-2"></i>Réinitialiser le mot de passe
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
