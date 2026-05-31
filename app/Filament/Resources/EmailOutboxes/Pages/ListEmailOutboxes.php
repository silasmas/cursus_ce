<?php

namespace App\Filament\Resources\EmailOutboxes\Pages;

use App\Filament\Resources\EmailOutboxes\EmailOutboxResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListEmailOutboxes extends ListRecords
{
    protected static string $resource = EmailOutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
