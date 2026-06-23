@extends('layouts.dashboard')

@section('title', 'Carnet de notes — SIGE UCAO')

@section('page-title', 'Carnet de notes')
@section('page-subtitle', $filiere.' '.$niveau.($matiere ? ' — '.$matiere : ''))

@section('page-actions')
    <a href="{{ route('professeur.carnet.export', ['filiere' => $filiere, 'niveau' => $niveau, 'matiere' => $matiere]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-filetype-csv me-1"></i>Export CSV
    </a>
@endsection

@section('page-content')
{{-- Sélecteur de matière + ajout d'une colonne d'évaluation --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('professeur.carnet.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="filiere" value="{{ $filiere }}">
            <input type="hidden" name="niveau" value="{{ $niveau }}">
            <div class="col-md-4">
                <label for="matiere" class="form-label mb-1">Matière</label>
                <select name="matiere" id="matiere" class="form-select" onchange="this.form.submit()">
                    @foreach ($matieres as $m)
                        <option value="{{ $m }}" @selected($m === $matiere)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label for="nouvelle_session" class="form-label mb-1">Ajouter une évaluation (colonne)</label>
                <input type="text" name="nouvelle_session" id="nouvelle_session" class="form-control" placeholder="Ex : Contrôle n°2">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-plus-lg me-1"></i>Ajouter la colonne</button>
            </div>
        </form>
    </div>
</div>

@if (empty($sessions))
    <div class="alert alert-info">Aucune évaluation pour cette matière. Ajoutez une colonne ci-dessus pour commencer la saisie.</div>
@else
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    @foreach ($sessions as $s)
                        <th class="text-center">{{ $s }}</th>
                    @endforeach
                    <th class="text-center">Moyenne</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($etudiants as $e)
                    @php
                        $valeurs = collect($sessions)->map(fn ($s) => $notes[$e->id][$s] ?? null)->filter(fn ($v) => $v !== null);
                        $moy = $valeurs->isNotEmpty() ? round($valeurs->avg(), 2) : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="small fw-semibold">{{ $e->user?->nom_complet }}</div>
                            <span class="text-muted small">{{ $e->matricule }}</span>
                        </td>
                        @foreach ($sessions as $s)
                            @php $val = $notes[$e->id][$s] ?? null; @endphp
                            <td style="max-width: 110px;">
                                <form method="POST" action="{{ route('professeur.carnet.note') }}">
                                    @csrf
                                    <input type="hidden" name="filiere" value="{{ $filiere }}">
                                    <input type="hidden" name="niveau" value="{{ $niveau }}">
                                    <input type="hidden" name="matiere" value="{{ $matiere }}">
                                    <input type="hidden" name="session" value="{{ $s }}">
                                    <input type="hidden" name="etudiant_id" value="{{ $e->id }}">
                                    <input type="number" step="0.25" min="0" max="20" name="valeur"
                                        value="{{ $val !== null ? rtrim(rtrim((string) $val, '0'), '.') : '' }}"
                                        class="form-control form-control-sm text-center {{ $val !== null && $val < 10 ? 'text-danger fw-bold' : '' }}"
                                        onchange="this.form.submit()" aria-label="Note {{ $e->matricule }} {{ $s }}">
                                </form>
                            </td>
                        @endforeach
                        <td class="text-center fw-semibold {{ $moy !== null && $moy < 10 ? 'text-danger' : '' }}">
                            {{ $moy !== null ? $moy : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($sessions) + 2 }}" class="text-center text-muted py-4">Aucun étudiant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body small text-muted">La modification d'une case enregistre la note automatiquement. Videz une case pour supprimer la note.</div>
</div>
@endif

<a href="{{ route('professeur.classes.show', ['filiere' => $filiere, 'niveau' => $niveau]) }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Retour à la classe
</a>
@endsection
