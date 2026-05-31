<?php

namespace App\Filament\Resources\Defenses\Pages;

use App\Filament\Resources\Defenses\DefenseResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditDefense extends EditRecord
{
    protected static string $resource = DefenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
