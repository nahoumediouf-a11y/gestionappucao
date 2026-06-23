@extends('layouts.dashboard')

@section('title', $cours->titre.' — Cours en ligne — SIGE UCAO')

@section('page-title', $cours->titre)

@section('page-subtitle', $cours->filiere.' '.$cours->niveau.($estModerateur ? ' — vous animez cette séance' : ''))

@section('page-actions')
    <a href="{{ $cours->lienVisio() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir dans un nouvel onglet
    </a>
    <a href="{{ $retourUrl }}" class="btn btn-outline-secondary">
        <i class="bi bi-box-arrow-left me-1"></i>Quitter
    </a>
@endsection

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div id="jitsi-container" style="min-height: 600px;"></div>
    </div>
</div>
<noscript>
    <div class="alert alert-warning mt-3">
        Activez JavaScript pour lancer la visioconférence, ou
        <a href="{{ $cours->lienVisio() }}" target="_blank" rel="noopener">ouvrez la salle dans un nouvel onglet</a>.
    </div>
</noscript>
@endsection

@push('scripts')
<script src="https://{{ config('services.jitsi.domain') }}/external_api.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof JitsiMeetExternalAPI === 'undefined') {
            return;
        }

        var estModerateur = @json($estModerateur);
        var boutonsModerateur = ['microphone', 'camera', 'desktop', 'chat', 'raisehand', 'participants-pane',
            'tileview', 'fullscreen', 'settings', 'mute-everyone', 'security', 'hangup'];
        var boutonsEtudiant = ['microphone', 'camera', 'chat', 'raisehand', 'tileview', 'fullscreen', 'hangup'];

        var api = new JitsiMeetExternalAPI(@json(config('services.jitsi.domain')), {
            roomName: @json($cours->room_name),
            parentNode: document.getElementById('jitsi-container'),
            width: '100%',
            height: 600,
            userInfo: {
                displayName: @json(auth()->user()->nom_complet),
                email: @json(auth()->user()->email),
            },
            configOverwrite: {
                prejoinPageEnabled: true,
                startWithAudioMuted: ! estModerateur,
                startWithVideoMuted: ! estModerateur,
                disableDeepLinking: true,
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: estModerateur ? boutonsModerateur : boutonsEtudiant,
                SHOW_JITSI_WATERMARK: false,
            },
        });

        api.addEventListener('readyToClose', function () {
            window.location = @json($retourUrl);
        });
    });
</script>
@endpush
