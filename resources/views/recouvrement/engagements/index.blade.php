@extends('layouts.dashboard')

@section('title', 'Engagements de paiement — SIGE UCAO')

@section('page-title', 'Engagements de paiement')

@section('page-actions')
    <a href="{{ route('recouvrement.engagements.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Créer un engagement
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($engagements as $engagement)
                    <tr>
                        <td>{{ $engagement->etudiant->user->nom_complet }} ({{ $engagement->etudiant->matricule }})</td>
                        <td>{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $engagement->date->format('d/m/Y') }}</td>
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
                        <td class="text-end">
                            <a href="{{ route('recouvrement.engagements.show', $engagement) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('recouvrement.engagements.edit', $engagement) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun engagement créé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $engagements->links() }}
</div>
@endsection
