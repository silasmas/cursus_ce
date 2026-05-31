<?php

namespace App\Filament\Resources\LearningGroups\Pages;

use App\Filament\Resources\LearningGroups\LearningGroupResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditLearningGroup extends EditRecord
{
    protected static string $resource = LearningGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
