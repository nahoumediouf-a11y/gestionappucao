@extends('layouts.app')

@section('title', 'Bienvenue — SIGE UCAO')

@section('content')
<div class="auth-wrapper position-relative">
    <span class="theme-toggle fs-4 text-white position-absolute top-0 end-0 m-3" onclick="ucaoToggleTheme()" title="Changer de thème">
        <i id="ucao-theme-icon" class="bi bi-moon-stars"></i>
    </span>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center text-white mb-4 ucao-fade-up">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        <i class="bi bi-mortarboard-fill fs-1"></i>
                        <span class="fs-3 fw-semibold">UCAO Saint Michel</span>
                    </div>
                    <h1 class="h2 fw-bold mb-2 text-uppercase">SIGE UCAO</h1>
                    <p class="opacity-75 mb-0">Système Intégré de Gestion des Étudiants — choisissez votre espace pour continuer</p>
                </div>

                @php $delai = 0; @endphp
                @foreach ($familles as $famille => $espaces)
                    <div class="d-flex align-items-center gap-2 text-white-50 text-uppercase small fw-semibold mt-4 mb-2">
                        <span>{{ $famille }}</span>
                        <span class="flex-grow-1 border-top border-light opacity-25"></span>
                    </div>
                    <div class="row g-3">
                        @foreach ($espaces as $espace)
                            @php $delai += 0.05; @endphp
                            <div class="col-sm-6 col-lg-4">
                                <a href="{{ route('login', ['espace' => $espace['cle']]) }}"
                                   class="text-decoration-none"
                                   aria-label="Accéder à l'espace {{ $espace['label'] }}">
                                    <div class="card auth-card h-100 ucao-fade-up ucao-choice-card" style="animation-delay:{{ $delai }}s">
                                        <div class="card-body text-center p-4">
                                            <i class="bi {{ $espace['icone'] }} fs-1 text-{{ $espace['couleur'] }} mb-3 d-block ucao-choice-icon"></i>
                                            <h2 class="h5 fw-bold mb-2">{{ $espace['label'] }}</h2>
                                            <p class="text-muted small mb-3">{{ $espace['baseline'] }}</p>
                                            <span class="btn btn-ucao btn-sm px-4">
                                                <i class="bi bi-box-arrow-in-right me-2"></i>Accéder
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <p class="text-center text-white-50 small mt-4 mb-0">
                    Pas encore de compte ? Contactez l'administration pour en créer un.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .ucao-choice-card {
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .ucao-choice-card:hover {
        transform: translateY(-6px) scale(1.015);
        box-shadow: 0 1.25rem 2.5rem rgba(0, 0, 0, .2);
    }
    .ucao-choice-card:hover .ucao-choice-icon {
        transform: translateY(-4px) scale(1.1);
    }
    .ucao-choice-icon {
        transition: transform .25s ease, color .25s ease;
    }
</style>
@endsection
