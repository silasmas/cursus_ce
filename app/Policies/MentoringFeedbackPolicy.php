<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MentoringFeedback;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentoringFeedbackPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MentoringFeedback');
    }

    public function view(AuthUser $authUser, MentoringFeedback $mentoringFeedback): bool
    {
        return $authUser->can('View:MentoringFeedback');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MentoringFeedback');
    }

    public function update(AuthUser $authUser, MentoringFeedback $mentoringFeedback): bool
    {
        return $authUser->can('Update:MentoringFeedback');
    }

    public function delete(AuthUser $authUser, MentoringFeedback $mentoringFeedback): bool
    {
        return $authUser->can('Delete:MentoringFeedback');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MentoringFeedback');
    }

    public function restore(AuthUser $authUser, MentoringFeedback $mentoringFeedback): bool
    {
        return $authUser->can('Restore:MentoringFeedback');
    }

    public function forceDelete(AuthUser $authUser, MentoringFeedback $mentoringFeedback): bool
    {
        return $authUser->can('ForceDelete:MentoringFeedback');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MentoringFeedback');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MentoringFeedback');
    }

    public function replicate(AuthUser $authUser, MentoringFeedback $mentoringFeedback): bool
    {
        return $authUser->can('Replicate:MentoringFeedback');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MentoringFeedback');
    }

}