@extends('layouts.app')

@section('title', 'Reçu '.$paiement->reference.' — SIGE UCAO')

@push('head')
<style>
@media print {
    body * { visibility: hidden; }
    #recu-print, #recu-print * { visibility: visible; }
    #recu-print { position: absolute; inset: 0; padding: 40px; }
}
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if (session('success'))
                <div class="alert alert-success d-print-none">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <div id="recu-print" class="card border-0 shadow-sm">
                <div class="card-body p-5">

                    {{-- En-tête --}}
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h2 class="h4 mb-0 fw-bold"><i class="bi bi-bank2 me-2 text-primary"></i>UCAO Saint Michel</h2>
                            <p class="text-muted mb-0 small">Université Catholique de l'Afrique de l'Ouest</p>
                            <p class="text-muted mb-0 small">Reçu officiel de paiement de frais de scolarité</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success fs-6 mb-1">Validé</span><br>
                            @if ($paiement->etudiant->solde <= 0)
                                <span class="badge bg-success bg-opacity-75 small">
                                    <i class="bi bi-check-circle me-1"></i>Scolarité entièrement soldée
                                </span>
                            @else
                                <span class="badge bg-warning text-dark small">
                                    Solde restant : {{ number_format($paiement->etudiant->solde, 0, ',', ' ') }} FCFA
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    {{-- Références --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">N° de reçu</p>
                            <p class="fw-bold font-monospace">{{ $paiement->reference }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">Date de paiement</p>
                            <p class="fw-bold">{{ $paiement->date_paiement->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    {{-- Étudiant / Agent --}}
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Étudiant</p>
                            <p class="fw-bold mb-0">{{ $paiement->etudiant->user->nom_complet }}</p>
                            <p class="text-muted small mb-0">Matricule : {{ $paiement->etudiant->matricule }}</p>
                            <p class="text-muted small">{{ $paiement->etudiant->filiere }} — {{ $paiement->etudiant->niveau }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted small mb-1">Validé par</p>
                            <p class="fw-bold mb-0">{{ $paiement->agent?->nom_complet ?? '—' }}</p>
                            <p class="text-muted small mb-0">Mode : {{ $paiement->modeLabel() }}</p>
                            @if ($paiement->valide_le)
                                <p class="text-muted small">Le {{ \Carbon\Carbon::parse($paiement->valide_le)->format('d/m/Y à H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Montant --}}
                    <div class="rounded-3 p-4 text-center mb-4" style="background: #f0fdf4; border: 2px solid #22c55e;">
                        <p class="text-muted small mb-1">Montant payé</p>
                        <p class="display-5 fw-bold text-success mb-0">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</p>
                    </div>

                    {{-- Situation scolarité --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light text-center">
                                <p class="text-muted small mb-1">Solde restant</p>
                                <p class="fw-bold mb-0 {{ $paiement->etudiant->solde <= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format(max(0, $paiement->etudiant->solde), 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light text-center">
                                <p class="text-muted small mb-1">Situation</p>
                                <p class="fw-bold mb-0 {{ $paiement->etudiant->solde <= 0 ? 'text-success' : 'text-warning' }}">
                                    {{ $paiement->etudiant->solde <= 0 ? 'À jour ✓' : 'Partiellement réglé' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Signature --}}
                    <div class="border-top pt-4 mt-2">
                        <div class="row">
                            <div class="col-6">
                                <p class="small text-muted mb-4">Signature de l'agent comptable</p>
                                <div style="height: 40px;"></div>
                                <p class="small fw-bold border-top pt-1">{{ $paiement->agent?->nom_complet ?? '—' }}</p>
                            </div>
                            <div class="col-6 text-end">
                                <p class="small text-muted">Document généré par SIGE UCAO</p>
                                <p class="small text-muted">{{ now()->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="text-center mt-3 d-print-none d-flex justify-content-center gap-2 flex-wrap">
                <button onclick="window.print()" class="btn btn-ucao">
                    <i class="bi bi-printer me-1"></i>Imprimer / Télécharger en PDF
                </button>
                @auth
                    @if (auth()->user()->role->value === 'agent_comptable')
                        <a href="{{ route('comptabilite.paiements.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour aux paiements
                        </a>
                    @elseif (auth()->user()->role->value === 'etudiant')
                        <a href="{{ route('etudiant.paiements.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour à mes paiements
                        </a>
                    @endif
                @endauth
            </div>

        </div>
    </div>
</div>
@endsection
