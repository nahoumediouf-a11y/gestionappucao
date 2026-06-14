@extends('layouts.dashboard')

@section('title', 'Statistiques — Recouvrement UCAO')

@section('page-title', 'Statistiques globales')

@section('page-content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total encaissé</div>
                <div class="fs-4 fw-bold text-success">{{ number_format($stats['total_encaisse'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Paiements enregistrés</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_paiements'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Paiements validés</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_paiements_valides'] }} / {{ $stats['nb_paiements'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total des impayés</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($stats['total_impayes'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Étudiants débiteurs</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_debiteurs'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Engagements en cours</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_engagements_en_cours'] }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
