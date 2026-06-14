@extends('layouts.dashboard')

@section('title', 'Statistiques de recouvrement — Recouvrement UCAO')

@section('page-title', 'Statistiques de recouvrement')

@section('page-content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Étudiants débiteurs</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_debiteurs'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total des impayés</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($stats['total_impayes'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Engagements créés</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_engagements'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Montant total engagé</div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($stats['total_engagements'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h3 class="h6 mb-3">Engagements par statut</h3>
        <table class="table table-sm mb-0">
            <tbody>
                @forelse ($parStatut as $statut => $total)
                    <tr>
                        <td>{{ ucfirst($statut) }}</td>
                        <td class="text-end fw-bold">{{ $total }}</td>
                    </tr>
                @empty
                    <tr><td class="text-muted">Aucun engagement.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
