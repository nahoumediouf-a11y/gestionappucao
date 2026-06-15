<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Absence;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\User;
use App\Notifications\SituationRougeNotification;
use Illuminate\Database\Seeder;

class EtudiantsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $prenoms = [
            'Awa', 'Moussa', 'Fatou', 'Ibrahima', 'Mariama', 'Cheikh', 'Aminata', 'Ousmane', 'Fatoumata', 'Mamadou',
            'Khady', 'Modou', 'Sokhna', 'Babacar', 'Ndeye', 'Abdou', 'Aissatou', 'Lamine', 'Coumba', 'Pape',
            'Bineta', 'Idrissa', 'Rokhaya', 'Seydou', 'Ndella',
        ];

        $noms = [
            'Diallo', 'Sow', 'Ndiaye', 'Diop', 'Fall', 'Gueye', 'Sarr', 'Ba', 'Cisse', 'Toure',
            'Kane', 'Mbaye', 'Faye', 'Sy', 'Camara', 'Sidibe', 'Bah', 'Diouf', 'Thiam', 'Niang',
            'Seck', 'Drame', 'Diagne', 'Wade', 'Sane',
        ];

        $filieres = ['Informatique', 'Gestion', 'Droit', 'Communication'];
        $niveaux = ['L1', 'L2', 'L3', 'M1', 'M2'];

        $matieresParFiliere = [
            'Informatique' => 'Algorithmique',
            'Gestion' => 'Comptabilité Générale',
            'Droit' => 'Droit Civil',
            'Communication' => "Communication d'entreprise",
        ];

        $modesPaiement = ['especes', 'virement', 'mobile_money'];

        $quartiers = ['Médina', 'Plateau', 'Sicap', 'Yoff', 'Parcelles Assainies', 'Liberté', 'Grand Yoff', 'Ouakam'];

        $villesNaissance = ['Dakar', 'Thiès', 'Saint-Louis', 'Kaolack', 'Ziguinchor', 'Mbour', 'Touba', 'Rufisque', 'Diourbel', 'Louga'];

        $profs = User::whereIn('login', ['prof', 'prof2', 'prof3', 'prof4', 'prof5'])->get()->values();
        $comptable = User::where('login', 'comptable')->first();
        $admin = User::where('login', 'admin')->first();

        $fraisTotal = 250000;

        for ($idx = 0; $idx < 50; $idx++) {
            $numero = $idx + 4; // etudiant4 .. etudiant53
            $prenom = $prenoms[$idx % count($prenoms)];
            $nom = $noms[($idx * 7) % count($noms)];
            $filiere = $filieres[$idx % count($filieres)];
            $niveau = $niveaux[$idx % count($niveaux)];
            $login = 'etudiant'.$numero;
            $matricule = (string) (1000681 + $idx); // 1000681 .. 1000730

            $telephone = sprintf('+221 7%d %03d %02d %02d', [6, 7, 8][$idx % 3], 100 + $idx, ($idx * 3) % 100, ($idx * 5) % 100);

            $user = User::updateOrCreate(
                ['login' => $login],
                [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $login.'@ucao.sn',
                    'telephone' => $telephone,
                    'password' => 'password',
                    'role' => Role::Etudiant,
                    'statut' => 'actif',
                ]
            );

            // Situation financière : 0 = à jour, 1 = paiement partiel, 2 = débiteur (aucun paiement), 3 = paiement rejeté
            $situation = $idx % 4;
            $solde = match ($situation) {
                0 => 0,
                1 => $fraisTotal - 150000,
                2, 3 => $fraisTotal,
            };

            $parentPrenom = $prenoms[($idx + 5) % count($prenoms)];
            $parentNom = $noms[($idx * 11 + 3) % count($noms)];
            $parentTelephone = sprintf('+221 7%d %03d %02d %02d', [6, 7, 8][($idx + 1) % 3], 200 + $idx, ($idx * 4) % 100, ($idx * 7) % 100);
            $adresse = sprintf('Cité %s, Villa %d, Dakar', $quartiers[$idx % count($quartiers)], 10 + $idx);
            $age = 19 + ($idx % 6);
            $dateNaissance = now()->subYears($age)->subDays(($idx * 17) % 365)->format('Y-m-d');
            $lieuNaissance = $villesNaissance[$idx % count($villesNaissance)];

            $etudiant = Etudiant::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'matricule' => $matricule,
                    'niveau' => $niveau,
                    'filiere' => $filiere,
                    'solde' => $solde,
                    'adresse' => $adresse,
                    'date_naissance' => $dateNaissance,
                    'lieu_naissance' => $lieuNaissance,
                    'contact_urgence_nom' => $parentPrenom.' '.$parentNom,
                    'contact_urgence_telephone' => $parentTelephone,
                ]
            );

            $reference = 'REC-2024-'.str_pad((string) (1010 + $idx), 4, '0', STR_PAD_LEFT);

            match ($situation) {
                0 => Paiement::updateOrCreate(
                    ['reference' => $reference],
                    [
                        'etudiant_id' => $etudiant->id,
                        'agent_id' => $comptable->id,
                        'date_paiement' => now()->subMonths(2),
                        'montant' => $fraisTotal,
                        'mode_paiement' => $modesPaiement[$idx % 3],
                        'statut' => 'valide',
                    ]
                ),
                1 => Paiement::updateOrCreate(
                    ['reference' => $reference],
                    [
                        'etudiant_id' => $etudiant->id,
                        'agent_id' => $comptable->id,
                        'date_paiement' => now()->subMonth(),
                        'montant' => 150000,
                        'mode_paiement' => $modesPaiement[$idx % 3],
                        'statut' => 'valide',
                    ]
                ),
                3 => Paiement::updateOrCreate(
                    ['reference' => $reference],
                    [
                        'etudiant_id' => $etudiant->id,
                        'agent_id' => $comptable->id,
                        'date_paiement' => now()->subWeeks(1),
                        'montant' => $fraisTotal,
                        'mode_paiement' => $modesPaiement[$idx % 3],
                        'statut' => 'annule',
                    ]
                ),
                default => null, // situation 2 : débiteur, aucun paiement enregistré
            };

            // 5 étudiants (un par filière + un supplémentaire) atteignent la situation rouge (3 absences non justifiées)
            if ($idx % 10 === 0) {
                $matiere = $matieresParFiliere[$filiere];
                $prof = $profs[$idx % max($profs->count(), 1)] ?? null;

                $absence = null;
                for ($a = 0; $a < 3; $a++) {
                    $absence = Absence::updateOrCreate(
                        ['etudiant_id' => $etudiant->id, 'matiere' => $matiere, 'date' => now()->subDays(20 - $a * 5)->toDateString()],
                        ['professeur_id' => $prof?->id, 'justifiee' => false]
                    );
                }

                if ($admin && $absence && $etudiant->enSituationRouge()) {
                    $admin->notify(new SituationRougeNotification($etudiant, $absence));
                }
            }
        }
    }
}
