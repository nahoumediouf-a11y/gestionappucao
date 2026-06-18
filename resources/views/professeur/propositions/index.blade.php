@extends('layouts.dashboard')

@section('title', 'Propositions de projets étudiants — SIGE UCAO')
@section('page-title', 'Propositions de projets étudiants')
@section('page-subtitle', 'Examinez et traitez les projets personnels soumis par les étudiants.')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
        @forelse ($propositions as $proposition)
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-bold">{{ $proposition->titre }}</span>
                            <span class="badge bg-{{ $proposition->statutBadge() }}">{{ $proposition->statutLabel() }}</span>
                        </div>
                        <div class="small text-muted mb-1">
                            <i class="bi bi-person me-1"></i>{{ $proposition->etudiant->user->nom_complet }}
                            ({{ $proposition->etudiant->matricule }}) —
                            {{ $proposition->etudiant->filiere }} {{ $proposition->etudiant->niveau }}
                            @if($proposition->matiere)
                                — <i class="bi bi-book me-1"></i>{{ $proposition->matiere }}
                            @endif
                        </div>
                        <p class="mb-1 small">{{ $proposition->description }}</p>
                        @if($proposition->commentaire)
                            <div class="small text-muted fst-italic">
                                <i class="bi bi-chat-left-text me-1"></i>{{ $proposition->commentaire }}
                            </div>
                        @endif
                        <div class="small text-muted mt-1">Soumis le {{ $proposition->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    @if($proposition->statut === 'en_attente')
                        <div class="d-flex gap-2 flex-shrink-0">
                            <button type="button" class="btn btn-sm btn-success"
                                data-bs-toggle="modal" data-bs-target="#modal-{{ $proposition->id }}-accepte">
                                <i class="bi bi-check-circle me-1"></i>Accepter
                            </button>
                            <button type="button" class="btn btn-sm btn-danger"
                                data-bs-toggle="modal" data-bs-target="#modal-{{ $proposition->id }}-refuse">
                                <i class="bi bi-x-circle me-1"></i>Refuser
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            @if($proposition->statut === 'en_attente')
                @foreach(['accepte' => ['Accepter', 'success'], 'refuse' => ['Refuser', 'danger']] as $statut => [$label, $color])
                    <div class="modal fade" id="modal-{{ $proposition->id }}-{{ $statut }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('professeur.propositions.traiter', $proposition) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="statut" value="{{ $statut }}">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ $label }} la proposition</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="fw-semibold">{{ $proposition->titre }}</p>
                                        <div class="mb-3">
                                            <label class="form-label">Commentaire <span class="text-muted">(optionnel)</span></label>
                                            <textarea name="commentaire" rows="3" class="form-control"
                                                placeholder="Laissez un message à l'étudiant…"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-{{ $color }}">{{ $label }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @empty
            <div class="list-group-item text-center text-muted py-5">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Aucune proposition de projet soumise par les étudiants.
            </div>
        @endforelse
    </div>
</div>
@endsection
