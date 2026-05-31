<?php

namespace App\Filament\Resources\Defenses\Pages;

use App\Filament\Resources\Defenses\DefenseResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListDefenses extends ListRecords
{
    protected static string $resource = DefenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
