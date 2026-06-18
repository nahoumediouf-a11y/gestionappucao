@extends('layouts.dashboard')

@section('title', 'Mes absences — SIGE UCAO')

@section('page-title', 'Mes absences')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Matière</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($absences as $absence)
                    <tr>
                        <td>{{ $absence->date->format('d/m/Y') }}</td>
                        <td>{{ $absence->heure ? substr($absence->heure, 0, 5) : '—' }}</td>
                        <td>{{ $absence->matiere }}</td>
                        <td>
                            <span class="badge bg-{{ $absence->justifiee ? 'success' : 'danger' }}">
                                {{ $absence->justifiee ? 'Justifiée' : 'Non justifiée' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucune absence enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
