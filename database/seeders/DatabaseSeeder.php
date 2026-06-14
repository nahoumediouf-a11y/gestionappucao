<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Absence;
use App\Models\EmploiDuTemps;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nom' => 'Diouf',
                'prenom' => 'Nahoume',
                'login' => 'admin',
                'email' => 'admin@ucao.sn',
                'password' => 'password',
                'role' => Role::Administrateur,
            ],
            [
                'nom' => 'Ndiaye',
                'prenom' => 'Awa',
                'login' => 'comptable',
                'email' => 'comptable@ucao.sn',
                'password' => 'password',
                'role' => Role::AgentComptable,
            ],
            [
                'nom' => 'Fall',
                'prenom' => 'Moussa',
                'login' => 'recouvrement',
                'email' => 'recouvrement@ucao.sn',
                'password' => 'password',
                'role' => Role::AgentRecouvrement,
            ],
            [
                'nom' => 'Sarr',
                'prenom' => 'Fatou',
                'login' => 'financier',
                'email' => 'financier@ucao.sn',
                'password' => 'password',
                'role' => Role::ResponsableFinancier,
            ],
            [
                'nom' => 'Gueye',
                'prenom' => 'Cheikh',
                'login' => 'prof',
                'email' => 'prof@ucao.sn',
                'password' => 'password',
                'role' => Role::Professeur,
            ],
            [
                'nom' => 'Faye',
                'prenom' => 'Aminata',
                'login' => 'etudiant1',
                'email' => 'etudiant1@ucao.sn',
                'password' => 'password',
                'role' => Role::Etudiant,
            ],
            [
                'nom' => 'Camara',
                'prenom' => 'Ibrahima',
                'login' => 'etudiant2',
                'email' => 'etudiant2@ucao.sn',
                'password' => 'password',
                'role' => Role::Etudiant,
            ],
            [
                'nom' => 'Sidibe',
                'prenom' => 'Fatoumata',
                'login' => 'etudiant3',
                'email' => 'etudiant3@ucao.sn',
                'password' => 'password',
                'role' => Role::Etudiant,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['login' => $data['login']],
                array_merge($data, ['statut' => 'actif'])
            );
        }

        $comptable = User::where('login', 'comptable')->first();
        $recouvrement = User::where('login', 'recouvrement')->first();
        $prof = User::where('login', 'prof')->first();

        $etu1 = Etudiant::updateOrCreate(
            ['user_id' => User::where('login', 'etudiant1')->first()->id],
            ['matricule' => 'UCAO-2024-001', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 150000]
        );

        $etu2 = Etudiant::updateOrCreate(
            ['user_id' => User::where('login', 'etudiant2')->first()->id],
            ['matricule' => 'UCAO-2024-002', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 0]
        );

        $etu3 = Etudiant::updateOrCreate(
            ['user_id' => User::where('login', 'etudiant3')->first()->id],
            ['matricule' => 'UCAO-2024-003', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 75000]
        );

        // Paiements
        Paiement::updateOrCreate(
            ['reference' => 'REC-2024-0001'],
            [
                'etudiant_id' => $etu1->id,
                'agent_id' => $comptable->id,
                'date_paiement' => now()->subMonths(2),
                'montant' => 100000,
                'mode_paiement' => 'especes',
                'statut' => 'valide',
            ]
        );

        Paiement::updateOrCreate(
            ['reference' => 'REC-2024-0002'],
            [
                'etudiant_id' => $etu2->id,
                'agent_id' => $comptable->id,
                'date_paiement' => now()->subMonth(),
                'montant' => 250000,
                'mode_paiement' => 'virement',
                'statut' => 'valide',
            ]
        );

        Paiement::updateOrCreate(
            ['reference' => 'REC-2024-0003'],
            [
                'etudiant_id' => $etu3->id,
                'agent_id' => $comptable->id,
                'date_paiement' => now()->subWeeks(2),
                'montant' => 175000,
                'mode_paiement' => 'mobile_money',
                'statut' => 'valide',
            ]
        );

        // Notes
        $notes = [
            ['etudiant_id' => $etu1->id, 'matiere' => 'Algorithmique', 'valeur' => 14.5, 'session' => 'Semestre 1'],
            ['etudiant_id' => $etu1->id, 'matiere' => 'Base de données', 'valeur' => 16.0, 'session' => 'Semestre 1'],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Algorithmique', 'valeur' => 11.0, 'session' => 'Semestre 1'],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Base de données', 'valeur' => 13.5, 'session' => 'Semestre 1'],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Algorithmique', 'valeur' => 17.0, 'session' => 'Semestre 1'],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Base de données', 'valeur' => 15.5, 'session' => 'Semestre 1'],
        ];

        foreach ($notes as $note) {
            Note::updateOrCreate(
                ['etudiant_id' => $note['etudiant_id'], 'matiere' => $note['matiere'], 'session' => $note['session']],
                array_merge($note, ['professeur_id' => $prof->id])
            );
        }

        // Absences
        Absence::updateOrCreate(
            ['etudiant_id' => $etu1->id, 'matiere' => 'Algorithmique', 'date' => now()->subDays(10)->toDateString()],
            ['professeur_id' => $prof->id, 'justifiee' => false]
        );

        Absence::updateOrCreate(
            ['etudiant_id' => $etu2->id, 'matiere' => 'Base de données', 'date' => now()->subDays(5)->toDateString()],
            ['professeur_id' => $prof->id, 'justifiee' => true]
        );

        Absence::updateOrCreate(
            ['etudiant_id' => $etu3->id, 'matiere' => 'Réseaux', 'date' => now()->subDays(3)->toDateString()],
            ['professeur_id' => $prof->id, 'justifiee' => false]
        );

        // Emploi du temps (Informatique L3)
        $creneaux = [
            ['jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Algorithmique', 'salle' => 'A101'],
            ['jour' => 'Mardi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Base de données', 'salle' => 'A102'],
            ['jour' => 'Jeudi', 'heure_debut' => '14:00', 'heure_fin' => '16:00', 'matiere' => 'Réseaux', 'salle' => 'A103'],
        ];

        foreach ($creneaux as $creneau) {
            EmploiDuTemps::updateOrCreate(
                ['filiere' => 'Informatique', 'niveau' => 'L3', 'jour' => $creneau['jour'], 'matiere' => $creneau['matiere']],
                array_merge($creneau, ['professeur_id' => $prof->id])
            );
        }

        // Engagement de paiement pour l'étudiant débiteur
        EngagementPaiement::updateOrCreate(
            ['etudiant_id' => $etu1->id, 'echeance' => now()->addDays(15)->toDateString()],
            [
                'agent_id' => $recouvrement->id,
                'date' => now()->subDays(2),
                'montant' => 150000,
                'statut' => 'en_attente',
            ]
        );
    }
}
