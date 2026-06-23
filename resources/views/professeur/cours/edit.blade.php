@extends('layouts.dashboard')

@section('title', 'Modifier un cours en ligne — SIGE UCAO')

@section('page-title', 'Modifier un cours en ligne')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('professeur.cours.update', $cours) }}">
            @method('PUT')
            @include('professeur.cours._form')

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer
                </button>
                <a href="{{ route('professeur.cours.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
