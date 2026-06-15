@extends('layouts.app')

@section('title', 'Reçu '.$paiement->reference.' — SIGE UCAO')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h2 class="h4 mb-0"><i class="bi bi-bank2 me-2"></i>UCAO Saint Michel</h2>
                            <p class="text-muted mb-0">Reçu de paiement de frais de scolarité</p>
                        </div>
                        <span class="badge bg-success fs-6">{{ ucfirst($paiement->statut) }}</span>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Référence</p>
                            <p class="fw-bold">{{ $paiement->reference }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">Date de paiement</p>
                            <p class="fw-bold">{{ $paiement->date_paiement->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Étudiant</p>
                            <p class="fw-bold mb-0">{{ $paiement->etudiant->user->nom_complet }}</p>
                            <p class="text-muted small">{{ $paiement->etudiant->matricule }} — {{ $paiement->etudiant->filiere }} {{ $paiement->etudiant->niveau }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">Enregistré par</p>
                            <p class="fw-bold mb-0">{{ $paiement->agent?->nom_complet ?? '—' }}</p>
                            <p class="text-muted small">Mode : {{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</p>
                        </div>
                    </div>

                    <div class="bg-light rounded p-4 text-center mb-4">
                        <p class="text-muted small mb-1">Montant payé</p>
                        <p class="display-6 fw-bold text-success mb-0">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</p>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Solde restant après paiement</p>
                            <p class="fw-bold">{{ number_format($paiement->etudiant->solde, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3 d-print-none">
                <button onclick="window.print()" class="btn btn-ucao">
                    <i class="bi bi-printer me-1"></i>Imprimer / Télécharger en PDF
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
