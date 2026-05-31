<?php

namespace App\Filament\Resources\ContentBlockProgress\Pages;

use App\Filament\Resources\ContentBlockProgress\ContentBlockProgressResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditContentBlockProgress extends EditRecord
{
    protected static string $resource = ContentBlockProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
