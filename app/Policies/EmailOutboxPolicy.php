<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmailOutbox;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailOutboxPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmailOutbox');
    }

    public function view(AuthUser $authUser, EmailOutbox $emailOutbox): bool
    {
        return $authUser->can('View:EmailOutbox');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmailOutbox');
    }

    public function update(AuthUser $authUser, EmailOutbox $emailOutbox): bool
    {
        return $authUser->can('Update:EmailOutbox');
    }

    public function delete(AuthUser $authUser, EmailOutbox $emailOutbox): bool
    {
        return $authUser->can('Delete:EmailOutbox');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EmailOutbox');
    }

    public function restore(AuthUser $authUser, EmailOutbox $emailOutbox): bool
    {
        return $authUser->can('Restore:EmailOutbox');
    }

    public function forceDelete(AuthUser $authUser, EmailOutbox $emailOutbox): bool
    {
        return $authUser->can('ForceDelete:EmailOutbox');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmailOutbox');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmailOutbox');
    }

    public function replicate(AuthUser $authUser, EmailOutbox $emailOutbox): bool
    {
        return $authUser->can('Replicate:EmailOutbox');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmailOutbox');
    }

}