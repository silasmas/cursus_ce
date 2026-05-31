<?php

namespace App\Filament\Resources\LearningGroups\Pages;

use App\Filament\Resources\LearningGroups\LearningGroupResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListLearningGroups extends ListRecords
{
    protected static string $resource = LearningGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
