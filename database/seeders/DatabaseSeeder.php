<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Absence;
use App\Models\EmploiDuTemps;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Paiement;
use App\Models\Projet;
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
                'nom' => 'Ba',
                'prenom' => 'Ousmane',
                'login' => 'prof2',
                'email' => 'prof2@ucao.sn',
                'password' => 'password',
                'role' => Role::Professeur,
            ],
            [
                'nom' => 'Diop',
                'prenom' => 'Mamadou',
                'login' => 'prof3',
                'email' => 'prof3@ucao.sn',
                'password' => 'password',
                'role' => Role::Professeur,
            ],
            [
                'nom' => 'Sow',
                'prenom' => 'Khadija',
                'login' => 'prof4',
                'email' => 'prof4@ucao.sn',
                'password' => 'password',
                'role' => Role::Professeur,
            ],
            [
                'nom' => 'Diallo',
                'prenom' => 'Modou',
                'login' => 'prof5',
                'email' => 'prof5@ucao.sn',
                'password' => 'password',
                'role' => Role::Professeur,
            ],
            [
                'nom' => 'Faye',
                'prenom' => 'Aminata',
                'login' => 'etudiant1',
                'email' => 'etudiant1@ucao.sn',
                'telephone' => '+221 77 123 45 01',
                'password' => 'password',
                'role' => Role::Etudiant,
            ],
            [
                'nom' => 'Camara',
                'prenom' => 'Ibrahima',
                'login' => 'etudiant2',
                'email' => 'etudiant2@ucao.sn',
                'telephone' => '+221 76 234 56 02',
                'password' => 'password',
                'role' => Role::Etudiant,
            ],
            [
                'nom' => 'Sidibe',
                'prenom' => 'Fatoumata',
                'login' => 'etudiant3',
                'email' => 'etudiant3@ucao.sn',
                'telephone' => '+221 78 345 67 03',
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
        $prof2 = User::where('login', 'prof2')->first();
        $prof3 = User::where('login', 'prof3')->first();
        $prof4 = User::where('login', 'prof4')->first();
        $prof5 = User::where('login', 'prof5')->first();

        $etu1 = Etudiant::updateOrCreate(
            ['user_id' => User::where('login', 'etudiant1')->first()->id],
            [
                'matricule' => '1000678', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 150000,
                'adresse' => 'Cité Sicap, Villa 12, Dakar',
                'date_naissance' => '2003-03-15',
                'lieu_naissance' => 'Dakar',
                'contact_urgence_nom' => 'Moussa Faye',
                'contact_urgence_telephone' => '+221 78 234 56 01',
            ]
        );

        $etu2 = Etudiant::updateOrCreate(
            ['user_id' => User::where('login', 'etudiant2')->first()->id],
            [
                'matricule' => '1000679', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 0,
                'adresse' => 'Cité Yoff, Villa 8, Dakar',
                'date_naissance' => '2002-07-22',
                'lieu_naissance' => 'Thiès',
                'contact_urgence_nom' => 'Mariama Camara',
                'contact_urgence_telephone' => '+221 77 345 67 02',
            ]
        );

        $etu3 = Etudiant::updateOrCreate(
            ['user_id' => User::where('login', 'etudiant3')->first()->id],
            [
                'matricule' => '1000680', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 75000,
                'adresse' => 'Cité Médina, Villa 21, Dakar',
                'date_naissance' => '2003-11-05',
                'lieu_naissance' => 'Saint-Louis',
                'contact_urgence_nom' => 'Cheikh Sidibe',
                'contact_urgence_telephone' => '+221 76 456 78 03',
            ]
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
            // Semestre 1
            ['etudiant_id' => $etu1->id, 'matiere' => 'Algorithmique', 'valeur' => 14.5, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu1->id, 'matiere' => 'Base de données', 'valeur' => 16.0, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu1->id, 'matiere' => 'Programmation Orientée Objet', 'valeur' => 15.0, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu1->id, 'matiere' => 'Mathématiques pour l\'informatique', 'valeur' => 13.0, 'session' => 'Semestre 1', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Algorithmique', 'valeur' => 11.0, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Base de données', 'valeur' => 13.5, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Programmation Orientée Objet', 'valeur' => 12.0, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Mathématiques pour l\'informatique', 'valeur' => 10.5, 'session' => 'Semestre 1', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Algorithmique', 'valeur' => 17.0, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Base de données', 'valeur' => 15.5, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Programmation Orientée Objet', 'valeur' => 16.5, 'session' => 'Semestre 1', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Mathématiques pour l\'informatique', 'valeur' => 14.0, 'session' => 'Semestre 1', 'professeur_id' => $prof3->id],

            // Semestre 2
            ['etudiant_id' => $etu1->id, 'matiere' => 'Réseaux', 'valeur' => 13.5, 'session' => 'Semestre 2', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu1->id, 'matiere' => 'Systèmes d\'exploitation', 'valeur' => 14.0, 'session' => 'Semestre 2', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu1->id, 'matiere' => 'Génie Logiciel', 'valeur' => 15.5, 'session' => 'Semestre 2', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Réseaux', 'valeur' => 10.0, 'session' => 'Semestre 2', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Systèmes d\'exploitation', 'valeur' => 12.5, 'session' => 'Semestre 2', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu2->id, 'matiere' => 'Génie Logiciel', 'valeur' => 11.0, 'session' => 'Semestre 2', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Réseaux', 'valeur' => 16.0, 'session' => 'Semestre 2', 'professeur_id' => $prof->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Systèmes d\'exploitation', 'valeur' => 17.5, 'session' => 'Semestre 2', 'professeur_id' => $prof3->id],
            ['etudiant_id' => $etu3->id, 'matiere' => 'Génie Logiciel', 'valeur' => 15.0, 'session' => 'Semestre 2', 'professeur_id' => $prof3->id],
        ];

        foreach ($notes as $note) {
            Note::updateOrCreate(
                ['etudiant_id' => $note['etudiant_id'], 'matiere' => $note['matiere'], 'session' => $note['session']],
                $note
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

        // Type de séance déduit de la matière (TP pour les matières pratiques,
        // TD pour les matières à exercices, CM par défaut).
        $typeSeance = function (string $matiere): string {
            $m = mb_strtolower($matiere);
            return match (true) {
                str_contains($m, 'base de données'), str_contains($m, 'programmation'), str_contains($m, 'réseaux'), str_contains($m, 'digital') => 'TP',
                str_contains($m, 'algorithmique'), str_contains($m, 'mathématiques'), str_contains($m, 'comptabilité'), str_contains($m, 'procédure') => 'TD',
                default => 'CM',
            };
        };

        // Emploi du temps (Informatique L3)
        $creneauxInfo = [
            ['jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Algorithmique', 'salle' => '1-1', 'professeur_id' => $prof->id],
            ['jour' => 'Lundi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Programmation Orientée Objet', 'salle' => '1-1', 'professeur_id' => $prof->id],
            ['jour' => 'Mardi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Systèmes d\'exploitation', 'salle' => '1-2', 'professeur_id' => $prof3->id],
            ['jour' => 'Mardi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Base de données', 'salle' => '1-2', 'professeur_id' => $prof->id],
            ['jour' => 'Mercredi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Génie Logiciel', 'salle' => '1-4', 'professeur_id' => $prof3->id],
            ['jour' => 'Jeudi', 'heure_debut' => '14:00', 'heure_fin' => '16:00', 'matiere' => 'Réseaux', 'salle' => '1-3', 'professeur_id' => $prof->id],
            ['jour' => 'Vendredi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Mathématiques pour l\'informatique', 'salle' => '1-1', 'professeur_id' => $prof3->id],
        ];

        foreach ($creneauxInfo as $creneau) {
            EmploiDuTemps::updateOrCreate(
                ['filiere' => 'Informatique', 'niveau' => 'L3', 'jour' => $creneau['jour'], 'matiere' => $creneau['matiere']],
                array_merge($creneau, ['type' => $typeSeance($creneau['matiere'])])
            );
        }

        // Emploi du temps (Gestion L3)
        $creneauxGestion = [
            ['jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Comptabilité Générale', 'salle' => '2-1', 'professeur_id' => $prof2->id],
            ['jour' => 'Mardi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Marketing', 'salle' => '2-2', 'professeur_id' => $prof2->id],
            ['jour' => 'Mercredi', 'heure_debut' => '14:00', 'heure_fin' => '16:00', 'matiere' => 'Management des Organisations', 'salle' => '2-1', 'professeur_id' => $prof2->id],
            ['jour' => 'Jeudi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Économie d\'Entreprise', 'salle' => '2-2', 'professeur_id' => $prof2->id],
        ];

        foreach ($creneauxGestion as $creneau) {
            EmploiDuTemps::updateOrCreate(
                ['filiere' => 'Gestion', 'niveau' => 'L3', 'jour' => $creneau['jour'], 'matiere' => $creneau['matiere']],
                array_merge($creneau, ['type' => $typeSeance($creneau['matiere'])])
            );
        }

        // Emploi du temps (Droit L3)
        $creneauxDroit = [
            ['jour' => 'Lundi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Droit Civil', 'salle' => '3-1', 'professeur_id' => $prof4->id],
            ['jour' => 'Mardi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Droit Constitutionnel', 'salle' => '3-2', 'professeur_id' => $prof4->id],
            ['jour' => 'Mercredi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Droit des Affaires', 'salle' => '3-1', 'professeur_id' => $prof4->id],
            ['jour' => 'Jeudi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Procédure Civile', 'salle' => '3-2', 'professeur_id' => $prof4->id],
            ['jour' => 'Vendredi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Histoire du Droit', 'salle' => '3-1', 'professeur_id' => $prof4->id],
        ];

        foreach ($creneauxDroit as $creneau) {
            EmploiDuTemps::updateOrCreate(
                ['filiere' => 'Droit', 'niveau' => 'L3', 'jour' => $creneau['jour'], 'matiere' => $creneau['matiere']],
                array_merge($creneau, ['type' => $typeSeance($creneau['matiere'])])
            );
        }

        // Emploi du temps (Communication L3)
        $creneauxCommunication = [
            ['jour' => 'Lundi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => "Communication d'entreprise", 'salle' => '4-1', 'professeur_id' => $prof5->id],
            ['jour' => 'Mardi', 'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Théories de la Communication', 'salle' => '4-2', 'professeur_id' => $prof5->id],
            ['jour' => 'Mercredi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Relations Publiques', 'salle' => '4-1', 'professeur_id' => $prof5->id],
            ['jour' => 'Jeudi', 'heure_debut' => '14:00', 'heure_fin' => '16:00', 'matiere' => 'Marketing Digital', 'salle' => '4-2', 'professeur_id' => $prof5->id],
            ['jour' => 'Vendredi', 'heure_debut' => '10:00', 'heure_fin' => '12:00', 'matiere' => 'Stratégie de Communication', 'salle' => '4-1', 'professeur_id' => $prof5->id],
        ];

        foreach ($creneauxCommunication as $creneau) {
            EmploiDuTemps::updateOrCreate(
                ['filiere' => 'Communication', 'niveau' => 'L3', 'jour' => $creneau['jour'], 'matiere' => $creneau['matiere']],
                array_merge($creneau, ['type' => $typeSeance($creneau['matiere'])])
            );
        }

        // Projets de classe
        $projets = [
            ['professeur_id' => $prof->id, 'titre' => 'Mini-projet : Tri et recherche', 'description' => 'Implémenter et comparer plusieurs algorithmes de tri (bulle, rapide, fusion) en pseudo-code et dans un langage au choix.', 'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Algorithmique', 'date_limite' => now()->addWeeks(2)->toDateString()],
            ['professeur_id' => $prof->id, 'titre' => 'Conception d\'une base de données', 'description' => 'Modéliser et créer une base de données relationnelle (MCD + script SQL) pour un système de gestion de bibliothèque.', 'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Base de données', 'date_limite' => now()->addWeeks(3)->toDateString()],
            ['professeur_id' => $prof->id, 'titre' => 'Application de gestion en POO', 'description' => 'Développer une petite application orientée objet (gestion de stock) avec héritage et interfaces.', 'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Programmation Orientée Objet', 'date_limite' => now()->addWeeks(4)->toDateString()],
            ['professeur_id' => $prof3->id, 'titre' => 'Étude comparative des systèmes d\'exploitation', 'description' => 'Rédiger un rapport comparant la gestion de la mémoire et des processus sous Linux et Windows.', 'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Systèmes d\'exploitation', 'date_limite' => now()->subDays(5)->toDateString()],
            ['professeur_id' => $prof3->id, 'titre' => 'Spécification d\'un projet logiciel', 'description' => 'Rédiger le cahier des charges et les diagrammes UML d\'une application de gestion de notes.', 'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Génie Logiciel', 'date_limite' => now()->addWeeks(5)->toDateString()],
            ['professeur_id' => $prof2->id, 'titre' => 'Étude de cas : tenue comptable', 'description' => 'Réaliser la comptabilisation des opérations courantes d\'une PME sur un exercice et établir le bilan.', 'filiere' => 'Gestion', 'niveau' => 'L3', 'matiere' => 'Comptabilité Générale', 'date_limite' => now()->addWeeks(2)->toDateString()],
            ['professeur_id' => $prof2->id, 'titre' => 'Plan marketing d\'un nouveau produit', 'description' => 'Élaborer un plan marketing complet (étude de marché, mix marketing, plan d\'action) pour un produit fictif.', 'filiere' => 'Gestion', 'niveau' => 'L3', 'matiere' => 'Marketing', 'date_limite' => now()->addWeeks(3)->toDateString()],
            ['professeur_id' => $prof4->id, 'titre' => 'Étude de jurisprudence', 'description' => 'Analyser et commenter une décision de justice récente en droit civil.', 'filiere' => 'Droit', 'niveau' => 'L3', 'matiere' => 'Droit Civil', 'date_limite' => now()->addWeeks(2)->toDateString()],
            ['professeur_id' => $prof4->id, 'titre' => 'Rédaction d\'un contrat commercial', 'description' => 'Rédiger un contrat de vente commerciale en respectant les clauses essentielles du droit des affaires.', 'filiere' => 'Droit', 'niveau' => 'L3', 'matiere' => 'Droit des Affaires', 'date_limite' => now()->addWeeks(4)->toDateString()],
            ['professeur_id' => $prof5->id, 'titre' => 'Plan de communication d\'entreprise', 'description' => 'Élaborer un plan de communication interne et externe pour une entreprise fictive.', 'filiere' => 'Communication', 'niveau' => 'L3', 'matiere' => "Communication d'entreprise", 'date_limite' => now()->addWeeks(2)->toDateString()],
            ['professeur_id' => $prof5->id, 'titre' => 'Campagne de marketing digital', 'description' => 'Concevoir une campagne de communication digitale (réseaux sociaux, contenus, calendrier) pour le lancement d\'un produit.', 'filiere' => 'Communication', 'niveau' => 'L3', 'matiere' => 'Marketing Digital', 'date_limite' => now()->addWeeks(3)->toDateString()],
        ];

        foreach ($projets as $projet) {
            Projet::updateOrCreate(
                ['filiere' => $projet['filiere'], 'niveau' => $projet['niveau'], 'matiere' => $projet['matiere'], 'titre' => $projet['titre']],
                $projet
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

        // 50 étudiants supplémentaires (filières variées, coordonnées, situations financières diverses)
        $this->call(EtudiantsDemoSeeder::class);

        // Cours en ligne de démonstration (Informatique L3)
        $this->call(CoursEnLigneSeeder::class);
    }
}
