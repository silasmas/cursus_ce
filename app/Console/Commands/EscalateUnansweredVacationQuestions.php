<?php

namespace App\Console\Commands;

use App\Services\Ecap\VacationQuestionService;
use Illuminate\Console\Command;

/**
 * Notifie admin et enseignants si une question ECAP n'a pas de réponse après 1 h.
 */
class EscalateUnansweredVacationQuestions extends Command
{
  /**
   * @var string
   */
  protected $signature = 'ecap:escalate-unanswered-questions';

  /**
   * @var string
   */
  protected $description = 'Alerte admin et enseignants pour les questions ECAP sans réponse depuis plus d\'une heure';

  /**
   * @param  VacationQuestionService  $vacationQuestionService  Service Q&R ECAP
   */
  public function handle(VacationQuestionService $vacationQuestionService): int
  {
    $count = $vacationQuestionService->escalateUnanswered();

    $this->info("Escalade effectuée pour {$count} question(s).");

    return self::SUCCESS;
  }
}
