<?php

namespace App\Filament\Resources\ReportSnapshots\Pages;

use App\Filament\Resources\ReportSnapshots\ReportSnapshotResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditReportSnapshot extends EditRecord
{
    protected static string $resource = ReportSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
