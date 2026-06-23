@extends('layouts.dashboard')

@section('title', $projet->titre.' — SIGE UCAO')

@section('page-title', $projet->titre)
@section('page-subtitle', $projet->typeLabel().' · '.$projet->matiere.' — échéance le '.$projet->date_limite->format('d/m/Y'))

@section('page-content')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h3 class="h6">Consignes</h3>
                <p class="mb-3">{{ $projet->description ?: 'Aucune consigne détaillée.' }}</p>
                <ul class="list-unstyled small text-muted mb-0">
                    <li><strong>Barème :</strong> noté sur {{ $projet->bareme }}</li>
                    <li><strong>Échéance :</strong> {{ $projet->date_limite->format('d/m/Y') }} (au-delà : rendu marqué en retard)</li>
                    @if ($projet->ouverture_at)
                        <li><strong>Ouverture du dépôt :</strong> {{ $projet->ouverture_at->format('d/m/Y H:i') }}</li>
                    @endif
                    @if ($projet->fermeture_at)
                        <li><strong>Fermeture du dépôt :</strong> {{ $projet->fermeture_at->format('d/m/Y H:i') }}</li>
                    @endif
                    @if ($projet->copie_unique)
                        <li class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Une seule remise autorisée.</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-3">Ma remise</h3>

                @if ($soumission)
                    <p class="mb-1">
                        <span class="badge bg-{{ $soumission->statutCouleur() }}">{{ $soumission->statutLabel() }}</span>
                        <span class="text-muted small">le {{ $soumission->rendu_a->format('d/m/Y H:i') }}</span>
                    </p>
                    @if ($soumission->fichier_path)
                        <p class="mb-2">
                            <a href="{{ route('etudiant.projets.fichier', $projet) }}">
                                <i class="bi bi-paperclip me-1"></i>{{ $soumission->fichier_nom }}
                            </a>
                        </p>
                    @endif
                    @if ($soumission->texte)
                        <p class="small bg-light p-2 rounded">{{ $soumission->texte }}</p>
                    @endif

                    @if ($soumission->estCorrigee())
                        <div class="alert alert-primary mt-3 mb-0">
                            <div class="fw-semibold">Note : {{ rtrim(rtrim((string) $soumission->note, '0'), '.') }}/{{ $projet->bareme }}</div>
                            @if ($soumission->commentaire_correction)
                                <div class="small mt-1">{{ $soumission->commentaire_correction }}</div>
                            @endif
                        </div>
                    @endif
                @else
                    <p class="text-muted small">Vous n'avez pas encore rendu ce travail.</p>
                @endif

                @if ($projet->accepteRendu() && ! ($soumission && $projet->copie_unique))
                    <hr>
                    <form method="POST" action="{{ route('etudiant.projets.soumettre', $projet) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="texte" class="form-label">Réponse (texte, optionnel)</label>
                            <textarea name="texte" id="texte" rows="3" class="form-control @error('texte') is-invalid @enderror">{{ old('texte', $soumission->texte ?? '') }}</textarea>
                            @error('texte')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="fichier" class="form-label">Fichier (pdf, doc, docx, zip, image — max 10 Mo)</label>
                            <input type="file" name="fichier" id="fichier" class="form-control @error('fichier') is-invalid @enderror">
                            @error('fichier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-ucao w-100">
                            <i class="bi bi-upload me-1"></i>{{ $soumission ? 'Mettre à jour ma remise' : 'Rendre mon travail' }}
                        </button>
                    </form>
                @elseif (! $projet->rendu_en_ligne)
                    <div class="alert alert-secondary mt-2 mb-0 small">Ce travail ne se rend pas en ligne.</div>
                @elseif ($soumission && $projet->copie_unique)
                    <div class="alert alert-secondary mt-2 mb-0 small">Remise unique déjà effectuée.</div>
                @else
                    <div class="alert alert-warning mt-2 mb-0 small">Le dépôt est fermé pour ce travail.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<a href="{{ route('etudiant.projets.index') }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Retour à la liste
</a>
@endsection
