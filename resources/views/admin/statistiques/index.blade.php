@extends('layouts.dashboard')

@section('title', 'Statistiques — SIGE UCAO')

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

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Répartition des utilisateurs par rôle</h3>
                <table class="table table-sm mb-3">
                    <tbody>
                        @foreach ($parRole as $role => $total)
                            <tr>
                                <td>{{ \App\Enums\Role::from($role)->label() }}</td>
                                <td class="text-end fw-bold">{{ $total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <canvas id="chartRoles" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Répartition des étudiants par filière</h3>
                <table class="table table-sm mb-3">
                    <tbody>
                        @foreach ($parFiliere as $filiere => $total)
                            <tr>
                                <td>{{ $filiere }}</td>
                                <td class="text-end fw-bold">{{ $total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <canvas id="chartFilieres" height="220"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h3 class="h6 mb-3">Paiements vs impayés</h3>
                <canvas id="chartPaiements" height="220"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const ucaoChartColors = ['#1e3a8a', '#d97706', '#16a34a', '#0ea5e9', '#9333ea', '#dc2626'];

    new Chart(document.getElementById('chartRoles'), {
        type: 'doughnut',
        data: {
            labels: @json($parRole->keys()->map(fn ($role) => \App\Enums\Role::from($role)->label())),
            datasets: [{
                data: @json($parRole->values()),
                backgroundColor: ucaoChartColors,
            }],
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
    });

    new Chart(document.getElementById('chartFilieres'), {
        type: 'bar',
        data: {
            labels: @json($parFiliere->keys()),
            datasets: [{
                label: 'Étudiants',
                data: @json($parFiliere->values()),
                backgroundColor: '#1e3a8a',
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    new Chart(document.getElementById('chartPaiements'), {
        type: 'pie',
        data: {
            labels: ['Encaissés', 'Impayés'],
            datasets: [{
                data: [{{ (float) $stats['total_paiements'] }}, {{ (float) $stats['total_impayes'] }}],
                backgroundColor: ['#16a34a', '#dc2626'],
            }],
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
    });
</script>
@endpush
