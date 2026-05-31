<?php

namespace App\Filament\Resources\PrayerSessionAttendees\Pages;

use App\Filament\Resources\PrayerSessionAttendees\PrayerSessionAttendeeResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListPrayerSessionAttendees extends ListRecords
{
    protected static string $resource = PrayerSessionAttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
