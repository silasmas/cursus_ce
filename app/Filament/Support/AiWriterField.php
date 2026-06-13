<?php

namespace App\Filament\Support;

use App\Filament\Support\PhilaAiWriterAction;

/**
 * Fabrique des actions Filament AI Writer en français pour PHILA-CE.
 */
class AiWriterField
{
  /**
   * Génère le contenu texte d'un bloc de chapitre.
   *
   * @return PhilaAiWriterAction Action attachée au champ body
   */
  public static function chapterContent(): PhilaAiWriterAction
  {
    return PhilaAiWriterAction::make('ai_chapter_content')
      ->targetField('body')
      ->contextFields(['title', 'type'])
      ->prompt(
        'Tu es rédacteur pédagogique pour PHILA-CE (formation biblique ECAP, Cité d\'Exaucement). '
        .'Rédige un contenu clair, structuré et accessible en français, adapté au titre et au type de bloc fournis. '
        .'Utilise des paragraphes courts. Ton respectueux et pédagogique. '
        .'Retourne UNIQUEMENT le texte du contenu, sans titre ni commentaire meta.',
      );
  }

  /**
   * Génère la description d'un cursus (programme).
   *
   * @return AiWriterAction Action attachée au champ description
   */
  public static function programDescription(): PhilaAiWriterAction
  {
    return PhilaAiWriterAction::make('ai_program_description')
      ->targetField('description')
      ->contextFields(['name', 'slug'])
      ->prompt(
        'Rédige une description courte (3 à 5 phrases) en français pour un cursus de formation PHILA-CE. '
        .'Explique l\'objectif, le public visé et ce que le fidèle va apprendre. '
        .'Retourne UNIQUEMENT la description.',
      );
  }

  /**
   * Génère l'énoncé d'une question de quiz.
   *
   * @return AiWriterAction Action attachée au champ stem
   */
  public static function questionStem(): PhilaAiWriterAction
  {
    return PhilaAiWriterAction::make('ai_question_stem')
      ->targetField('stem')
      ->contextFields(['type'])
      ->prompt(
        'Rédige l\'énoncé d\'une question de quiz biblique ou pédagogique en français. '
        .'Une seule question claire, sans révéler la réponse. '
        .'Adapte le niveau à une formation ECAP. '
        .'Retourne UNIQUEMENT l\'énoncé.',
      );
  }

  /**
   * Rédige une réponse officielle à une question vacation ECAP.
   *
   * @return AiWriterAction Action attachée au champ answer_body
   */
  public static function vacationAnswer(): PhilaAiWriterAction
  {
    return PhilaAiWriterAction::make('ai_vacation_answer')
      ->targetField('answer_body')
      ->contextFields(['subject', 'body'])
      ->prompt(
        'Tu es enseignant ECAP PHILA-CE. Rédige une réponse pédagogique, bienveillante et bibliquement fondée '
        .'à la question du fidèle (contexte fourni). Structure en paragraphes courts. '
        .'Retourne UNIQUEMENT le texte de la réponse, en français.',
      );
  }

  /**
   * Génère la description d'une activité du calendrier ECAP.
   *
   * @return AiWriterAction Action attachée au champ description
   */
  public static function calendarActivityDescription(): PhilaAiWriterAction
  {
    return PhilaAiWriterAction::make('ai_calendar_description')
      ->targetField('description')
      ->contextFields(['title', 'item_type'])
      ->prompt(
        'Rédige une description courte (1 à 3 phrases) en français pour une activité ou un module '
        .'du calendrier ECAP. Indique l\'objectif pédagogique ou pastoral. '
        .'Retourne UNIQUEMENT la description.',
      );
  }

  /**
   * Génère le résumé d'un document légal affiché aux fidèles.
   *
   * @return AiWriterAction Action attachée au champ summary
   */
  public static function legalDocumentSummary(): PhilaAiWriterAction
  {
    return PhilaAiWriterAction::make('ai_legal_summary')
      ->targetField('summary')
      ->contextFields(['title', 'version'])
      ->prompt(
        'Rédige un résumé en français (2 à 4 phrases) d\'un document légal ou réglementaire PHILA-CE, '
        .'compréhensible par un fidèle. Ne remplace pas le PDF : résume l\'objet du document. '
        .'Retourne UNIQUEMENT le résumé.',
      );
  }
}
