<?php

return [
  'fields' => [
    'recurrence' => [
      'label' => 'Récurrence',
      'start_date' => 'Date de début',
      'start_date_time' => 'Date et heure de début',
      'repeats' => 'Répétition',
      'fused_repeats' => 'Répéter tous les',
      'timezone' => 'Fuseau horaire',
      'interval' => 'Intervalle',
      'repeat_on' => 'Répéter le',
      'repeat_by' => 'Répéter par',
      'day_of_month' => 'Jour du mois',
      'day_of_week' => 'Jour de la semaine',
      'in_months' => 'Mois concernés',
      'ends' => 'Fin',
      'never' => 'Jamais',
      'on_date' => 'À la date',
      'after_occurrences' => 'Après un nombre d\'occurrences',
      'end_date' => 'Date de fin',
      'occurrences' => 'Nombre d\'occurrences',
      'preview' => 'Aperçu',
      'next_occurrences' => 'Prochaines occurrences',
      'preview_on_calendar' => 'Aperçu sur le calendrier',
      'calendar_modal_close' => 'Fermer',
    ],
  ],

  'frequencies' => [
    'DAILY' => 'Quotidien',
    'WEEKLY' => 'Hebdomadaire',
    'MONTHLY' => 'Mensuel',
    'YEARLY' => 'Annuel',
  ],

  'frequency_units' => [
    'daily' => '{1} jour|[2,*] jours',
    'weekly' => '{1} semaine|[2,*] semaines',
    'monthly' => '{1} mois|[2,*] mois',
    'yearly' => '{1} an|[2,*] ans',
  ],

  'intervals' => [
    'days' => 'jour(s)',
    'weeks' => 'semaine(s)',
    'months' => 'mois',
    'years' => 'an(s)',
  ],

  'weekdays' => [
    'MO' => 'Lundi',
    'TU' => 'Mardi',
    'WE' => 'Mercredi',
    'TH' => 'Jeudi',
    'FR' => 'Vendredi',
    'SA' => 'Samedi',
    'SU' => 'Dimanche',
  ],

  'weekday_letters' => [
    'SU' => 'D',
    'MO' => 'L',
    'TU' => 'M',
    'WE' => 'M',
    'TH' => 'J',
    'FR' => 'V',
    'SA' => 'S',
  ],

  'positions' => [
    '1' => 'Premier',
    '2' => 'Deuxième',
    '3' => 'Troisième',
    '4' => 'Quatrième',
    '-1' => 'Dernier',
  ],

  'months' => [
    '1' => 'Janvier',
    '2' => 'Février',
    '3' => 'Mars',
    '4' => 'Avril',
    '5' => 'Mai',
    '6' => 'Juin',
    '7' => 'Juillet',
    '8' => 'Août',
    '9' => 'Septembre',
    '10' => 'Octobre',
    '11' => 'Novembre',
    '12' => 'Décembre',
  ],

  'messages' => [
    'no_recurrence' => 'Aucune récurrence',
    'invalid_recurrence' => 'Schéma de récurrence invalide',
    'unable_to_preview' => 'Impossible de générer l\'aperçu',
  ],

  'preview' => [
    'repeats' => 'Répète :pattern',
    'starting_only' => 'à partir du :date',
    'until_only' => 'jusqu\'au :date',
    'date_range' => 'du :start au :end',
    'for_occurrences' => 'pendant :count occurrences',
    'at_time' => 'à :time',
  ],
];
