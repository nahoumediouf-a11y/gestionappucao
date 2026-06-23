@extends('layouts.dashboard')

@section('title', 'Cours en ligne — SIGE UCAO')

@section('page-title', 'Supervision des cours en ligne')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Titre</th>
                    <th>Professeur</th>
                    <th>Classe</th>
                    <th>Début prévu</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cours as $seance)
                    <tr>
                        <td class="fw-semibold">{{ $seance->titre }}</td>
                        <td>{{ $seance->professeur->nom_complet }}</td>
                        <td>{{ $seance->filiere }} {{ $seance->niveau }}</td>
                        <td>{{ $seance->debut_prevu->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-{{ $seance->statutCouleur() }}">{{ $seance->statutLabel() }}</span></td>
                        <td class="text-end">
                            @if (in_array($seance->statut, ['planifie', 'en_cours'], true))
                                <form method="POST" action="{{ route('admin.cours-en-ligne.annuler', $seance) }}" class="d-inline" onsubmit="return confirm('Annuler cette séance ?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-slash-circle"></i> Annuler
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun cours en ligne enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($cours->hasPages())
        <div class="card-body">{{ $cours->links() }}</div>
    @endif
</div>
@endsection
