<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProgramSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProgramSetting');
    }

    public function view(AuthUser $authUser, ProgramSetting $programSetting): bool
    {
        return $authUser->can('View:ProgramSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProgramSetting');
    }

    public function update(AuthUser $authUser, ProgramSetting $programSetting): bool
    {
        return $authUser->can('Update:ProgramSetting');
    }

    public function delete(AuthUser $authUser, ProgramSetting $programSetting): bool
    {
        return $authUser->can('Delete:ProgramSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProgramSetting');
    }

    public function restore(AuthUser $authUser, ProgramSetting $programSetting): bool
    {
        return $authUser->can('Restore:ProgramSetting');
    }

    public function forceDelete(AuthUser $authUser, ProgramSetting $programSetting): bool
    {
        return $authUser->can('ForceDelete:ProgramSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProgramSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProgramSetting');
    }

    public function replicate(AuthUser $authUser, ProgramSetting $programSetting): bool
    {
        return $authUser->can('Replicate:ProgramSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProgramSetting');
    }

}