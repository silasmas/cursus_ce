<?php

namespace App\Filament\Resources\MentorProfiles\Pages;

use App\Filament\Resources\MentorProfiles\MentorProfileResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditMentorProfile extends EditRecord
{
    protected static string $resource = MentorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
