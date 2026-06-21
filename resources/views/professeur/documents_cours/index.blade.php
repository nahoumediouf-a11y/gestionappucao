@extends('layouts.dashboard')
@section('title', 'Mes documents de cours')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-richtext text-primary me-2"></i>Documents de cours</h4>
        <a href="{{ route('professeur.documents_cours.create') }}" class="btn btn-primary">
            <i class="bi bi-cloud-upload me-1"></i> Publier un document
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if($documents->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <i class="bi bi-folder2-open text-muted" style="font-size:3rem"></i>
            <p class="text-muted mt-3">Aucun document publié. Cliquez sur « Publier un document » pour commencer.</p>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Document</th>
                            <th>Matière</th>
                            <th>Filière / Niveau</th>
                            <th>Taille</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $icons = ['pdf'=>'bi-file-earmark-pdf text-danger','doc'=>'bi-file-earmark-word text-primary','docx'=>'bi-file-earmark-word text-primary','ppt'=>'bi-file-earmark-slides text-warning','pptx'=>'bi-file-earmark-slides text-warning','xls'=>'bi-file-earmark-excel text-success','xlsx'=>'bi-file-earmark-excel text-success','zip'=>'bi-file-zip text-secondary'];
                                        $icon = $icons[strtolower($doc->type_fichier)] ?? 'bi-file-earmark text-muted';
                                    @endphp
                                    <i class="bi {{ $icon }} fs-4"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $doc->titre }}</div>
                                        @if($doc->description)<small class="text-muted">{{ Str::limit($doc->description, 60) }}</small>@endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-primary">{{ $doc->matiere }}</span></td>
                            <td>
                                @if($doc->filiere)<span class="badge bg-secondary me-1">{{ $doc->filiere }}</span>@endif
                                @if($doc->niveau)<span class="badge bg-info text-dark">{{ $doc->niveau }}</span>@endif
                                @if(!$doc->filiere && !$doc->niveau)<span class="text-muted small">Tous</span>@endif
                            </td>
                            <td class="text-muted small">{{ $doc->tailleFormatee }}</td>
                            <td class="text-muted small">{{ $doc->created_at->format('d/m/Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('professeur.documents_cours.destroy', $doc) }}" onsubmit="return confirm('Supprimer ce document ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($documents->hasPages())
                <div class="card-footer">{{ $documents->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
