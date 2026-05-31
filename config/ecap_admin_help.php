<?php

/**
 * Textes d'aide contextuelle pour l'administration ECAP et pédagogique.
 */
return [
  'pages' => [
    'list_academic_sessions' => 'Liste des sessions ECAP. Cliquez sur une ligne puis « Modifier » pour accéder au calendrier, aux périodes pédagogiques et aux vacations.',
    'create_academic_session' => 'Créez une nouvelle session : le code ECAP-XXXXXX est généré automatiquement. Configurez ensuite les onglets calendrier, périodes et vacations.',
    'edit_academic_session' => 'Gérez le déroulé complet de cette session via les onglets ci-dessous.',
    'list_course_modules' => 'Modules de cours par programme. Pour ECAP, configurez le quiz de fin de module dans l\'onglet dédié de chaque module.',
    'edit_course_module' => 'Contenu du module : chapitres et quiz de validation obligatoire (5 questions, 80 %).',
    'list_assessments' => 'Toutes les évaluations : quiz de chapitre, quiz de fin de module ECAP, TP et examens.',
    'edit_assessment' => 'Paramètres du test. Pour un quiz de fin de module, ajoutez exactement 5 questions avec un chapitre de révision par question.',
  ],

  'relation_managers' => [
    'module_schedules' => 'Planifiez ici le déroulé chronologique de la session : modules de cours et activités (conférences, examens, cérémonies…). Chaque entrée peut être rattachée à une période pédagogique (cours, TFE, défenses).',
    'session_periods' => 'Définissez les grandes fenêtres pédagogiques de la session (cours, travaux de fin d\'études, défenses), puis affectez-y les contenus (modules, chapitres, évaluations). Le portail fidèle n\'affiche que les contenus de la période active.',
    'session_vacations' => 'Créneaux présentiel proposés à l\'inscription ECAP. Indiquez la tranche horaire (ex. 08h00–12h00). Le fidèle choisit une vacation s\'il s\'inscrit en présentiel.',
    'learning_groups' => 'Groupes de vacation : petites communautés d\'étude réparties équitablement sur toute la session ECAP. Les membres y sont affectés automatiquement.',
    'module_exit_quiz' => 'Quiz obligatoire de fin de module ECAP : exactement 5 questions, seuil 80 %. Chaque question doit être liée à un chapitre de révision. Le module suivant reste verrouillé tant que le quiz n\'est pas réussi.',
    'chapters' => 'Chapitres composant ce module. Publiez-les pour qu\'ils apparaissent dans le parcours fidèle.',
    'questions' => 'Questions du test. Pour un quiz de fin de module : 5 questions QCM, chacune avec un chapitre de révision en cas d\'erreur.',
  ],

  'fidèle_module_visibility' => 'Un module et ses chapitres apparaissent côté fidèle si : (1) accès programme ECAP ouvert, (2) cours et chapitres publiés, (3) module listé au calendrier de la session si des entrées existent, (4) chapitre rattaché à la période pédagogique active si des périodes sont configurées, (5) déblocage progressif chapitre par chapitre + quiz fin de module.',

  'admin_create_module_flow' => 'Cours (Contenu pédagogique) → Module de cours → Chapitres (+ blocs) → Évaluations/TP. Puis Session ECAP : calendrier (dates module), périodes (contenus), affectations acteurs.',
];
