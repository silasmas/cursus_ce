<?php

namespace App\Filament\Resources\MentoringFeedback\Pages;

use App\Filament\Resources\MentoringFeedback\MentoringFeedbackResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListMentoringFeedback extends ListRecords
{
    protected static string $resource = MentoringFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
