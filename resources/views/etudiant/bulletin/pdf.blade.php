<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bulletin — {{ $etudiant->matricule }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #212529;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #0d6efd;
        }
        .header p {
            margin: 4px 0 0;
            color: #6c757d;
        }
        .infos {
            width: 100%;
            margin-bottom: 20px;
        }
        .infos td {
            vertical-align: top;
            padding: 4px 0;
        }
        .infos .label {
            color: #6c757d;
            font-size: 11px;
        }
        .infos .value {
            font-weight: bold;
            font-size: 13px;
        }
        .moyenne {
            text-align: right;
        }
        .moyenne .value {
            font-size: 22px;
            color: #198754;
        }
        .session-title {
            background-color: #f8f9fa;
            padding: 6px 10px;
            font-weight: bold;
            margin-top: 16px;
            border: 1px solid #dee2e6;
        }
        table.notes {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.notes th, table.notes td {
            border: 1px solid #dee2e6;
            padding: 6px 10px;
        }
        table.notes th {
            background-color: #f8f9fa;
            text-align: left;
        }
        table.notes td.note {
            text-align: right;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>UCAO Saint Michel</h1>
        <p>Bulletin de notes</p>
    </div>

    <table class="infos">
        <tr>
            <td>
                <div class="label">Étudiant</div>
                <div class="value">{{ $etudiant->user->nom_complet }}</div>
                <div class="label">{{ $etudiant->matricule }} — {{ $etudiant->filiere }} {{ $etudiant->niveau }}</div>
            </td>
            <td class="moyenne">
                <div class="label">Moyenne générale</div>
                <div class="value">{{ $moyenneGenerale }} / 20</div>
            </td>
        </tr>
    </table>

    @forelse ($parSession as $session => $data)
        <div class="session-title">
            {{ $session }} — Moyenne : {{ $data['moyenne'] }} / 20
        </div>
        <table class="notes">
            <thead>
                <tr>
                    <th>Matière</th>
                    <th class="note">Note / 20</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['notes'] as $note)
                    <tr>
                        <td>{{ $note->matiere }}</td>
                        <td class="note">{{ $note->valeur }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Aucune note disponible pour le moment.</p>
    @endforelse

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} — Recouvrement UCAO
    </div>
</body>
</html>
