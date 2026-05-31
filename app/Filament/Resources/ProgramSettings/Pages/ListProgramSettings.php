<?php

namespace App\Filament\Resources\ProgramSettings\Pages;

use App\Filament\Resources\ProgramSettings\ProgramSettingResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListProgramSettings extends ListRecords
{
    protected static string $resource = ProgramSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
