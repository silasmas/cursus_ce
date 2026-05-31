<?php

namespace App\Filament\Resources\AttemptAnswers\Pages;

use App\Filament\Resources\AttemptAnswers\AttemptAnswerResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListAttemptAnswers extends ListRecords
{
    protected static string $resource = AttemptAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
