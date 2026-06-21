@extends('layouts.dashboard')

@section('title', 'Tous les paiements — SIGE UCAO')

@section('page-title', 'Tous les paiements')
@section('page-subtitle', "Supervision et validation des opérations financières.")

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Étudiant</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Enregistré par</th>
                    <th>Validation</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paiements as $paiement)
                    <tr>
                        <td><code>{{ $paiement->reference }}</code></td>
                        <td>{{ $paiement->etudiant->user->nom_complet }} ({{ $paiement->etudiant->matricule }})</td>
                        <td>{{ $paiement->date_paiement->format('d/m/Y') }}</td>
                        <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $paiement->modeLabel() }}</td>
                        <td>{{ $paiement->agent?->nom_complet ?? '—' }}</td>
                        <td>
                            @if ($paiement->valide_par)
                                <span class="badge bg-success">
                                    Validé par {{ $paiement->validePar->nom_complet }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">En attente de validation</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @unless ($paiement->valide_par)
                                <form method="POST" action="{{ route('financier.paiements.valider', $paiement) }}" onsubmit="return confirm('Confirmer la validation du paiement {{ $paiement->reference }} d\'un montant de {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA ?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-ucao">
                                        <i class="bi bi-check-circle me-1"></i>Valider
                                    </button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $paiements->links() }}
</div>
@endsection
