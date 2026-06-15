@extends('layouts.dashboard')

@section('title', 'Projets de classe — SIGE UCAO')

@section('page-title', 'Projets de classe')

@section('page-actions')
    <a href="{{ route('professeur.projets.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Assigner un projet
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
                    <th>Échéance</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projets as $projet)
                    <tr>
                        <td>{{ $projet->titre }}</td>
                        <td>{{ $projet->matiere }}</td>
                        <td>{{ $projet->filiere }} {{ $projet->niveau }}</td>
                        <td>{{ $projet->date_limite->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $projet->statut() === 'Terminé' ? 'secondary' : 'success' }}">
                                {{ $projet->statut() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('professeur.projets.edit', $projet) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('professeur.projets.destroy', $projet) }}" class="d-inline" onsubmit="return confirm('Supprimer ce projet ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun projet assigné.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
