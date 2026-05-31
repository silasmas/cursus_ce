<?php

namespace App\Filament\Resources\CourseModules\Pages;

use App\Filament\Resources\CourseModules\CourseModuleResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

/**
 * Édition d'un module de cours (chapitres + quiz M5).
 */
class EditCourseModule extends EditRecord
{
  protected static string $resource = CourseModuleResource::class;

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()->label('Supprimer'),
    ];
  }
}
