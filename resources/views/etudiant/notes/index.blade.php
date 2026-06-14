@extends('layouts.dashboard')

@section('title', 'Mes notes — Recouvrement UCAO')

@section('page-title', 'Mes notes')

@section('page-actions')
    <a href="{{ route('etudiant.bulletin.index') }}" class="btn btn-ucao" target="_blank">
        <i class="bi bi-file-earmark-text me-1"></i>Voir le bulletin
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="text-muted small">Moyenne générale</div>
        <div class="fs-3 fw-bold">{{ $moyenne }} / 20</div>
    </div>
</div>

@forelse ($parSession as $session => $notesSession)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <strong>{{ $session }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Note / 20</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notesSession as $note)
                        <tr>
                            <td>{{ $note->matiere }}</td>
                            <td class="fw-bold">{{ $note->valeur }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="alert alert-info">Aucune note disponible pour le moment.</div>
@endforelse
@endsection
