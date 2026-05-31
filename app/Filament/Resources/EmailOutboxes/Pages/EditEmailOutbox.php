<?php

namespace App\Filament\Resources\EmailOutboxes\Pages;

use App\Filament\Resources\EmailOutboxes\EmailOutboxResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditEmailOutbox extends EditRecord
{
    protected static string $resource = EmailOutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
