<?php

namespace App\Services;

use App\Models\Etudiant;
use Paydunya\Setup;
use Paydunya\Checkout\CheckoutInvoice;

class PaydunyaService
{
    public function __construct()
    {
        Setup::setMasterKey(config('paydunya.master_key'));
        Setup::setPrivateKey(config('paydunya.private_key'));
        Setup::setToken(config('paydunya.token'));
        Setup::setMode(config('paydunya.mode'));

        Setup::setStoreName(config('paydunya.store.name'));
        Setup::setStoreTagline(config('paydunya.store.tagline'));
        Setup::setStorePhoneNumber(config('paydunya.store.phone'));
        Setup::setStorePostalAddress(config('paydunya.store.postal_addr'));
        Setup::setStoreWebsiteUrl(config('paydunya.store.website_url'));

        Setup::setCallbackUrl(url(config('paydunya.callback_url')));
        Setup::setReturnUrl(url(config('paydunya.return_url')));
        Setup::setCancelUrl(url(config('paydunya.cancel_url')));
    }

    public function creerFacture(Etudiant $etudiant, int $montant, string $reference): array
    {
        $invoice = new CheckoutInvoice();

        $invoice->addItem(
            'Frais de scolarité '.$etudiant->filiere.' '.$etudiant->niveau,
            1,
            $montant,
            $montant,
            'Paiement scolarité — '.$etudiant->matricule
        );

        $invoice->setTotalAmount($montant);
        $invoice->addCustomData('etudiant_id', $etudiant->id);
        $invoice->addCustomData('matricule', $etudiant->matricule);
        $invoice->addCustomData('reference', $reference);
        $invoice->addCustomData('nom_etudiant', $etudiant->user->nom_complet);

        $invoice->setDescription('Paiement scolarité UCAO — '.$etudiant->user->nom_complet.' ('.$etudiant->matricule.')');

        if ($invoice->create()) {
            return [
                'success'      => true,
                'url'          => $invoice->getInvoiceUrl(),
                'token'        => $invoice->getToken(),
            ];
        }

        return [
            'success' => false,
            'error'   => $invoice->response_text ?? 'Erreur PayDunya',
        ];
    }

    public function verifierPaiement(string $token): array
    {
        $invoice = new CheckoutInvoice();

        if ($invoice->confirm($token)) {
            return [
                'success'    => true,
                'statut'     => $invoice->getStatus(),
                'montant'    => $invoice->getTotalAmount(),
                'custom_data'=> $invoice->getCustomData(),
            ];
        }

        return ['success' => false, 'statut' => 'pending'];
    }
}
