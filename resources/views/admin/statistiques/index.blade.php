@extends('layouts.dashboard')

@section('title', 'Statistiques — Recouvrement UCAO')

@section('page-title', 'Statistiques générales')

@section('page-content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Utilisateurs</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_utilisateurs'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Étudiants</div>
                <div class="fs-3 fw-bold">{{ $stats['nb_etudiants'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total paiements encaissés</div>
                <div class="fs-4 fw-bold text-success">{{ number_format($stats['total_paiements'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total impayés ({{ $stats['nb_debiteurs'] }} débiteurs)</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($stats['total_impayes'], 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h3 class="h6 mb-3">Répartition des utilisateurs par rôle</h3>
        <table class="table table-sm mb-0">
            <tbody>
                @foreach ($parRole as $role => $total)
                    <tr>
                        <td>{{ \App\Enums\Role::from($role)->label() }}</td>
                        <td class="text-end fw-bold">{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
