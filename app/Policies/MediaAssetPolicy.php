<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MediaAsset;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaAssetPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MediaAsset');
    }

    public function view(AuthUser $authUser, MediaAsset $mediaAsset): bool
    {
        return $authUser->can('View:MediaAsset');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MediaAsset');
    }

    public function update(AuthUser $authUser, MediaAsset $mediaAsset): bool
    {
        return $authUser->can('Update:MediaAsset');
    }

    public function delete(AuthUser $authUser, MediaAsset $mediaAsset): bool
    {
        return $authUser->can('Delete:MediaAsset');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MediaAsset');
    }

    public function restore(AuthUser $authUser, MediaAsset $mediaAsset): bool
    {
        return $authUser->can('Restore:MediaAsset');
    }

    public function forceDelete(AuthUser $authUser, MediaAsset $mediaAsset): bool
    {
        return $authUser->can('ForceDelete:MediaAsset');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MediaAsset');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MediaAsset');
    }

    public function replicate(AuthUser $authUser, MediaAsset $mediaAsset): bool
    {
        return $authUser->can('Replicate:MediaAsset');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MediaAsset');
    }

}