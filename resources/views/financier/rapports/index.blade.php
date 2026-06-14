@extends('layouts.dashboard')

@section('title', 'Rapports financiers — Recouvrement UCAO')

@section('page-title', 'Rapports financiers')

@section('page-content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="text-muted small">Total général encaissé</div>
        <div class="fs-3 fw-bold text-success">{{ number_format($total, 0, ',', ' ') }} FCFA</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Répartition par mode de paiement</h3>
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Mode</th><th>Nb</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($parMode as $ligne)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $ligne->mode_paiement)) }}</td>
                                <td>{{ $ligne->nb }}</td>
                                <td class="text-end fw-bold">{{ number_format($ligne->total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Aucune donnée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Répartition par mois</h3>
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Mois</th><th>Nb</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($parMois as $ligne)
                            <tr>
                                <td>{{ $ligne->mois }}</td>
                                <td>{{ $ligne->nb }}</td>
                                <td class="text-end fw-bold">{{ number_format($ligne->total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Aucune donnée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
