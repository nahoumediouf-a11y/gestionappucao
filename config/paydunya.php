<?php

return [
    'master_key'  => env('PAYDUNYA_MASTER_KEY', ''),
    'private_key' => env('PAYDUNYA_PRIVATE_KEY', ''),
    'token'       => env('PAYDUNYA_TOKEN', ''),
    'mode'        => env('PAYDUNYA_MODE', 'test'), // 'test' ou 'live'

    'store' => [
        'name'        => 'UCAO Saint Michel',
        'tagline'     => 'Paiement des frais de scolarité',
        'phone'       => '+221 33 000 00 00',
        'postal_addr' => 'Dakar, Sénégal',
        'logo_url'    => '',
        'website_url' => env('APP_URL', 'http://127.0.0.1:8000'),
    ],

    'return_url'  => '/etudiant/paiements/retour',
    'cancel_url'  => '/etudiant/paiements',
    'callback_url'=> '/etudiant/paiements/callback',
];
