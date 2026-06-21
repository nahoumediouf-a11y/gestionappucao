@extends('layouts.dashboard')
@section('title', 'Documents de cours')
@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-file-earmark-richtext text-primary me-2"></i>Documents de cours</h4>

    @if($documents->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <i class="bi bi-folder2-open text-muted" style="font-size:3rem"></i>
            <p class="text-muted mt-3">Aucun document disponible pour votre filière/niveau pour le moment.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach($documents as $doc)
            @php
                $icons = ['pdf'=>['bi-file-earmark-pdf','danger'],'doc'=>['bi-file-earmark-word','primary'],'docx'=>['bi-file-earmark-word','primary'],'ppt'=>['bi-file-earmark-slides','warning'],'pptx'=>['bi-file-earmark-slides','warning'],'xls'=>['bi-file-earmark-excel','success'],'xlsx'=>['bi-file-earmark-excel','success'],'zip'=>['bi-file-zip','secondary']];
                [$icon, $color] = $icons[strtolower($doc->type_fichier)] ?? ['bi-file-earmark','muted'];
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex gap-3">
                        <div class="flex-shrink-0">
                            <i class="bi {{ $icon }} text-{{ $color }}" style="font-size:2.5rem"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $doc->titre }}</div>
                            @if($doc->description)
                                <small class="text-muted d-block mb-1">{{ Str::limit($doc->description, 80) }}</small>
                            @endif
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge bg-primary">{{ $doc->matiere }}</span>
                                @if($doc->filiere)<span class="badge bg-secondary">{{ $doc->filiere }}</span>@endif
                                @if($doc->niveau)<span class="badge bg-info text-dark">{{ $doc->niveau }}</span>@endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>{{ $doc->professeur->prenom }} {{ $doc->professeur->nom }}
                                    &nbsp;·&nbsp; {{ $doc->tailleFormatee }}
                                </small>
                                <a href="{{ route('etudiant.documents_cours.telecharger', $doc) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ $doc->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($documents->hasPages())
            <div class="mt-4">{{ $documents->links() }}</div>
        @endif
    @endif
</div>
@endsection
