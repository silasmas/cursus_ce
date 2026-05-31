<?php

namespace App\Filament\Resources\MentoringReports\Pages;

use App\Filament\Resources\MentoringReports\MentoringReportResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListMentoringReports extends ListRecords
{
    protected static string $resource = MentoringReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
