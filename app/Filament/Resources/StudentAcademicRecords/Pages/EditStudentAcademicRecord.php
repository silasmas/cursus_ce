<?php

namespace App\Filament\Resources\StudentAcademicRecords\Pages;

use App\Filament\Resources\StudentAcademicRecords\StudentAcademicRecordResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditStudentAcademicRecord extends EditRecord
{
    protected static string $resource = StudentAcademicRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
