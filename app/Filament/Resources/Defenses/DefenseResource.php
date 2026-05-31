<?php

namespace App\Filament\Resources\Defenses;

use App\Filament\Resources\Defenses\Pages\CreateDefense;
use App\Filament\Resources\Defenses\Pages\EditDefense;
use App\Filament\Resources\Defenses\Pages\ListDefenses;
use App\Filament\Resources\Defenses\Schemas\DefenseForm;
use App\Filament\Resources\Defenses\Tables\DefensesTable;
use App\Models\Defense;
use BackedEnum;
use UnitEnum;
use App\Filament\Concerns\HasFrenchFilamentLabels;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DefenseResource extends Resource
{
    use HasFrenchFilamentLabels;

    protected static ?string $model = Defense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return DefenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DefensesTable::configure($table);
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
            'index' => ListDefenses::route('/'),
            'create' => CreateDefense::route('/create'),
            'edit' => EditDefense::route('/{record}/edit'),
        ];
    }
}
