@extends('layouts.dashboard')

@section('title', 'Copies — '.$projet->titre.' — SIGE UCAO')

@section('page-title', 'Copies — '.$projet->titre)
@section('page-subtitle', $projet->typeLabel().' · '.$projet->matiere.' — '.$projet->filiere.' '.$projet->niveau.' · noté sur '.$projet->bareme)

@section('page-actions')
    <a href="{{ route('professeur.projets.export', $projet) }}" class="btn btn-outline-secondary">
        <i class="bi bi-filetype-csv me-1"></i>Export CSV
    </a>
@endsection

@section('page-content')
<div class="row g-3 mb-3">
    @php
        $cartes = [
            ['Rendus', $stats['rendus'].' / '.$stats['attendus'], 'primary'],
            ['En retard', $stats['retards'], 'warning'],
            ['Corrigées', $stats['corrigees'], 'success'],
            ['Moyenne', $stats['moyenne'] !== null ? $stats['moyenne'].' /'.$projet->bareme : '—', 'dark'],
        ];
    @endphp
    @foreach ($cartes as [$label, $valeur, $couleur])
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="h4 mb-0 text-{{ $couleur }}">{{ $valeur }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Rendu</th>
                    <th>Copie</th>
                    <th>Note</th>
                    <th class="text-end">Correction</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($soumissions as $s)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $s->etudiant->user?->nom_complet }}</div>
                            <small class="text-muted">{{ $s->etudiant->matricule }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $s->statutCouleur() }}">{{ $s->statutLabel() }}</span>
                            <div class="small text-muted">{{ $s->rendu_a->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            @if ($s->fichier_path)
                                <a href="{{ route('professeur.projets.copie.fichier', [$projet, $s]) }}">
                                    <i class="bi bi-paperclip"></i> {{ \Illuminate\Support\Str::limit($s->fichier_nom, 24) }}
                                </a>
                            @endif
                            @if ($s->texte)
                                <details class="small"><summary>Texte</summary>{{ $s->texte }}</details>
                            @endif
                            @if (! $s->fichier_path && ! $s->texte)
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($s->estCorrigee())
                                <span class="fw-semibold">{{ rtrim(rtrim((string) $s->note, '0'), '.') }}/{{ $projet->bareme }}</span>
                            @else
                                <span class="text-muted small">non corrigée</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('professeur.projets.corriger', [$projet, $s]) }}" class="row g-1 justify-content-end">
                                @csrf
                                <div class="col-auto" style="max-width: 90px;">
                                    <input type="number" step="0.25" min="0" max="{{ $projet->bareme }}" name="note"
                                        value="{{ $s->note }}" class="form-control form-control-sm" placeholder="/{{ $projet->bareme }}" required>
                                </div>
                                <div class="col-auto" style="max-width: 200px;">
                                    <input type="text" name="commentaire_correction" value="{{ $s->commentaire_correction }}"
                                        class="form-control form-control-sm" placeholder="Commentaire">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-ucao">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune copie rendue pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<a href="{{ route('professeur.projets.index') }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Retour aux travaux
</a>
@endsection
