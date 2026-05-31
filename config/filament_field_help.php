<?php

/**
 * Textes d'aide (helperText) pour les champs Filament ECAP et pédagogiques.
 */
return [
  'sort_order' => 'Position dans la liste : 1 = affiché en premier, 2 = ensuite, etc. Sert uniquement à l\'ordre d\'affichage (admin et portail), pas aux dates.',

  'academic_session' => [
    'name' => 'Nom lisible par l\'équipe et les fidèles (ex. « Session ECAP 2026 »).',
    'is_active' => 'Session visible sur le portail. Seule, cette case n\'ouvre pas les inscriptions : les dates ci-dessous déterminent la fenêtre d\'inscription.',
    'starts_on' => 'Date de début officielle de la vacation (session complète).',
    'ends_on' => 'Date de fin officielle de la vacation.',
    'registration_opens_at' => 'À partir de cette date et heure, le formulaire d\'inscription s\'ouvre. Laissez vide pour ouvrir dès que la session est active.',
    'registration_closes_at' => 'Après cette date et heure, les inscriptions se ferment et le countdown disparaît. Laissez vide pour ne pas fixer de limite.',
  ],

  'calendar' => [
    'item_type' => 'Module = cours ECAP planifié. Activité = événement (conférence, examen, cérémonie…).',
    'session_period_id' => 'Rattache cette entrée à une période pédagogique (cours, TFE ou défenses) si applicable.',
    'course_module_id' => 'Module de cours ECAP concerné par ce créneau.',
    'title' => 'Intitulé visible pour une activité (ex. « Journée de prière », « Examen module 1 »).',
    'description' => 'Précisions optionnelles pour l\'équipe (non affiché au fidèle pour l\'instant).',
    'starts_on' => 'Premier jour de cette entrée au calendrier.',
    'ends_on' => 'Dernier jour de cette entrée au calendrier.',
  ],

  'period' => [
    'type' => 'Grande phase de la session : cours, travaux de fin d\'études (TFE) ou défenses.',
    'name' => 'Libellé personnalisé. Laissez vide pour utiliser le nom du type (ex. « Période des cours »).',
    'starts_on' => 'La période devient active à cette date (contenus affectés visibles côté fidèle ECAP).',
    'ends_on' => 'La période se termine à cette date.',
    'is_active' => 'Décochez pour désactiver temporairement sans supprimer la période.',
    'content_type' => 'Type de contenu pédagogique à rendre disponible pendant cette période.',
    'content_id' => 'Élément précis (module, chapitre ou évaluation) lié à la période.',
    'content_label' => 'Nom affiché en admin uniquement ; laissez vide pour le libellé par défaut.',
  ],

  'vacation' => [
    'name' => 'Nom de la vacation présentiel (ex. « Vacation du matin »).',
    'code' => 'Code court interne (ex. MATIN, SOIR).',
    'time_starts' => 'Heure de début du créneau présentiel.',
    'time_ends' => 'Heure de fin du créneau présentiel.',
    'capacity_max' => 'Nombre maximum de fidèles pour cette vacation (optionnel).',
    'is_active' => 'Seules les vacations actives sont proposées à l\'inscription.',
  ],

  'learning_group' => [
    'name' => 'Nom du groupe de vacation (ex. « Groupe Alpha »).',
  ],

  'user' => [
    'name' => 'Nom affiché sur le portail et dans les e-mails (prénom et nom).',
    'email' => 'Adresse de connexion et de réception des notifications.',
    'email_verified_at' => 'Date de validation de l\'e-mail. Laissez vide si le fidèle doit confirmer via OTP.',
    'password' => 'Laissez vide à la modification pour conserver le mot de passe actuel. Minimum 8 caractères à la création.',
  ],

  'program_setting' => [
    'program_id' => 'Cursus PHILA-CE concerné (ECAP, Métamorpho…). Une seule configuration par cursus.',
    'linear_progression' => 'Si activé : chaque chapitre se débloque après le précédent (et le quiz de fin de module ECAP le cas échéant). Si désactivé : tous les chapitres ouverts du module sont accessibles.',
    'quiz_mandatory' => 'Si activé : le fidèle doit réussir les quiz de chapitre avant de pouvoir valider l\'étape (marquer le chapitre comme terminé).',
    'settings' => 'JSON optionnel pour des réglages futurs (seuils, textes, flags techniques).',
  ],

  'course' => [
    'program_id' => 'Cursus PHILA-CE auquel ce cours appartient (ECAP, Métamorpho, etc.).',
    'slug' => 'Identifiant technique dans l\'URL (ex. fondamentaux-apollos). Sans espaces, en minuscules.',
    'name' => 'Nom affiché dans l\'administration et le portail fidèle.',
    'sort_order' => 'Ordre d\'affichage parmi les cours du même programme.',
    'is_published' => 'Seuls les cours publiés sont visibles côté fidèle.',
  ],

  'media_asset' => [
    'disk' => 'Disque Laravel de stockage (généralement public ou local).',
    'path' => 'Chemin relatif du fichier dans le disque (ex. media/videos/intro.mp4).',
    'mime_type' => 'Type MIME du fichier (ex. video/mp4, application/pdf).',
    'size_bytes' => 'Taille du fichier en octets.',
    'duration_seconds' => 'Durée en secondes pour les fichiers audio/vidéo.',
    'transcode_status' => 'État du transcodage vidéo (pending, done, failed…).',
  ],

  'chapter' => [
    'course_id' => 'Cours parent (ex. parcours ECAP année 1).',
    'course_module_id' => 'Module auquel ce chapitre appartient. Obligatoire pour le parcours modulaire ECAP.',
    'title' => 'Titre affiché dans le menu du fidèle (Mon espace → cours).',
    'sort_order' => 'Ordre d\'affichage dans le module : 1 = premier chapitre.',
    'is_published' => 'Tant que cette case n\'est pas cochée, le chapitre reste invisible pour les fidèles.',
  ],

  'content_block' => [
    'chapter_id' => 'Chapitre contenant ce bloc. Préférez l\'onglet « Blocs » depuis le chapitre.',
    'type' => 'Type de contenu : texte, vidéo, audio, fichier, etc. Selon le type, certains champs deviennent obligatoires.',
    'sort_order' => 'Ordre de lecture dans le chapitre.',
    'title' => 'Titre optionnel au-dessus du bloc.',
    'body' => 'Texte HTML ou markdown selon le type « texte ».',
    'media_asset_id' => 'Fichier de la bibliothèque média (évite de re-téléverser le même PDF ou la même vidéo).',
    'url' => 'Lien externe (YouTube, Google Drive public, etc.) si pas de média uploadé.',
    'metadata' => 'JSON technique optionnel (durée, options du lecteur). Réservé aux cas avancés.',
  ],
];
