<?php

namespace App\Filament\Resources\PrayerSessions\Pages;

use App\Filament\Resources\PrayerSessions\PrayerSessionResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListPrayerSessions extends ListRecords
{
    protected static string $resource = PrayerSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
