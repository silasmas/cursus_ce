<?php

namespace App\Filament\Resources\AssignmentSubmissions\Pages;

use App\Filament\Resources\AssignmentSubmissions\AssignmentSubmissionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditAssignmentSubmission extends EditRecord
{
    protected static string $resource = AssignmentSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
