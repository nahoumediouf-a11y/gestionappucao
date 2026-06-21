@extends('layouts.dashboard')

@section('title', 'Détail de l\'engagement — SIGE UCAO')

@section('page-title', 'Détail de l\'engagement de paiement')
@section('page-subtitle', 'Étudiant : '.$engagement->etudiant->user->nom_complet.' ('.$engagement->etudiant->matricule.')')

@section('page-actions')
    <a href="{{ route('recouvrement.engagements.edit', $engagement) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil me-1"></i>Modifier
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-sm-4">Étudiant</dt>
            <dd class="col-sm-8">{{ $engagement->etudiant->user->nom_complet }} ({{ $engagement->etudiant->matricule }})</dd>

            <dt class="col-sm-4">Filière / Niveau</dt>
            <dd class="col-sm-8">{{ $engagement->etudiant->filiere }} {{ $engagement->etudiant->niveau }}</dd>

            <dt class="col-sm-4">Montant engagé</dt>
            <dd class="col-sm-8 fw-bold">{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</dd>

            <dt class="col-sm-4">Date de l'engagement</dt>
            <dd class="col-sm-8">{{ $engagement->date->format('d/m/Y') }}</dd>

            <dt class="col-sm-4">Échéance</dt>
            <dd class="col-sm-8">{{ $engagement->echeance->format('d/m/Y') }}</dd>

            <dt class="col-sm-4">Statut</dt>
            <dd class="col-sm-8">
                <span class="badge bg-{{ match($engagement->statut) {
                    'honore' => 'success',
                    'relance' => 'warning',
                    'annule' => 'secondary',
                    default => 'info',
                } }}">
                    {{ ucfirst($engagement->statut) }}
                </span>
            </dd>

            <dt class="col-sm-4">Créé par</dt>
            <dd class="col-sm-8">{{ $engagement->agent?->nom_complet ?? '—' }}</dd>

            <dt class="col-sm-4">Solde actuel de l'étudiant</dt>
            <dd class="col-sm-8 text-danger fw-bold">{{ number_format($engagement->etudiant->solde, 0, ',', ' ') }} FCFA</dd>
        </dl>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('recouvrement.engagements.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour à la liste
    </a>
</div>
@endsection
