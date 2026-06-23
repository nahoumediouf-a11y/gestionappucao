<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Export CSV réutilisable, ouvrable directement dans Excel (BOM UTF-8 pour les
 * accents + séparateur « ; » attendu par Excel en locale française).
 */
class CsvExport
{
    /**
     * @param  array<int, string>  $entetes
     * @param  iterable<int, array<int, mixed>>  $lignes
     */
    public static function stream(string $nomFichier, array $entetes, iterable $lignes): StreamedResponse
    {
        return response()->streamDownload(function () use ($entetes, $lignes) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 : Excel reconnaît ainsi les caractères accentués.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $entetes, ';');
            foreach ($lignes as $ligne) {
                fputcsv($out, $ligne, ';');
            }
            fclose($out);
        }, $nomFichier, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
