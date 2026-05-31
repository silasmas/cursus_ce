<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContentBlockProgress;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentBlockProgressPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContentBlockProgress');
    }

    public function view(AuthUser $authUser, ContentBlockProgress $contentBlockProgress): bool
    {
        return $authUser->can('View:ContentBlockProgress');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContentBlockProgress');
    }

    public function update(AuthUser $authUser, ContentBlockProgress $contentBlockProgress): bool
    {
        return $authUser->can('Update:ContentBlockProgress');
    }

    public function delete(AuthUser $authUser, ContentBlockProgress $contentBlockProgress): bool
    {
        return $authUser->can('Delete:ContentBlockProgress');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContentBlockProgress');
    }

    public function restore(AuthUser $authUser, ContentBlockProgress $contentBlockProgress): bool
    {
        return $authUser->can('Restore:ContentBlockProgress');
    }

    public function forceDelete(AuthUser $authUser, ContentBlockProgress $contentBlockProgress): bool
    {
        return $authUser->can('ForceDelete:ContentBlockProgress');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContentBlockProgress');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContentBlockProgress');
    }

    public function replicate(AuthUser $authUser, ContentBlockProgress $contentBlockProgress): bool
    {
        return $authUser->can('Replicate:ContentBlockProgress');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContentBlockProgress');
    }

}