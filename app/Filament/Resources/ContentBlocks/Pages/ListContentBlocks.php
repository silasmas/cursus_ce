<?php

namespace App\Filament\Resources\ContentBlocks\Pages;

use App\Filament\Resources\ContentBlocks\ContentBlockResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListContentBlocks extends ListRecords
{
    protected static string $resource = ContentBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouveau contenu'),
        ];
    }
}
