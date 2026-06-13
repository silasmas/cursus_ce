<?php

/**
 * Textes de l'assistant de rédaction IA (Filament admin).
 */
return [
  'tooltip' => 'Générer avec l\'IA',

  'modal' => [
    'heading' => 'Générer avec l\'IA',
    'description' => 'Décrivez le texte souhaité. L\'IA rédigera un contenu en français pour le champ cible.',
    'submit' => 'Générer',
  ],

  'fields' => [
    'input_label' => 'Que souhaitez-vous rédiger ?',
    'input_placeholder' => 'Ex. : un paragraphe d\'introduction sur l\'Église selon la Bible, ton pastoral…',
    'input_helper' => 'Minimum 3 caractères. Soyez précis sur le sujet et le ton souhaité.',
    'tone_label' => 'Ton',
    'tone_placeholder' => 'Par défaut',
    'length_label' => 'Longueur',
    'length_placeholder' => 'Par défaut',
    'emojis_label' => 'Utiliser des émojis',
  ],

  'tones' => [
    'professional' => 'Professionnel',
    'casual' => 'Décontracté',
    'formal' => 'Formel',
    'friendly' => 'Amical',
    'persuasive' => 'Persuasif',
  ],

  'lengths' => [
    'short' => 'Court',
    'medium' => 'Moyen',
    'long' => 'Long',
  ],

  'validation' => [
    'input_required' => 'Décrivez ce que vous souhaitez générer.',
    'input_min' => 'Saisissez au moins :min caractères pour guider l\'IA.',
  ],

  'success' => [
    'title' => 'Texte généré',
    'body' => 'Le contenu a été inséré dans le champ. Relisez-le avant de publier.',
  ],

  'missing_context' => [
    'title' => 'Contexte incomplet',
    'body' => 'Renseignez d\'abord : :fields',
  ],

  'errors' => [
    'missing_api_key_title' => 'Clé API non configurée',
    'missing_api_key_body' => 'Ajoutez OPENAI_API_KEY dans le fichier .env (AI_WRITER_PROVIDER=openai), puis exécutez php artisan config:clear.',
    'unauthorized_title' => 'Clé API refusée',
    'unauthorized_body' => 'La clé OpenAI est invalide ou expirée. Vérifiez OPENAI_API_KEY sur platform.openai.com.',
    'forbidden_title' => 'Accès refusé',
    'forbidden_body' => 'Votre compte OpenAI n\'a pas accès à ce modèle ou à cette API.',
    'rate_limit_title' => 'Quota dépassé',
    'rate_limit_body' => 'Limite OpenAI atteinte. Réessayez plus tard ou vérifiez votre facturation.',
    'timeout_title' => 'Délai dépassé',
    'timeout_body' => 'OpenAI met trop de temps à répondre. Réessayez dans quelques instants.',
    'generic_title' => 'Génération impossible',
    'generic_body' => 'L\'assistant IA n\'a pas pu répondre. Réessayez ou contactez un administrateur.',
  ],
];
