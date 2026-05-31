<?php

namespace App\Filament\Resources\ProgramAccesses\Pages;

use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProgramAccesses\ProgramAccessResource;
use App\Filament\Resources\ProgramAccesses\Schemas\ProgramAccessForm;
use App\Services\ProgramAccess\ProgramAccessStateService;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

/**
 * Édition d'un accès cursus (statut exclusif).
 */
class EditProgramAccess extends EditRecord
{
  protected static string $resource = ProgramAccessResource::class;

  /**
   * Préremplit le statut courant.
   *
   * @param  array<string, mixed>  $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeFill(array $data): array
  {
    $data['access_status'] = ProgramAccessForm::statusCode($this->getRecord());

    return $data;
  }

  /**
   * Applique le statut via le service métier.
   *
   * @param  array<string, mixed>  $data
   */
  protected function handleRecordUpdate(Model $record, array $data): Model
  {
    $service = app(ProgramAccessStateService::class);

    match ($data['access_status'] ?? 'pending') {
      'open' => $service->setOpen($record, 'admin_validated'),
      'needs_admin_validation' => $service->setNeedsAdminValidation($record, 'admin_validated'),
      'completed' => $service->setCompleted($record),
      'waived' => $service->setWaived($record),
      default => $service->setPending($record),
    };

    return $record->refresh();
  }

  /**
   * Actions d'en-tête.
   *
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()->label('Supprimer'),
    ];
  }
}
