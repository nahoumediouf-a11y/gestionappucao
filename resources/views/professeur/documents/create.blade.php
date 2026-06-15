@extends('layouts.dashboard')

@section('title', 'Ajouter un document — Recouvrement UCAO')

@section('page-title', 'Ajouter un document de cours')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('professeur.documents.store') }}" enctype="multipart/form-data">
            @csrf

            @if ($creneaux->isNotEmpty())
                <div class="mb-3">
                    <label for="creneau" class="form-label">Classe / matière concernée</label>
                    <select id="creneau" class="form-select" onchange="ucaoRemplirCreneau(this)">
                        <option value="">-- Choisir un raccourci --</option>
                        @foreach ($creneaux as $creneau)
                            <option value="{{ $creneau->filiere }}|{{ $creneau->niveau }}|{{ $creneau->matiere }}">
                                {{ $creneau->filiere }} {{ $creneau->niveau }} — {{ $creneau->matiere }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Sélectionnez une de vos classes pour pré-remplir les champs ci-dessous, ou renseignez-les manuellement.</div>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="titre" class="form-label">Titre du document</label>
                    <input type="text" name="titre" id="titre" value="{{ old('titre') }}"
                        class="form-control @error('titre') is-invalid @enderror">
                    @error('titre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="filiere" class="form-label">Filière</label>
                    <input type="text" name="filiere" id="filiere" value="{{ old('filiere') }}"
                        class="form-control @error('filiere') is-invalid @enderror">
                    @error('filiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="niveau" class="form-label">Niveau</label>
                    <input type="text" name="niveau" id="niveau" value="{{ old('niveau') }}"
                        class="form-control @error('niveau') is-invalid @enderror">
                    @error('niveau')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="matiere" class="form-label">Matière</label>
                    <input type="text" name="matiere" id="matiere" value="{{ old('matiere') }}"
                        class="form-control @error('matiere') is-invalid @enderror">
                    @error('matiere')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description (optionnel)</label>
                    <textarea name="description" id="description" rows="3"
                        class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="fichier" class="form-label">Fichier (PDF, Word, PowerPoint, Excel, ZIP, image — max 10 Mo)</label>
                    <input type="file" name="fichier" id="fichier"
                        class="form-control @error('fichier') is-invalid @enderror">
                    @error('fichier')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Mettre en ligne
                </button>
                <a href="{{ route('professeur.documents.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@if ($creneaux->isNotEmpty())
    <script>
        function ucaoRemplirCreneau(select) {
            if (! select.value) {
                return;
            }
            var parts = select.value.split('|');
            document.getElementById('filiere').value = parts[0];
            document.getElementById('niveau').value = parts[1];
            document.getElementById('matiere').value = parts[2];
        }
    </script>
@endif
@endsection
