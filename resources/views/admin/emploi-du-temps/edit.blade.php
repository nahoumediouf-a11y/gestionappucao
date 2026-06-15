@extends('layouts.dashboard')

@section('title', 'Modifier un créneau — SIGE UCAO')

@section('page-title', 'Modifier le créneau : '.$creneau->matiere)

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.emploi-du-temps.update', $creneau) }}">
            @csrf
            @method('PUT')
            @include('admin.emploi-du-temps._form')

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
