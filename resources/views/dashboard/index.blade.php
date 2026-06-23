@extends('layouts.dashboard')

@section('title', 'Tableau de bord — SIGE UCAO')

@section('page-title', 'Tableau de bord')
@section('page-subtitle', "Modules accessibles selon votre rôle.")

@section('page-content')
@if (! empty($apercu))
    {{-- En-tête personnalisé étudiant --}}
    <div class="card border-0 shadow-sm mb-3 ucao-fade-up" style="background: linear-gradient(135deg, var(--ucao-blue) 0%, #2563eb 100%);">
        <div class="card-body text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h4 mb-1">Bonjour {{ $user->prenom }} 👋</h2>
                <p class="mb-0 opacity-75 small">
                    <i class="bi bi-mortarboard me-1"></i>{{ $user->etudiant->filiere }} {{ $user->etudiant->niveau }}
                    · Matricule {{ $user->etudiant->matricule }}
                </p>
            </div>
            <i class="bi bi-mortarboard-fill fs-1 opacity-25 d-none d-sm-block"></i>
        </div>
    </div>

    {{-- Bandeau d'aperçu --}}
    <div class="row g-3 mb-4">
        @php
            $soldeOk = $apercu['solde'] <= 0;
        @endphp
        <div class="col-6 col-lg-3">
            <a href="{{ route('etudiant.paiements.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-cash-coin me-1"></i>Solde restant</div>
                        <div class="h5 mb-0 text-{{ $soldeOk ? 'success' : 'danger' }}">
                            {{ number_format($apercu['solde'], 0, ',', ' ') }} FCFA
                        </div>
                        @if (! $soldeOk)
                            <span class="badge bg-danger mt-1">À payer</span>
                        @else
                            <span class="badge bg-success mt-1">À jour</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('etudiant.bulletin.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-bar-chart me-1"></i>Moyenne générale</div>
                        <div class="h5 mb-0 text-{{ $apercu['moyenne'] >= 10 ? 'success' : ($apercu['moyenne'] > 0 ? 'danger' : 'secondary') }}">
                            {{ $apercu['moyenne'] > 0 ? $apercu['moyenne'].' /20' : '—' }}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ $apercu['coursEnCours'] ? route('etudiant.cours.index') : route('etudiant.edt.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-clock me-1"></i>Prochaine séance</div>
                        @if ($apercu['coursEnCours'])
                            <div class="fw-semibold small">{{ \Illuminate\Support\Str::limit($apercu['coursEnCours']->titre, 22) }}</div>
                            <span class="badge bg-success mt-1">En ligne maintenant</span>
                        @elseif ($apercu['prochaineSeance'])
                            <div class="fw-semibold small">{{ \Illuminate\Support\Str::limit($apercu['prochaineSeance']->matiere, 22) }}</div>
                            <div class="text-muted small">{{ \Illuminate\Support\Str::substr($apercu['prochaineSeance']->heure_debut, 0, 5) }} · salle {{ $apercu['prochaineSeance']->salle }}</div>
                        @else
                            <div class="text-muted small">Aucune aujourd'hui</div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('etudiant.projets.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-kanban me-1"></i>Travaux à rendre</div>
                        <div class="h5 mb-0 text-{{ $apercu['aRendre'] ? 'warning' : 'success' }}">{{ $apercu['aRendre'] }}</div>
                        @if ($apercu['prochaineEcheance'])
                            <div class="text-muted small">Prochaine : {{ $apercu['prochaineEcheance']->date_limite->format('d/m') }}</div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    </div>
@endif

<div class="row g-3">
    @forelse ($modules as $module)
        @php
            $href      = isset($module['url']) ? $module['url'] : route($module['route']);
            $highlight = ! empty($module['highlight']);
        @endphp
        <div class="col-md-6 col-lg-4">
            <a href="{{ $href }}" class="text-decoration-none text-reset">
                <div class="card h-100 border-0 shadow-sm {{ $highlight ? 'border border-danger border-2' : '' }}"
                     style="{{ $highlight ? 'box-shadow: 0 0 0 3px rgba(220,53,69,.15) !important;' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-{{ $module['color'] }} {{ $highlight ? 'bg-opacity-25' : 'bg-opacity-10' }} text-{{ $module['color'] }} p-3">
                                <i class="bi {{ $module['icon'] }} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="h6 mb-1 {{ $highlight ? 'text-danger fw-bold' : '' }}">
                                    {{ $module['label'] }}
                                    @if (! empty($module['badge']))
                                        <span class="badge bg-{{ $highlight ? 'danger' : 'danger' }} rounded-pill ms-1">{{ $module['badge'] }}</span>
                                    @endif
                                </h3>
                                @if ($highlight)
                                    <p class="small text-danger mb-0 fw-semibold">
                                        <i class="bi bi-arrow-right-circle me-1"></i>Cliquez pour payer maintenant
                                    </p>
                                @else
                                    <p class="small text-muted mb-0">Module autorisé pour {{ $user->role->label() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">Aucun module accessible.</div>
        </div>
    @endforelse
</div>
@endsection
