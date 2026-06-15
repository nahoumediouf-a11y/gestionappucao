@extends('layouts.app')

@section('title', 'Bienvenue — SIGE UCAO')

@section('content')
<div class="auth-wrapper position-relative">
    <span class="theme-toggle fs-4 text-white position-absolute top-0 end-0 m-3" onclick="ucaoToggleTheme()" title="Changer de thème">
        <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
    </span>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center text-white mb-4 ucao-fade-up">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <i class="bi bi-mortarboard-fill fs-1"></i>
                        <span class="fs-3 fw-semibold">UCAO Saint Michel</span>
                    </div>
                    <h1 class="h2 fw-bold mb-2">Système de contrôle de recouvrement</h1>
                    <p class="opacity-75 mb-0">Choisissez votre espace pour continuer</p>
                </div>

                <div class="row g-4 justify-content-center">
                    <div class="col-md-6">
                        <a href="{{ route('login', ['espace' => 'etudiant']) }}" class="text-decoration-none">
                            <div class="card auth-card h-100 ucao-fade-up ucao-choice-card" style="animation-delay:.05s">
                                <div class="card-body text-center p-4 p-md-5">
                                    <i class="bi bi-person-badge fs-1 text-primary mb-3 d-block ucao-choice-icon"></i>
                                    <h2 class="h4 fw-bold mb-2">Espace Étudiant</h2>
                                    <p class="text-muted mb-3">
                                        Consultez vos notes, votre bulletin, votre emploi du temps,
                                        vos absences et le suivi de vos paiements.
                                    </p>
                                    <span class="btn btn-ucao px-4">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Accéder
                                    </span>
                                </div>
                            </div>
                        </a>
                        <p class="text-center text-white small mt-2 mb-0">
                            Pas encore de compte ?
                            <a href="{{ route('register') }}" class="text-white text-decoration-underline">Créer un compte étudiant</a>
                        </p>
                    </div>

                    <div class="col-md-6">
                        <a href="{{ route('login', ['espace' => 'personnel']) }}" class="text-decoration-none">
                            <div class="card auth-card h-100 ucao-fade-up ucao-choice-card" style="animation-delay:.15s">
                                <div class="card-body text-center p-4 p-md-5">
                                    <i class="bi bi-briefcase fs-1 text-primary mb-3 d-block ucao-choice-icon"></i>
                                    <h2 class="h4 fw-bold mb-2">Espace Administration / Personnel</h2>
                                    <p class="text-muted mb-3">
                                        Administrateur, agent comptable, agent de recouvrement,
                                        responsable financier ou professeur.
                                    </p>
                                    <span class="btn btn-ucao px-4">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Accéder
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ucao-choice-card {
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .ucao-choice-card:hover {
        transform: translateY(-8px) scale(1.015);
        box-shadow: 0 1.25rem 2.5rem rgba(0, 0, 0, .2);
    }
    .ucao-choice-card:hover .ucao-choice-icon {
        transform: translateY(-4px) scale(1.1);
        color: var(--ucao-gold) !important;
    }
    .ucao-choice-icon {
        transition: transform .25s ease, color .25s ease;
        animation: ucao-float 4s ease-in-out infinite;
    }
</style>
@endsection
