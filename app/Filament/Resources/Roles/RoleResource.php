<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as ShieldRoleResource;

/**
 * Rôles Shield avec libellés français et info-bulle menu admin.
 */
class RoleResource extends ShieldRoleResource
{
  use HasFrenchFilamentLabels;
}
