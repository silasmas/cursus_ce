<?php

namespace App\Filament\Resources\ReportSnapshots\Pages;

use App\Filament\Resources\ReportSnapshots\ReportSnapshotResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListReportSnapshots extends ListRecords
{
    protected static string $resource = ReportSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
