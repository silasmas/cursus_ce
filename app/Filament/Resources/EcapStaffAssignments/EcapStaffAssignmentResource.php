<?php

namespace App\Filament\Resources\EcapStaffAssignments;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\EcapStaffAssignments\Pages\CreateEcapStaffAssignment;
use App\Filament\Resources\EcapStaffAssignments\Pages\EditEcapStaffAssignment;
use App\Filament\Resources\EcapStaffAssignments\Pages\ListEcapStaffAssignments;
use App\Filament\Resources\EcapStaffAssignments\Schemas\EcapStaffAssignmentForm;
use App\Filament\Resources\EcapStaffAssignments\Tables\EcapStaffAssignmentsTable;
use App\Models\EcapStaffAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Administration des acteurs de vacation ECAP (M6).
 */
class EcapStaffAssignmentResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = EcapStaffAssignment::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

  protected static string|\UnitEnum|null $navigationGroup = 'ECAP';

  protected static ?int $navigationSort = 2;

  protected static ?string $slug = 'acteurs-vacation-ecap';

  /**
   * @return array<string, \Filament\Resources\Pages\PageRegistration>
   */
  public static function getPages(): array
  {
    return [
      'index' => ListEcapStaffAssignments::route('/'),
      'create' => CreateEcapStaffAssignment::route('/create'),
      'edit' => EditEcapStaffAssignment::route('/{record}/edit'),
    ];
  }

  public static function form(Schema $schema): Schema
  {
    return EcapStaffAssignmentForm::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return EcapStaffAssignmentsTable::configure($table);
  }
}
