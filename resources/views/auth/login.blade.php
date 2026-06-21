@extends('layouts.app')

@section('title', 'Connexion — SIGE UCAO')

@section('content')
<div class="auth-wrapper position-relative">
    <span class="theme-toggle fs-4 text-white position-absolute top-0 end-0 m-3" onclick="ucaoToggleTheme()" title="Changer de thème">
        <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
    </span>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <a href="{{ route('welcome') }}" class="d-inline-flex align-items-center gap-2 text-white text-decoration-none mb-3 opacity-75">
                    <i class="bi bi-arrow-left"></i> Retour au choix de l'espace
                </a>
                <div class="card auth-card">
                    <div class="row g-0">
                        <div class="col-md-5 auth-brand d-flex flex-column justify-content-center">
                            <div class="px-2">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    @if ($espace === 'etudiant')
                                        <i class="bi bi-person-badge fs-2"></i>
                                    @else
                                        <i class="bi bi-shield-lock-fill fs-2"></i>
                                    @endif
                                    <span class="fs-5 fw-semibold">UCAO Saint Michel</span>
                                </div>
                                @if ($espace === 'etudiant')
                                    <h1 class="h3 fw-bold mb-3">Espace Étudiant</h1>
                                    <p class="mb-4 opacity-75">
                                        Connectez-vous pour consulter vos notes, votre bulletin, votre emploi du temps,
                                        vos absences et le suivi de vos paiements.
                                    </p>
                                    <ul class="list-unstyled small opacity-90 mb-0">
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Notes et bulletin</li>
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Emploi du temps</li>
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Absences</li>
                                        <li><i class="bi bi-check-circle me-2"></i>Paiements et reçus</li>
                                    </ul>
                                @else
                                    <h1 class="h3 fw-bold mb-3">Système de contrôle de recouvrement</h1>
                                    <p class="mb-4 opacity-75">
                                        Accès réservé au personnel autorisé. Connectez-vous avec vos identifiants.
                                    </p>
                                    <ul class="list-unstyled small opacity-90 mb-0">
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Administrateur</li>
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Agent comptable</li>
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Agent de recouvrement</li>
                                        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Responsable financier</li>
                                        <li><i class="bi bi-check-circle me-2"></i>Professeur</li>
                                    </ul>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-7 auth-form-panel p-4 p-md-5">
                            <h2 class="h4 mb-1">Connexion</h2>
                            <p class="text-muted mb-4">Entrez vos identifiants pour accéder à votre espace</p>

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

                            <form method="POST" action="{{ route('login') }}" novalidate>
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
                                            placeholder="Votre identifiant"
                                            required
                                            autofocus
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
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

                                <div class="mb-3">
                                    <label for="captcha" class="form-label">Vérification : combien font {{ $captcha['question'] }} ?</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                        <input
                                            type="number"
                                            class="form-control @error('captcha') is-invalid @enderror"
                                            id="captcha"
                                            name="captcha"
                                            placeholder="Votre réponse"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                                    </div>
                                    <a href="{{ route('password.request') }}" class="small">Mot de passe oublié ?</a>
                                </div>

                                <button type="submit" class="btn btn-ucao w-100 py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                </button>
                            </form>

                            @if ($espace === 'etudiant')
                            <hr class="my-4">
                            <p class="small text-muted mb-0">
                                Pas encore de compte ? Contactez l'administration.
                            </p>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
