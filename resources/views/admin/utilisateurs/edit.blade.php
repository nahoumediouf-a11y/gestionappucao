@extends('layouts.dashboard')

@section('title', 'Modifier un utilisateur — Recouvrement UCAO')

@section('page-title', 'Modifier l\'utilisateur : '.$user->nom_complet)

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.utilisateurs.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.utilisateurs._form')

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
