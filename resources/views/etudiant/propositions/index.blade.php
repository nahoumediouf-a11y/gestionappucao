@extends('layouts.dashboard')

@section('title', 'Mes propositions de projets — SIGE UCAO')
@section('page-title', 'Mes propositions de projets')
@section('page-subtitle', 'Soumettez vos idées de projets personnels à l\'équipe pédagogique.')

@section('page-actions')
    <a href="{{ route('etudiant.propositions.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Proposer un projet
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
                    <th>Date de soumission</th>
                    <th>Statut</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($propositions as $proposition)
                    <tr>
                        <td class="fw-semibold">{{ $proposition->titre }}</td>
                        <td>{{ $proposition->matiere ?? '—' }}</td>
                        <td>{{ $proposition->created_at->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $proposition->statutBadge() }}">
                                {{ $proposition->statutLabel() }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $proposition->commentaire ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Aucune proposition soumise. <a href="{{ route('etudiant.propositions.create') }}">Proposez votre premier projet</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
