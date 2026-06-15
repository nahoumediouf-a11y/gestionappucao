@extends('layouts.dashboard')

@section('title', 'Notes — SIGE UCAO')

@section('page-title', 'Notes saisies')

@section('page-actions')
    <a href="{{ route('professeur.notes.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Saisir une note
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Matière</th>
                    <th>Session</th>
                    <th>Note / 20</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notes as $note)
                    <tr>
                        <td>{{ $note->etudiant->user->nom_complet }} ({{ $note->etudiant->matricule }})</td>
                        <td>{{ $note->matiere }}</td>
                        <td>{{ $note->session }}</td>
                        <td class="fw-bold">{{ $note->valeur }}</td>
                        <td class="text-end">
                            <a href="{{ route('professeur.notes.edit', $note) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune note saisie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
