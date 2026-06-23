@extends('layouts.dashboard')

@section('title', 'Mon espace enseignant — SIGE UCAO')

@section('page-title', 'Mon espace enseignant')
@section('page-subtitle', "Votre journée, vos classes et ce qu'il reste à traiter.")

@section('page-content')
{{-- À traiter --}}
<div class="row g-3 mb-4">
    @php
        $aTraiter = [
            ['Copies à corriger', $copiesACorriger, 'bi-inbox', 'warning', route('professeur.projets.index')],
            ['Échéances à venir', $echeances->count(), 'bi-calendar-event', 'info', route('professeur.projets.index')],
            ['Propositions en attente', $propositionsEnAttente, 'bi-lightbulb', 'primary', route('professeur.propositions.index')],
            ['Mes classes', $classes->count(), 'bi-people', 'success', null],
        ];
    @endphp
    @foreach ($aTraiter as [$label, $valeur, $icon, $couleur, $url])
        <div class="col-6 col-lg-3">
            <a href="{{ $url ?? '#' }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-{{ $couleur }} bg-opacity-10 text-{{ $couleur }} p-3">
                            <i class="bi {{ $icon }} fs-4"></i>
                        </div>
                        <div>
                            <div class="h4 mb-0">{{ $valeur }}</div>
                            <div class="small text-muted">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Aujourd'hui --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3"><i class="bi bi-calendar-day me-1"></i>Aujourd'hui</h3>

                <div class="fw-semibold small text-muted mb-1">Emploi du temps</div>
                @forelse ($seancesDuJour as $s)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div>
                            <span class="badge bg-{{ $s->typeCouleur() }}">{{ $s->type }}</span>
                            {{ $s->matiere }}
                            <span class="text-muted small">· {{ $s->filiere }} {{ $s->niveau }} · salle {{ $s->salle }}</span>
                        </div>
                        <span class="text-muted small">{{ \Illuminate\Support\Str::substr($s->heure_debut, 0, 5) }}–{{ \Illuminate\Support\Str::substr($s->heure_fin, 0, 5) }}</span>
                    </div>
                @empty
                    <p class="text-muted small">Aucune séance aujourd'hui.</p>
                @endforelse

                <div class="fw-semibold small text-muted mt-3 mb-1">Cours en ligne</div>
                @forelse ($coursEnLigne as $c)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <span class="badge bg-{{ $c->statutCouleur() }}">{{ $c->statutLabel() }}</span>
                            {{ $c->titre }}
                            <span class="text-muted small">· {{ $c->debut_prevu->format('d/m H:i') }}</span>
                        </div>
                        @if ($c->statut === 'en_cours')
                            <a href="{{ route('professeur.cours.salle', $c) }}" class="btn btn-sm btn-success">Rejoindre</a>
                        @else
                            <a href="{{ route('professeur.cours.index') }}" class="btn btn-sm btn-outline-secondary">Gérer</a>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small">Aucun cours en ligne à venir.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Mes classes --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3"><i class="bi bi-people me-1"></i>Mes classes</h3>
                @forelse ($classes as $classe)
                    <a href="{{ route('professeur.classes.show', ['filiere' => $classe->filiere, 'niveau' => $classe->niveau]) }}"
                       class="d-flex justify-content-between align-items-center text-decoration-none text-reset border-bottom py-2">
                        <span>{{ $classe->filiere }} {{ $classe->niveau }}</span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                @empty
                    <p class="text-muted small">Aucune classe rattachée à votre emploi du temps.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
