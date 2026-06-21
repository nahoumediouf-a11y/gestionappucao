@extends('layouts.dashboard')
@section('title', 'Publier un document de cours')
@section('content')
<div class="container py-4" style="max-width:680px">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('professeur.documents_cours.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-bold mb-0"><i class="bi bi-cloud-upload text-primary me-2"></i>Publier un document de cours</h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('professeur.documents_cours.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Titre du document <span class="text-danger">*</span></label>
                    <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                           value="{{ old('titre') }}" placeholder="ex: Cours Algorithmique — Chapitre 3">
                    @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-muted small">(facultatif)</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                              placeholder="Résumé, objectifs, contenu du document...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                        <input type="text" name="matiere" class="form-control @error('matiere') is-invalid @enderror"
                               value="{{ old('matiere') }}" placeholder="ex: Algorithmique">
                        @error('matiere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Filière <span class="text-muted small">(laisser vide = tous)</span></label>
                        <select name="filiere" class="form-select">
                            <option value="">Toutes les filières</option>
                            <option value="Informatique" {{ old('filiere')=='Informatique'?'selected':'' }}>Informatique</option>
                            <option value="Gestion" {{ old('filiere')=='Gestion'?'selected':'' }}>Gestion</option>
                            <option value="Droit" {{ old('filiere')=='Droit'?'selected':'' }}>Droit</option>
                            <option value="Sciences" {{ old('filiere')=='Sciences'?'selected':'' }}>Sciences</option>
                            <option value="Théologie" {{ old('filiere')=='Théologie'?'selected':'' }}>Théologie</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Niveau <span class="text-muted small">(laisser vide = tous)</span></label>
                        <select name="niveau" class="form-select">
                            <option value="">Tous les niveaux</option>
                            @foreach(['L1','L2','L3','M1','M2'] as $n)
                                <option value="{{ $n }}" {{ old('niveau')==$n?'selected':'' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Fichier <span class="text-danger">*</span></label>
                    <input type="file" name="fichier" class="form-control @error('fichier') is-invalid @enderror"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.txt">
                    <div class="form-text">PDF, Word, PowerPoint, Excel, ZIP — max 20 Mo</div>
                    @error('fichier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-cloud-upload me-1"></i> Publier
                    </button>
                    <a href="{{ route('professeur.documents_cours.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
