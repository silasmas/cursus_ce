<?php

namespace App\Filament\Resources\MentoringReports\Pages;

use App\Filament\Resources\MentoringReports\MentoringReportResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditMentoringReport extends EditRecord
{
    protected static string $resource = MentoringReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
