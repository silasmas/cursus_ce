<?php

namespace App\Filament\Resources\ProgramSettings\Pages;

use App\Filament\Resources\ProgramSettings\ProgramSettingResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditProgramSetting extends EditRecord
{
    protected static string $resource = ProgramSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
