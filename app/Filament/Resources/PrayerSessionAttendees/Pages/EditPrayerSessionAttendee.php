<?php

namespace App\Filament\Resources\PrayerSessionAttendees\Pages;

use App\Filament\Resources\PrayerSessionAttendees\PrayerSessionAttendeeResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditPrayerSessionAttendee extends EditRecord
{
    protected static string $resource = PrayerSessionAttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
