<?php

namespace App\Filament\Resources\MentoringDecisions\Pages;

use App\Filament\Resources\MentoringDecisions\MentoringDecisionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditMentoringDecision extends EditRecord
{
    protected static string $resource = MentoringDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
