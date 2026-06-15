<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $titre }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px;">
        <h2 style="color: #1e3a8a;">SIGE UCAO — Information aux parents</h2>

        <p>Madame, Monsieur,</p>

        <p>{{ $contenu }}</p>

        <p>
            Étudiant(e) concerné(e) : <strong>{{ $etudiant->user->nom_complet }}</strong><br>
            Matricule : {{ $etudiant->matricule }}<br>
            Filière / Niveau : {{ $etudiant->filiere }} {{ $etudiant->niveau }}
        </p>

        <p>Pour toute question, vous pouvez contacter l'administration de l'UCAO Saint Michel.</p>

        <p style="margin-top: 32px; color: #6b7280; font-size: 0.875rem;">
            Cet email est généré automatiquement par la plateforme de gestion académique SIGE UCAO. Merci de ne pas y répondre directement.
        </p>
    </div>
</body>
</html>
