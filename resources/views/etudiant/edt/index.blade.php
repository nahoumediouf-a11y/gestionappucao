@extends('layouts.dashboard')

@section('title', 'Emploi du temps — Recouvrement UCAO')

@section('page-title', 'Emploi du temps')
@section('page-subtitle', $etudiant->filiere.' — '.$etudiant->niveau)

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Jour</th>
                    <th>Horaire</th>
                    <th>Matière</th>
                    <th>Salle</th>
                    <th>Professeur</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($creneaux as $creneau)
                    <tr>
                        <td>{{ $creneau->jour }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($creneau->heure_debut)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($creneau->heure_fin)->format('H:i') }}</td>
                        <td>{{ $creneau->matiere }}</td>
                        <td>{{ $creneau->salle }}</td>
                        <td>{{ $creneau->professeur?->nom_complet ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun créneau programmé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
