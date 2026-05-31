<?php

namespace App\Filament\Resources\ContentBlockProgress\Pages;

use App\Filament\Resources\ContentBlockProgress\ContentBlockProgressResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListContentBlockProgress extends ListRecords
{
    protected static string $resource = ContentBlockProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
