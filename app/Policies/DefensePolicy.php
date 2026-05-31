<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Defense;
use Illuminate\Auth\Access\HandlesAuthorization;

class DefensePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Defense');
    }

    public function view(AuthUser $authUser, Defense $defense): bool
    {
        return $authUser->can('View:Defense');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Defense');
    }

    public function update(AuthUser $authUser, Defense $defense): bool
    {
        return $authUser->can('Update:Defense');
    }

    public function delete(AuthUser $authUser, Defense $defense): bool
    {
        return $authUser->can('Delete:Defense');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Defense');
    }

    public function restore(AuthUser $authUser, Defense $defense): bool
    {
        return $authUser->can('Restore:Defense');
    }

    public function forceDelete(AuthUser $authUser, Defense $defense): bool
    {
        return $authUser->can('ForceDelete:Defense');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Defense');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Defense');
    }

    public function replicate(AuthUser $authUser, Defense $defense): bool
    {
        return $authUser->can('Replicate:Defense');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Defense');
    }

}