<?php

/**
 * Seeders exécutables depuis l'admin (Maintenance production).
 */
return [

  'groups' => [
    'production' => 'Démarrage production',
    'demo' => 'Démonstration (optionnel)',
  ],

  'seeders' => [

    'production-starter' => [
      'class' => Database\Seeders\ProductionStarterSeeder::class,
      'label' => 'Pack production complet',
      'description' => 'Cursus PHILA-CE, session ECAP 20, calendrier, règlement PDF (si présent) et permissions admin.',
      'group' => 'production',
      'color' => 'primary',
      'icon' => 'heroicon-o-rocket-launch',
      'confirm' => 'Charger le pack production complet ? Les données existantes seront mises à jour sans écraser les inscriptions.',
    ],

    'formation-content' => [
      'class' => Database\Seeders\FormationContentSeeder::class,
      'label' => 'Cursus & contenu pédagogique',
      'description' => '5 cursus PHILA-CE, cours ECAP, modules, chapitres, quiz de fin de module et contenus vidéo.',
      'group' => 'production',
      'color' => 'info',
      'icon' => 'heroicon-o-academic-cap',
      'confirm' => 'Exécuter le seeder des cursus et du contenu pédagogique ?',
    ],

    'ecap-session' => [
      'class' => Database\Seeders\EcapProductionSessionSeeder::class,
      'label' => 'Session ECAP 20 (2026)',
      'description' => 'Session active, fenêtre d\'inscription publique et vacations Jaspe / Topaz.',
      'group' => 'production',
      'color' => 'success',
      'icon' => 'heroicon-o-calendar-days',
      'confirm' => 'Créer ou mettre à jour la session ECAP 20 de référence ?',
    ],

    'ecap-calendar' => [
      'class' => Database\Seeders\EcapSession20CalendarSeeder::class,
      'label' => 'Calendrier session 20',
      'description' => 'Périodes pédagogiques, modules datés et activités (TP, méditations, TFE).',
      'group' => 'production',
      'color' => 'warning',
      'icon' => 'heroicon-o-calendar',
      'confirm' => 'Charger le calendrier ECAP session 20 ?',
    ],

    'legal-documents' => [
      'class' => Database\Seeders\LegalDocumentSeeder::class,
      'label' => 'Documents légaux ECAP',
      'description' => 'Règlement intérieur PDF (database/seeders/assets/ecap-reglement-interieur.pdf).',
      'group' => 'production',
      'color' => 'gray',
      'icon' => 'heroicon-o-document-text',
      'confirm' => 'Importer le règlement intérieur ECAP ?',
    ],

    'admin-permissions' => [
      'class' => Database\Seeders\AdminPermissionsSeeder::class,
      'label' => 'Permissions admin',
      'description' => 'Synchronise les permissions Shield sur super_admin et panel_user.',
      'group' => 'production',
      'color' => 'info',
      'icon' => 'heroicon-o-shield-check',
      'confirm' => 'Synchroniser les permissions administrateur ?',
    ],

    'portal-demo' => [
      'class' => Database\Seeders\PortalDemoSeeder::class,
      'label' => 'Données démo portail',
      'description' => 'Quiz, TP et mentor de test — utile en staging, à éviter en production réelle.',
      'group' => 'demo',
      'color' => 'danger',
      'icon' => 'heroicon-o-beaker',
      'confirm' => 'Charger les données de démonstration du portail ? Réservé aux environnements de test.',
    ],

  ],

];
