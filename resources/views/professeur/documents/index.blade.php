@extends('layouts.dashboard')

@section('title', 'Documents de cours — SIGE UCAO')

@section('page-title', 'Documents de cours')
@section('page-subtitle', 'Mettez à disposition vos supports de cours pour vos étudiants.')

@section('page-actions')
    <a href="{{ route('professeur.documents.create') }}" class="btn btn-ucao">
        <i class="bi bi-cloud-upload me-1"></i>Ajouter un document
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Titre</th>
                    <th>Matière</th>
                    <th>Filière / Niveau</th>
                    <th>Fichier</th>
                    <th>Ajouté le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>
                            {{ $document->titre }}
                            @if ($document->description)
                                <div class="small text-muted">{{ $document->description }}</div>
                            @endif
                        </td>
                        <td>{{ $document->matiere }}</td>
                        <td>{{ $document->filiere }} {{ $document->niveau }}</td>
                        <td>
                            <i class="bi bi-file-earmark me-1"></i>{{ $document->nom_original }}
                            <span class="text-muted small">({{ $document->tailleLisible() }})</span>
                        </td>
                        <td>{{ $document->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('professeur.documents.download', $document) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST" action="{{ route('professeur.documents.destroy', $document) }}" class="d-inline" onsubmit="return confirm('Supprimer ce document ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun document mis en ligne.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
