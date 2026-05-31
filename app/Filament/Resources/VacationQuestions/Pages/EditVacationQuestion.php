<?php

namespace App\Filament\Resources\VacationQuestions\Pages;

use App\Enums\VacationQuestionStatus;
use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\VacationQuestions\VacationQuestionResource;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

/**
 * Édition et réponse à une question vacation ECAP.
 */
class EditVacationQuestion extends EditRecord
{
  protected static string $resource = VacationQuestionResource::class;

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make(),
    ];
  }

  /**
   * Marque la question comme répondue lors de l'enregistrement d'une réponse.
   *
   * @param  array<string, mixed>  $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeSave(array $data): array
  {
    if (filled($data['answer_body'] ?? null)) {
      $data['status'] = VacationQuestionStatus::Answered->value;
      $data['answered_at'] = $data['answered_at'] ?? now();
      $data['answered_by_user_id'] = $data['answered_by_user_id'] ?? Auth::id();
    }

    return $data;
  }
}
