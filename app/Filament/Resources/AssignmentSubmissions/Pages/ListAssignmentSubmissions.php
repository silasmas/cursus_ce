<?php

namespace App\Filament\Resources\AssignmentSubmissions\Pages;

use App\Filament\Resources\AssignmentSubmissions\AssignmentSubmissionResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListAssignmentSubmissions extends ListRecords
{
    protected static string $resource = AssignmentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
