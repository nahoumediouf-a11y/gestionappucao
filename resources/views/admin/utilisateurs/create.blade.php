@extends('layouts.dashboard')

@section('title', 'Ajouter un utilisateur — SIGE UCAO')

@section('page-title', 'Ajouter un utilisateur')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.utilisateurs.store') }}">
            @csrf
            @include('admin.utilisateurs._form')

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Créer l'utilisateur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
