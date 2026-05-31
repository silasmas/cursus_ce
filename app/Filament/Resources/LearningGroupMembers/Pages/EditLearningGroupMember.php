<?php

namespace App\Filament\Resources\LearningGroupMembers\Pages;

use App\Filament\Resources\LearningGroupMembers\LearningGroupMemberResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditLearningGroupMember extends EditRecord
{
    protected static string $resource = LearningGroupMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
