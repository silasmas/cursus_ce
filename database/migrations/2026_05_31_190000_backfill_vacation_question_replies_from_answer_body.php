<?php

use App\Models\VacationQuestion;
use App\Models\VacationQuestionReply;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
  /**
   * Recrée les lignes de réponse manquantes à partir de answer_body (données antérieures).
   */
  public function up(): void
  {
    VacationQuestion::query()
      ->whereNotNull('answer_body')
      ->whereNotNull('answered_by_user_id')
      ->orderBy('id')
      ->each(function (VacationQuestion $question): void {
        $exists = VacationQuestionReply::query()
          ->where('vacation_question_id', $question->id)
          ->where('user_id', $question->answered_by_user_id)
          ->where('reply_type', 'answer')
          ->exists();

        if ($exists) {
          return;
        }

        $answeredAt = $question->answered_at ?? $question->updated_at ?? now();

        VacationQuestionReply::query()->create([
          'vacation_question_id' => $question->id,
          'user_id' => $question->answered_by_user_id,
          'body' => $question->answer_body,
          'reply_type' => 'answer',
          'version' => 1,
          'created_at' => $answeredAt,
          'updated_at' => $answeredAt,
        ]);
      });
  }

  /**
   * Aucune suppression : les réponses backfillées restent en base.
   */
  public function down(): void
  {
    // Rien à annuler sans risque de perte de données.
  }
};
