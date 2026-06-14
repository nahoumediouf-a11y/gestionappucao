@extends('layouts.dashboard')

@section('title', 'Paiements — Recouvrement UCAO')

@section('page-title', 'Historique des paiements')

@section('page-actions')
    <a href="{{ route('comptabilite.paiements.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Enregistrer un paiement
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Rechercher par matricule ou nom..." value="{{ $q }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Rechercher</button>
            </div>
        </form>
    </div>
</div>

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
                    <th>Statut</th>
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
                        <td>{{ ucfirst(str_replace('_', ' ', $paiement->mode_paiement)) }}</td>
                        <td>
                            <span class="badge bg-{{ $paiement->statut === 'valide' ? 'success' : 'secondary' }}">
                                {{ ucfirst($paiement->statut) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('comptabilite.paiements.recu', $paiement) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-receipt"></i> Reçu
                            </a>
                            <a href="{{ route('comptabilite.paiements.edit', $paiement) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aucun paiement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
