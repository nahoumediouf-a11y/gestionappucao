@extends('layouts.dashboard')

@section('title', 'Tableau de bord — SIGE UCAO')

@section('page-title', 'Tableau de bord')
@section('page-subtitle', "Modules accessibles selon votre rôle.")

@section('page-content')
@if (! empty($apercu))
    {{-- En-tête personnalisé étudiant --}}
    <div class="card border-0 shadow-sm mb-3 ucao-fade-up" style="background: linear-gradient(135deg, var(--ucao-blue) 0%, #2563eb 100%);">
        <div class="card-body text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h4 mb-1">Bonjour {{ $user->prenom }} 👋</h2>
                <p class="mb-0 opacity-75 small">
                    <i class="bi bi-mortarboard me-1"></i>{{ $user->etudiant->filiere }} {{ $user->etudiant->niveau }}
                    · Matricule {{ $user->etudiant->matricule }}
                </p>
            </div>
            <i class="bi bi-mortarboard-fill fs-1 opacity-25 d-none d-sm-block"></i>
        </div>
    </div>

    {{-- Bandeau d'aperçu --}}
    <div class="row g-3 mb-4">
        @php
            $soldeOk = $apercu['solde'] <= 0;
        @endphp
        <div class="col-6 col-lg-3">
            <a href="{{ route('etudiant.paiements.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-cash-coin me-1"></i>Solde restant</div>
                        <div class="h5 mb-0 text-{{ $soldeOk ? 'success' : 'danger' }}">
                            {{ number_format($apercu['solde'], 0, ',', ' ') }} FCFA
                        </div>
                        @if (! $soldeOk)
                            <span class="badge bg-danger mt-1">À payer</span>
                        @else
                            <span class="badge bg-success mt-1">À jour</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('etudiant.bulletin.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-bar-chart me-1"></i>Moyenne générale</div>
                        <div class="h5 mb-0 text-{{ $apercu['moyenne'] >= 10 ? 'success' : ($apercu['moyenne'] > 0 ? 'danger' : 'secondary') }}">
                            {{ $apercu['moyenne'] > 0 ? $apercu['moyenne'].' /20' : '—' }}
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ $apercu['coursEnCours'] ? route('etudiant.cours.index') : route('etudiant.edt.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-clock me-1"></i>Prochaine séance</div>
                        @if ($apercu['coursEnCours'])
                            <div class="fw-semibold small">{{ \Illuminate\Support\Str::limit($apercu['coursEnCours']->titre, 22) }}</div>
                            <span class="badge bg-success mt-1">En ligne maintenant</span>
                        @elseif ($apercu['prochaineSeance'])
                            <div class="fw-semibold small">{{ \Illuminate\Support\Str::limit($apercu['prochaineSeance']->matiere, 22) }}</div>
                            <div class="text-muted small">{{ \Illuminate\Support\Str::substr($apercu['prochaineSeance']->heure_debut, 0, 5) }} · salle {{ $apercu['prochaineSeance']->salle }}</div>
                        @else
                            <div class="text-muted small">Aucune aujourd'hui</div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('etudiant.projets.index') }}" class="text-decoration-none text-reset">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small"><i class="bi bi-kanban me-1"></i>Travaux à rendre</div>
                        <div class="h5 mb-0 text-{{ $apercu['aRendre'] ? 'warning' : 'success' }}">{{ $apercu['aRendre'] }}</div>
                        @if ($apercu['prochaineEcheance'])
                            <div class="text-muted small">Prochaine : {{ $apercu['prochaineEcheance']->date_limite->format('d/m') }}</div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    </div>
@endif

@if (! empty($stats))
    {{-- Cartes statistiques --}}
    <div class="row g-3 mb-3">
        @php
            $cartesStats = [
                ['Étudiants', number_format($stats['cartes']['etudiants'], 0, ',', ' '), 'bi-people', 'primary'],
                ['Professeurs', number_format($stats['cartes']['professeurs'], 0, ',', ' '), 'bi-easel', 'info'],
                ['Paiements du mois', number_format($stats['cartes']['paiementsMois'], 0, ',', ' ').' FCFA', 'bi-cash-coin', 'success'],
                ['Taux de recouvrement', $stats['cartes']['tauxRecouvrement'].' %', 'bi-graph-up-arrow', $stats['cartes']['tauxRecouvrement'] >= 50 ? 'success' : 'warning'],
            ];
        @endphp
        @foreach ($cartesStats as [$label, $valeur, $icon, $couleur])
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-{{ $couleur }} bg-opacity-10 text-{{ $couleur }} p-3">
                            <i class="bi {{ $icon }} fs-4"></i>
                        </div>
                        <div>
                            <div class="h5 mb-0">{{ $valeur }}</div>
                            <div class="small text-muted">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Graphiques --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Évolution des paiements (6 mois)</h2>
                    <canvas id="chartPaiements" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Répartition des étudiants</h2>
                    <canvas id="chartFilieres" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3">Absences par mois</h2>
                    <canvas id="chartAbsences" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;
            var bleu = '#2563eb', vert = '#10b981', orange = '#f59e0b', rouge = '#ef4444', violet = '#8b5cf6', cyan = '#06b6d4';
            var palette = [bleu, vert, orange, rouge, violet, cyan];

            new Chart(document.getElementById('chartPaiements'), {
                type: 'line',
                data: {
                    labels: @json($stats['paiements']['labels']),
                    datasets: [{ label: 'Paiements (FCFA)', data: @json($stats['paiements']['valeurs']),
                        borderColor: bleu, backgroundColor: 'rgba(37,99,235,.12)', fill: true, tension: .35 }]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            new Chart(document.getElementById('chartFilieres'), {
                type: 'doughnut',
                data: {
                    labels: @json($stats['filieres']['labels']),
                    datasets: [{ data: @json($stats['filieres']['valeurs']), backgroundColor: palette }]
                },
                options: { plugins: { legend: { position: 'bottom' } } }
            });

            new Chart(document.getElementById('chartAbsences'), {
                type: 'bar',
                data: {
                    labels: @json($stats['absences']['labels']),
                    datasets: [{ label: 'Absences', data: @json($stats['absences']['valeurs']), backgroundColor: orange }]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        });
    </script>
    @endpush
@endif

<div class="row g-3">
    @forelse ($modules as $module)
        @php
            $href      = isset($module['url']) ? $module['url'] : route($module['route']);
            $highlight = ! empty($module['highlight']);
        @endphp
        <div class="col-md-6 col-lg-4">
            <a href="{{ $href }}" class="text-decoration-none text-reset">
                <div class="card h-100 border-0 shadow-sm {{ $highlight ? 'border border-danger border-2' : '' }}"
                     style="{{ $highlight ? 'box-shadow: 0 0 0 3px rgba(220,53,69,.15) !important;' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-{{ $module['color'] }} {{ $highlight ? 'bg-opacity-25' : 'bg-opacity-10' }} text-{{ $module['color'] }} p-3">
                                <i class="bi {{ $module['icon'] }} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="h6 mb-1 {{ $highlight ? 'text-danger fw-bold' : '' }}">
                                    {{ $module['label'] }}
                                    @if (! empty($module['badge']))
                                        <span class="badge bg-{{ $highlight ? 'danger' : 'danger' }} rounded-pill ms-1">{{ $module['badge'] }}</span>
                                    @endif
                                </h3>
                                @if ($highlight)
                                    <p class="small text-danger mb-0 fw-semibold">
                                        <i class="bi bi-arrow-right-circle me-1"></i>Cliquez pour payer maintenant
                                    </p>
                                @else
                                    <p class="small text-muted mb-0">Module autorisé pour {{ $user->role->label() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">Aucun module accessible.</div>
        </div>
    @endforelse
</div>
@endsection
