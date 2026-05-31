<?php

namespace App\Filament\Resources\ChapterProgress\Pages;

use App\Filament\Resources\ChapterProgress\ChapterProgressResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

class ListChapterProgress extends ListRecords
{
    protected static string $resource = ChapterProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
