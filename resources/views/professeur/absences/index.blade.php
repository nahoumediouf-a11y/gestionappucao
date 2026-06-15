@extends('layouts.dashboard')

@section('title', 'Absences — SIGE UCAO')

@section('page-title', 'Absences enregistrées')

@section('page-actions')
    <a href="{{ route('professeur.absences.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Enregistrer une absence
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
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($absences as $absence)
                    <tr>
                        <td>{{ $absence->etudiant->user->nom_complet }} ({{ $absence->etudiant->matricule }})</td>
                        <td>{{ $absence->matiere }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($absence->date)->format('d/m/Y') }}</td>
                        <td>
                            @if ($absence->justifiee)
                                <span class="badge bg-success">Justifiée</span>
                            @else
                                <span class="badge bg-danger">Non justifiée</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('professeur.absences.edit', $absence) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune absence enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
