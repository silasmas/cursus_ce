<?php

namespace App\Filament\Resources\MentorProfiles\Pages;

use App\Filament\Resources\MentorProfiles\MentorProfileResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListMentorProfiles extends ListRecords
{
    protected static string $resource = MentorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
