@extends('layouts.dashboard')

@section('title', 'Suivi de paiement — SIGE UCAO')

@section('page-title', 'Suivi de paiement')

@section('page-content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="text-muted small">Solde restant à payer</div>
        <div class="fs-3 fw-bold {{ $etudiant->solde > 0 ? 'text-danger' : 'text-success' }}">
            {{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Historique des paiements / Reçus</strong></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th class="text-end">Reçu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paiements as $paiement)
                    <tr>
                        <td><code>{{ $paiement->reference }}</code></td>
                        <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $paiement->modeLabel() }}</td>
                        <td class="text-end">
                            <a href="{{ route('etudiant.paiements.recu', $paiement) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Engagements de paiement</strong></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Montant</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($engagements as $engagement)
                    <tr>
                        <td>{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $engagement->echeance->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ match($engagement->statut) {
                                'honore' => 'success',
                                'relance' => 'warning',
                                'annule' => 'secondary',
                                default => 'info',
                            } }}">
                                {{ ucfirst($engagement->statut) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Aucun engagement en cours.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
