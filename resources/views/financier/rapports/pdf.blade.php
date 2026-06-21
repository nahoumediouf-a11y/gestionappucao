<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport financier — SIGE UCAO</title>
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
        .total-box {
            text-align: center;
            margin-bottom: 24px;
        }
        .total-box .label {
            color: #6c757d;
            font-size: 11px;
        }
        .total-box .amount {
            font-size: 20px;
            font-weight: bold;
            color: #198754;
        }
        h2 {
            font-size: 13px;
            margin: 18px 0 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .footer {
            margin-top: 24px;
            font-size: 9px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIGE UCAO — Rapport financier</h1>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="total-box">
        <div class="label">Total général encaissé</div>
        <div class="amount">{{ number_format($total, 0, ',', ' ') }} FCFA</div>
    </div>

    <h2>Répartition par mode de paiement</h2>
    <table>
        <thead>
            <tr><th>Mode</th><th>Nombre</th><th class="text-end">Total</th></tr>
        </thead>
        <tbody>
            @forelse ($parMode as $ligne)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $ligne->mode_paiement)) }}</td>
                    <td>{{ $ligne->nb }}</td>
                    <td class="text-end">{{ number_format($ligne->total, 0, ',', ' ') }} FCFA</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Répartition par mois</h2>
    <table>
        <thead>
            <tr><th>Mois</th><th>Nombre</th><th class="text-end">Total</th></tr>
        </thead>
        <tbody>
            @forelse ($parMois as $ligne)
                <tr>
                    <td>{{ $ligne->mois }}</td>
                    <td>{{ $ligne->nb }}</td>
                    <td class="text-end">{{ number_format($ligne->total, 0, ',', ' ') }} FCFA</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement par le SIGE UCAO — Système Intégré de Gestion des Étudiants.
    </div>
</body>
</html>
