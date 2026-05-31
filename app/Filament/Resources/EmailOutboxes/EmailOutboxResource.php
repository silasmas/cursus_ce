<?php

namespace App\Filament\Resources\EmailOutboxes;

use App\Filament\Resources\EmailOutboxes\Pages\CreateEmailOutbox;
use App\Filament\Resources\EmailOutboxes\Pages\EditEmailOutbox;
use App\Filament\Resources\EmailOutboxes\Pages\ListEmailOutboxes;
use App\Filament\Resources\EmailOutboxes\Schemas\EmailOutboxForm;
use App\Filament\Resources\EmailOutboxes\Tables\EmailOutboxesTable;
use App\Models\EmailOutbox;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailOutboxResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = EmailOutbox::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $recordTitleAttribute = 'to_email';

    public static function form(Schema $schema): Schema
    {
        return EmailOutboxForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailOutboxesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailOutboxes::route('/'),
            'create' => CreateEmailOutbox::route('/create'),
            'edit' => EditEmailOutbox::route('/{record}/edit'),
        ];
    }
}
