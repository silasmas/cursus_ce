<?php

namespace App\Filament\Resources\ChapterProgress\Pages;

use App\Filament\Resources\ChapterProgress\ChapterProgressResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

class EditChapterProgress extends EditRecord
{
    protected static string $resource = ChapterProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
