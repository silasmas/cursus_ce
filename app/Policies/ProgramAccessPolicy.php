<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProgramAccess;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramAccessPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProgramAccess');
    }

    public function view(AuthUser $authUser, ProgramAccess $programAccess): bool
    {
        return $authUser->can('View:ProgramAccess');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProgramAccess');
    }

    public function update(AuthUser $authUser, ProgramAccess $programAccess): bool
    {
        return $authUser->can('Update:ProgramAccess');
    }

    public function delete(AuthUser $authUser, ProgramAccess $programAccess): bool
    {
        return $authUser->can('Delete:ProgramAccess');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProgramAccess');
    }

    public function restore(AuthUser $authUser, ProgramAccess $programAccess): bool
    {
        return $authUser->can('Restore:ProgramAccess');
    }

    public function forceDelete(AuthUser $authUser, ProgramAccess $programAccess): bool
    {
        return $authUser->can('ForceDelete:ProgramAccess');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProgramAccess');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProgramAccess');
    }

    public function replicate(AuthUser $authUser, ProgramAccess $programAccess): bool
    {
        return $authUser->can('Replicate:ProgramAccess');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProgramAccess');
    }

}