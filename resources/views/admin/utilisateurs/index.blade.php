@extends('layouts.dashboard')

@section('title', 'Gestion des utilisateurs — Recouvrement UCAO')

@section('page-title', 'Gestion des utilisateurs')
@section('page-subtitle', 'Ajouter, modifier ou supprimer un utilisateur et gérer les rôles.')

@section('page-actions')
    <a href="{{ route('admin.utilisateurs.create') }}" class="btn btn-ucao">
        <i class="bi bi-person-plus me-1"></i>Ajouter un utilisateur
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Login</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Détails</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->nom_complet }}</td>
                        <td><code>{{ $user->login }}</code></td>
                        <td>{{ $user->email ?? '—' }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $user->role->label() }}</span></td>
                        <td>
                            <span class="badge bg-{{ $user->statut === 'actif' ? 'success' : 'secondary' }}">
                                {{ ucfirst($user->statut) }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            @if ($user->etudiant)
                                {{ $user->etudiant->matricule }} — {{ $user->etudiant->filiere }} {{ $user->etudiant->niveau }}
                                — Solde: {{ number_format($user->etudiant->solde, 0, ',', ' ') }} FCFA
                                @if ($user->etudiant->enSituationRouge())
                                    <span class="badge bg-danger ms-1">Situation rouge</span>
                                @endif
                                <br>
                                <i class="bi bi-telephone"></i> {{ $user->telephone ?? '—' }}
                                · <i class="bi bi-geo-alt"></i> {{ $user->etudiant->adresse ?? '—' }}
                                <br>
                                <i class="bi bi-person-heart"></i> Urgence : {{ $user->etudiant->contact_urgence_nom ?? '—' }}
                                ({{ $user->etudiant->contact_urgence_telephone ?? '—' }})
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.utilisateurs.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.utilisateurs.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
