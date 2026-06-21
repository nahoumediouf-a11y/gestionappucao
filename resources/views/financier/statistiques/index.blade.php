@extends('layouts.dashboard')

@section('title', 'Statistiques financières — SIGE UCAO')
@section('page-title', 'Statistiques financières')
@section('page-subtitle', 'Vue d\'ensemble des encaissements et du recouvrement.')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('page-content')

{{-- ===== KPIs ===== --}}
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-cash-coin me-1"></i>Total encaissé</div>
                <div class="fs-5 fw-bold text-success">{{ number_format($totalEncaisse, 0, ',', ' ') }}</div>
                <div class="small text-muted">FCFA</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Total impayés</div>
                <div class="fs-5 fw-bold text-danger">{{ number_format($totalImpayes, 0, ',', ' ') }}</div>
                <div class="small text-muted">FCFA</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-graph-up me-1"></i>Taux de recouvrement</div>
                <div class="fs-5 fw-bold text-primary">{{ $tauxRecouvrement }} %</div>
                <div class="progress mt-2" style="height:6px;">
                    <div class="progress-bar bg-primary" style="width:{{ $tauxRecouvrement }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-clock me-1"></i>En attente validation</div>
                <div class="fs-5 fw-bold text-warning">{{ $nbEnAttente }}</div>
                <div class="small text-muted">déclaration(s)</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-receipt me-1"></i>Paiements validés</div>
                <div class="fs-5 fw-bold">{{ $nbValides }} <span class="text-muted fs-6">/ {{ $nbPaiements }}</span></div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-people me-1"></i>Étudiants débiteurs</div>
                <div class="fs-5 fw-bold text-danger">{{ $nbDebiteurs }}</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-check-circle me-1"></i>Étudiants à jour</div>
                <div class="fs-5 fw-bold text-success">{{ $nbAJour }}</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1"><i class="bi bi-people-fill me-1"></i>Total étudiants</div>
                <div class="fs-5 fw-bold">{{ $nbDebiteurs + $nbAJour }}</div>
            </div>
        </div>
    </div>

</div>

{{-- ===== GRAPHIQUES ligne 1 ===== --}}
<div class="row g-3 mb-4">

    {{-- Encaissements par mois --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-bar-chart-line me-1 text-primary"></i>Encaissements par mois (FCFA)</h6>
                <canvas id="chartMois" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Répartition modes de paiement --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-pie-chart me-1 text-success"></i>Modes de paiement</h6>
                <canvas id="chartModes" height="180"></canvas>
                <div class="mt-3">
                    @foreach ($parMode as $m)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $m->mode_paiement) }}</span>
                        <span class="fw-semibold">{{ number_format($m->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ===== GRAPHIQUES ligne 2 ===== --}}
<div class="row g-3 mb-4">

    {{-- Impayés par filière --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-bar-chart-fill me-1 text-danger"></i>Impayés par filière (FCFA)</h6>
                <canvas id="chartFiliere" height="160"></canvas>
            </div>
        </div>
    </div>

    {{-- Taux à jour vs débiteurs par filière --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-people-fill me-1 text-info"></i>À jour vs Débiteurs par filière</h6>
                <canvas id="chartAJour" height="160"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- ===== TOP DEBITEURS ===== --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="fw-semibold mb-3"><i class="bi bi-trophy me-1 text-warning"></i>Top 5 étudiants débiteurs</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Étudiant</th>
                        <th>Matricule</th>
                        <th>Filière / Niveau</th>
                        <th class="text-end">Solde restant</th>
                        <th>Progression</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topDebiteurs as $i => $etudiant)
                    @php
                        $niveauKey = strtoupper($etudiant->niveau ?? '');
                        if (str_contains($niveauKey, 'L3')) $niveauKey = 'L3';
                        $scolarites = ['L1'=>650000,'L2'=>700000,'L3'=>780000,'M1'=>850000,'M2'=>900000];
                        $total = $scolarites[$niveauKey] ?? 780000;
                        $paye = max(0, $total - $etudiant->solde);
                        $pct = $total > 0 ? min(100, round($paye/$total*100)) : 0;
                    @endphp
                    <tr>
                        <td><span class="badge bg-{{ $i === 0 ? 'danger' : ($i === 1 ? 'warning text-dark' : 'secondary') }}">{{ $i+1 }}</span></td>
                        <td class="fw-semibold">{{ $etudiant->user->nom_complet }}</td>
                        <td><code>{{ $etudiant->matricule }}</code></td>
                        <td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td>
                        <td class="text-end text-danger fw-bold">{{ number_format($etudiant->solde, 0, ',', ' ') }} FCFA</td>
                        <td style="min-width:120px;">
                            <div class="progress" style="height:8px;">
                                <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : ($pct >= 40 ? 'warning' : 'danger') }}"
                                    style="width:{{ $pct }}%"></div>
                            </div>
                            <small class="text-muted">{{ $pct }}% réglé</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const ucaoColors = ['#3b5bdb','#2f9e44','#f59f00','#f03e3e','#ae3ec9','#1098ad','#f76707','#099268'];

// Chart 1 — Encaissements par mois
new Chart(document.getElementById('chartMois'), {
    type: 'bar',
    data: {
        labels: {!! $parMois->pluck('mois')->toJson() !!},
        datasets: [{
            label: 'Encaissé (FCFA)',
            data: {!! $parMois->pluck('total')->toJson() !!},
            backgroundColor: '#3b5bdb',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => (v/1000000).toFixed(1) + 'M'
                }
            }
        }
    }
});

// Chart 2 — Modes de paiement (donut)
new Chart(document.getElementById('chartModes'), {
    type: 'doughnut',
    data: {
        labels: {!! $parMode->pluck('mode_paiement')->map(fn($m) => ucfirst(str_replace('_',' ',$m)))->toJson() !!},
        datasets: [{
            data: {!! $parMode->pluck('total')->toJson() !!},
            backgroundColor: ucaoColors,
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } },
        cutout: '60%',
    }
});

// Chart 3 — Impayés par filière
new Chart(document.getElementById('chartFiliere'), {
    type: 'bar',
    data: {
        labels: {!! $parFiliere->pluck('filiere')->toJson() !!},
        datasets: [{
            label: 'Impayés (FCFA)',
            data: {!! $parFiliere->pluck('total_impayes')->toJson() !!},
            backgroundColor: '#f03e3e',
            borderRadius: 6,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { callback: v => (v/1000).toFixed(0)+'k' } } }
    }
});

// Chart 4 — À jour vs Débiteurs par filière (stacked)
new Chart(document.getElementById('chartAJour'), {
    type: 'bar',
    data: {
        labels: {!! $parFiliere->pluck('filiere')->toJson() !!},
        datasets: [
            {
                label: 'À jour',
                data: {!! $parFiliere->pluck('nb_a_jour')->toJson() !!},
                backgroundColor: '#2f9e44',
                borderRadius: 4,
            },
            {
                label: 'Débiteurs',
                data: {!! $parFiliere->map(fn($f) => $f->nb_etudiants - $f->nb_a_jour)->toJson() !!},
                backgroundColor: '#f03e3e',
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
@endpush
