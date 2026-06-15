@extends('layouts.dashboard')

@section('title', 'Assistant IA — Recouvrement UCAO')

@section('page-title', 'Assistant IA')
@section('page-subtitle', "Posez vos questions sur votre situation académique, financière ou vos absences.")

@section('page-content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div id="ucao-chat-messages" class="mb-3" style="max-height: 50vh; overflow-y: auto;">
            <div class="d-flex mb-2">
                <div class="bg-light rounded-3 p-3">
                    <i class="bi bi-robot me-1"></i>
                    Bonjour {{ auth()->user()->prenom }} ! Je suis l'assistant de Recouvrement UCAO. Posez-moi une question sur votre profil, vos notes, vos absences, votre solde, votre emploi du temps ou vos projets de classe.
                </div>
            </div>
        </div>

        <form id="ucao-chat-form" class="d-flex gap-2">
            @csrf
            <input type="text" id="ucao-chat-input" class="form-control" placeholder="Écrivez votre question..." autocomplete="off" required>
            <button type="submit" class="btn btn-ucao">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('ucao-chat-form');
    const input = document.getElementById('ucao-chat-input');
    const messages = document.getElementById('ucao-chat-messages');
    const csrfToken = form.querySelector('input[name="_token"]').value;
    let historique = [];

    function addMessage(content, fromUser) {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex mb-2' + (fromUser ? ' justify-content-end' : '');

        const bubble = document.createElement('div');
        bubble.className = (fromUser ? 'bg-primary text-white' : 'bg-light') + ' rounded-3 p-3';
        bubble.style.maxWidth = '80%';
        bubble.style.whiteSpace = 'pre-wrap';
        if (!fromUser) {
            bubble.innerHTML = '<i class="bi bi-robot me-1"></i>';
        }
        bubble.append(document.createTextNode(content));

        wrapper.appendChild(bubble);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        addMessage(message, true);
        input.value = '';
        input.disabled = true;

        try {
            const response = await fetch('{{ route('assistant.ask') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message, historique }),
            });

            const data = await response.json();
            const reponse = data.reponse ?? "Désolé, une erreur est survenue.";

            addMessage(reponse, false);

            historique.push({ role: 'user', content: message });
            historique.push({ role: 'assistant', content: reponse });
        } catch (err) {
            addMessage("Désolé, une erreur réseau est survenue.", false);
        } finally {
            input.disabled = false;
            input.focus();
        }
    });
})();
</script>
@endpush
@endsection
