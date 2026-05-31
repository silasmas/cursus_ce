<?php

namespace App\Filament\Resources\LearningGroupMembers\Pages;

use App\Filament\Resources\LearningGroupMembers\LearningGroupMemberResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListLearningGroupMembers extends ListRecords
{
    protected static string $resource = LearningGroupMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
