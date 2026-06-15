@extends('layouts.dashboard')

@section('title', 'Ajouter un créneau — SIGE UCAO')

@section('page-title', 'Ajouter un créneau')

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.emploi-du-temps.store') }}">
            @csrf
            @include('admin.emploi-du-temps._form')

            <div class="mt-4">
                <button type="submit" class="btn btn-ucao">
                    <i class="bi bi-check-circle me-1"></i>Ajouter le créneau
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
