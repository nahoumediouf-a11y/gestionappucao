@extends('layouts.dashboard')

@section('title', 'Gestion des salles et emploi du temps — SIGE UCAO')

@section('page-title', 'Gestion des salles et emploi du temps')
@section('page-subtitle', "Gérer les créneaux et réaffecter les salles. Les étudiants et le professeur concernés sont notifiés automatiquement en cas de changement de salle.")

@section('page-actions')
    <a href="{{ route('admin.emploi-du-temps.create') }}" class="btn btn-ucao">
        <i class="bi bi-plus-circle me-1"></i>Ajouter un créneau
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="filiere" class="form-control" placeholder="Filière" value="{{ $filiere }}">
            </div>
            <div class="col-md-4">
                <input type="text" name="niveau" class="form-control" placeholder="Niveau" value="{{ $niveau }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Filière / Niveau</th>
                    <th>Jour</th>
                    <th>Horaire</th>
                    <th>Matière</th>
                    <th>Type</th>
                    <th>Salle</th>
                    <th>Professeur</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($creneaux as $creneau)
                    <tr>
                        <td>{{ $creneau->filiere }} {{ $creneau->niveau }}</td>
                        <td>{{ $creneau->jour }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($creneau->heure_debut)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($creneau->heure_fin)->format('H:i') }}</td>
                        <td>{{ $creneau->matiere }}</td>
                        <td><span class="badge bg-{{ $creneau->typeCouleur() }}">{{ $creneau->type }}</span></td>
                        <td><span class="badge bg-info-subtle text-info">{{ $creneau->salle }}</span></td>
                        <td>{{ $creneau->professeur?->nom_complet ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.emploi-du-temps.edit', $creneau) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.emploi-du-temps.destroy', $creneau) }}" class="d-inline" onsubmit="return confirm('Supprimer ce créneau ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucun créneau programmé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($creneaux->hasPages())
        <div class="card-footer bg-white">
            {{ $creneaux->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
