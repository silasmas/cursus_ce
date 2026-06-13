<?php

/**
 * Textes de la visite guidée admin PHILA-CE.
 */
return [
  'tooltip' => 'Visite guidée',

  'welcome' => [
    'title' => 'Bienvenue sur PHILA-CE Admin',
    'text' => '<strong>Découvrez les sections du panneau d\'administration</strong> dans l\'ordre du menu latéral.',
    'buttons' => [
      'skip' => 'Passer',
      'start' => 'Commencer',
    ],
  ],

  'finish' => [
    'title' => 'Visite terminée',
    'text' => '<strong>Vous connaissez maintenant les principales entrées du menu.</strong><br><br>Relancez la visite à tout moment via l\'icône 🎓 du menu utilisateur.',
    'buttons' => [
      'back' => 'Précédent',
      'finish' => 'Terminer',
    ],
  ],

  'buttons' => [
    'next' => 'Suivant',
    'previous' => 'Précédent',
    'cancel' => 'Fermer',
    'complete' => 'Terminer',
  ],

  'steps' => [
    'maintenance-production' => 'Exécutez migrations, Shield, storage et seeders sans SSH. L\'encart CI/CD en bas du panneau fournit les commandes curl pour la route HTTP de déploiement.',
  ],
];
