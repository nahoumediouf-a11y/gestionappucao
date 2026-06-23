@extends('layouts.dashboard')

@section('title', 'Recherche — SIGE UCAO')

@section('page-title', 'Recherche')

@section('page-content')
@php $role = auth()->user()->role; @endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('recherche.globale') }}" class="row g-2 align-items-end">
            <div class="{{ $estAdmin ? 'col-md-8' : 'col-md-10' }}">
                <label for="q" class="form-label">Terme de recherche</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" id="q" value="{{ $q }}" class="form-control"
                        placeholder="Nom, matricule, filière, niveau..." autofocus>
                </div>
            </div>
            @if ($estAdmin)
                <div class="col-md-2">
                    <label for="type" class="form-label">Catégorie</label>
                    <select name="type" id="type" class="form-select">
                        <option value="etudiant" @selected($type === 'etudiant')>Étudiants</option>
                        <option value="personnel" @selected($type === 'personnel')>Personnel</option>
                    </select>
                </div>
            @endif
            <div class="col-md-2">
                <button type="submit" class="btn btn-ucao w-100"><i class="bi bi-search me-1"></i>Rechercher</button>
            </div>
        </form>
    </div>
</div>

@if ($q === '')
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Saisissez un terme pour lancer la recherche.</div>
@else
    {{-- Résultats personnel (admin) --}}
    @if ($type === 'personnel')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">{{ $personnel->count() }} membre(s) du personnel</div>
            <div class="list-group list-group-flush">
                @forelse ($personnel as $membre)
                    <a href="{{ route('admin.utilisateurs.index', ['q' => $membre->login]) }}" class="list-group-item list-group-item-action">
                        @include('partials._identite', ['identite' => $membre, 'taille' => 'sm'])
                    </a>
                @empty
                    <div class="list-group-item text-center text-muted py-4">Aucun membre du personnel trouvé.</div>
                @endforelse
            </div>
        </div>
    @else
        {{-- Résultats étudiants --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">{{ $etudiants->count() }} étudiant(s) trouvé(s)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Matricule</th><th>Étudiant</th><th>Classe</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($etudiants as $etudiant)
                            <tr>
                                <td><code>{{ $etudiant->matricule }}</code></td>
                                <td>{{ $etudiant->user?->nom_complet ?? '—' }}</td>
                                <td>{{ $etudiant->filiere }} {{ $etudiant->niveau }}</td>
                                <td class="text-end">
                                    <a href="{{ \App\Http\Controllers\RechercheGlobaleController::destinationEtudiant($role, $etudiant) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Aucun étudiant trouvé pour « {{ $q }} ».</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
@endsection
