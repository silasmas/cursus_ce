<?php

namespace App\Filament\Resources\StudentAcademicRecords\Pages;

use App\Filament\Resources\StudentAcademicRecords\StudentAcademicRecordResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListStudentAcademicRecords extends ListRecords
{
    protected static string $resource = StudentAcademicRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
