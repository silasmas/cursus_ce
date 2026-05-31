<?php

namespace App\Filament\Resources\PrayerSessions\Pages;

use App\Filament\Resources\PrayerSessions\PrayerSessionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditPrayerSession extends EditRecord
{
    protected static string $resource = PrayerSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
