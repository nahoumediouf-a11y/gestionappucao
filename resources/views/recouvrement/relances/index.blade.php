@extends('layouts.dashboard')

@section('title', 'Relances — Recouvrement UCAO')

@section('page-title', 'Relances des débiteurs')
@section('page-subtitle', 'Engagements en attente ou déjà relancés.')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Montant</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($engagements as $engagement)
                    <tr class="{{ $engagement->echeance->isPast() ? 'table-danger' : '' }}">
                        <td>{{ $engagement->etudiant->user->nom_complet }} ({{ $engagement->etudiant->matricule }})</td>
                        <td>{{ number_format($engagement->montant, 0, ',', ' ') }} FCFA</td>
                        <td>
                            {{ $engagement->echeance->format('d/m/Y') }}
                            @if ($engagement->echeance->isPast())
                                <span class="badge bg-danger">Échéance dépassée</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $engagement->statut === 'relance' ? 'warning' : 'secondary' }}">
                                {{ ucfirst($engagement->statut) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('recouvrement.relances.relancer', $engagement) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-ucao">
                                    <i class="bi bi-bell me-1"></i>Envoyer une relance
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune relance à effectuer.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
