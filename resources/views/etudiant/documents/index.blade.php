@extends('layouts.dashboard')

@section('title', 'Documents de cours — SIGE UCAO')

@section('page-title', 'Documents de cours')
@section('page-subtitle', 'Supports de cours mis en ligne par vos professeurs.')

@section('page-content')
@forelse ($documents as $document)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h3 class="h6 mb-1">{{ $document->titre }}</h3>
                    <div class="text-muted small mb-2">
                        {{ $document->matiere }} — {{ $document->filiere }} {{ $document->niveau }}
                        @if ($document->professeur)
                            · Prof. {{ $document->professeur->nom_complet }}
                        @endif
                    </div>
                    @if ($document->description)
                        <p class="mb-2">{{ $document->description }}</p>
                    @endif
                    <div class="small text-muted">
                        <i class="bi bi-file-earmark me-1"></i>{{ $document->nom_original }}
                        ({{ $document->tailleLisible() }})
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ route('etudiant.documents.download', $document) }}" class="btn btn-ucao btn-sm">
                        <i class="bi bi-download me-1"></i>Télécharger
                    </a>
                    <div class="small text-muted mt-1">{{ $document->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-info">Aucun document de cours n'a été mis en ligne pour le moment.</div>
@endforelse
@endsection
