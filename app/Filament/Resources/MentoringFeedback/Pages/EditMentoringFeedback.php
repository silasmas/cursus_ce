<?php

namespace App\Filament\Resources\MentoringFeedback\Pages;

use App\Filament\Resources\MentoringFeedback\MentoringFeedbackResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditMentoringFeedback extends EditRecord
{
    protected static string $resource = MentoringFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
