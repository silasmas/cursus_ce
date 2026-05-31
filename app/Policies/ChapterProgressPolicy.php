<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ChapterProgress;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChapterProgressPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ChapterProgress');
    }

    public function view(AuthUser $authUser, ChapterProgress $chapterProgress): bool
    {
        return $authUser->can('View:ChapterProgress');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ChapterProgress');
    }

    public function update(AuthUser $authUser, ChapterProgress $chapterProgress): bool
    {
        return $authUser->can('Update:ChapterProgress');
    }

    public function delete(AuthUser $authUser, ChapterProgress $chapterProgress): bool
    {
        return $authUser->can('Delete:ChapterProgress');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ChapterProgress');
    }

    public function restore(AuthUser $authUser, ChapterProgress $chapterProgress): bool
    {
        return $authUser->can('Restore:ChapterProgress');
    }

    public function forceDelete(AuthUser $authUser, ChapterProgress $chapterProgress): bool
    {
        return $authUser->can('ForceDelete:ChapterProgress');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ChapterProgress');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ChapterProgress');
    }

    public function replicate(AuthUser $authUser, ChapterProgress $chapterProgress): bool
    {
        return $authUser->can('Replicate:ChapterProgress');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ChapterProgress');
    }

}