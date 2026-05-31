<?php

/**
 * Définition des 5 cursus PHILA-CE (Cahier des Charges).
 *
 * Chaque entrée correspond à un programme (`Program.slug`) débloqué progressivement.
 */
return [

  'modules' => [
    [
      'slug' => 'connaissez-phila',
      'order' => 1,
      'name' => 'Connaissez-vous PHILA',
      'short_name' => 'PHILA',
      'subtitle' => 'Accueil & introduction',
      'description' => 'Découvrez la vision, la mission et les valeurs de la Cité d\'Exaucement.',
      'objective' => 'Accueillir et introduire le fidèle à l\'église.',
    ],
    [
      'slug' => 'metamorpho',
      'order' => 2,
      'name' => 'Métamorpho',
      'short_name' => 'Métamorpho',
      'subtitle' => 'Accompagnement spirituel',
      'description' => 'Parcours personnalisé avec mentor, guide PDF et rapports de suivi.',
      'objective' => 'Accompagnement spirituel personnalisé.',
    ],
    [
      'slug' => 'ecap',
      'order' => 3,
      'name' => 'ECAP',
      'short_name' => 'ECAP',
      'subtitle' => 'École d\'Apolos',
      'description' => 'Formation biblique structurée : cours, TP, examens et défense de travaux.',
      'objective' => 'Formation biblique structurée.',
    ],
    [
      'slug' => 'gifted',
      'order' => 4,
      'name' => 'École des dons',
      'short_name' => 'Gifted',
      'subtitle' => 'Identification des dons',
      'description' => 'Cours multimédias et test d\'évaluation pour orienter selon vos dons spirituels.',
      'objective' => 'Identification des dons spirituels.',
    ],
    [
      'slug' => 'eyano',
      'order' => 5,
      'name' => 'Eyano',
      'short_name' => 'Eyano',
      'subtitle' => 'École de prière',
      'description' => 'Formation pratique à la prière avec mentor et sessions en ligne.',
      'objective' => 'Formation pratique à la prière.',
    ],
  ],

  /*
  | Programmes où le mentor doit valider les TP avant progression.
  */
  'mentor_approval_programs' => ['metamorpho', 'ecap'],

  /*
  | Programmes où l'aval mentor seul débloque l'étape (sans formateur).
  */
  'mentor_only_tp_programs' => ['metamorpho'],

];
