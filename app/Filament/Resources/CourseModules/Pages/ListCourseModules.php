<?php

namespace App\Filament\Resources\CourseModules\Pages;

use App\Filament\Resources\CourseModules\CourseModuleResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

/**
 * Liste des modules de cours.
 */
class ListCourseModules extends ListRecords
{
  protected static string $resource = CourseModuleResource::class;

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()->label('Nouveau module'),
    ];
  }
}
