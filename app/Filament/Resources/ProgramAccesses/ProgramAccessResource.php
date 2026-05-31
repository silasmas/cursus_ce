<?php

namespace App\Filament\Resources\ProgramAccesses;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\ProgramAccesses\Pages\EditProgramAccess;
use App\Filament\Resources\ProgramAccesses\Pages\ListProgramAccesses;
use App\Filament\Resources\ProgramAccesses\Schemas\ProgramAccessForm;
use App\Filament\Resources\ProgramAccesses\Tables\ProgramAccessesTable;
use App\Models\ProgramAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Accès aux cursus par utilisateur (validation admin).
 */
class ProgramAccessResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = ProgramAccess::class;

  protected static ?string $navigationLabel = 'Accès au cursus';

  protected static ?string $modelLabel = 'accès au cursus';

  protected static ?string $pluralModelLabel = 'Accès au cursus';

  protected static bool $hasTitleCaseModelLabel = false;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

  protected static string|\UnitEnum|null $navigationGroup = 'Gestion des cursus';

  protected static ?int $navigationSort = 3;

  protected static ?string $slug = 'acces-cursus';

  public static function form(Schema $schema): Schema
  {
    return ProgramAccessForm::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return ProgramAccessesTable::configure($table);
  }

  public static function getPages(): array
  {
    return [
      'index' => ListProgramAccesses::route('/'),
      'edit' => EditProgramAccess::route('/{record}/edit'),
    ];
  }

  public static function canCreate(): bool
  {
    return false;
  }
}

