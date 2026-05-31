<?php

namespace App\Filament\Resources\MentoringDecisions\Pages;

use App\Filament\Resources\MentoringDecisions\MentoringDecisionResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListMentoringDecisions extends ListRecords
{
    protected static string $resource = MentoringDecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
