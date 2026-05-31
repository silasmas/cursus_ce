<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MentoringDecision;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentoringDecisionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MentoringDecision');
    }

    public function view(AuthUser $authUser, MentoringDecision $mentoringDecision): bool
    {
        return $authUser->can('View:MentoringDecision');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MentoringDecision');
    }

    public function update(AuthUser $authUser, MentoringDecision $mentoringDecision): bool
    {
        return $authUser->can('Update:MentoringDecision');
    }

    public function delete(AuthUser $authUser, MentoringDecision $mentoringDecision): bool
    {
        return $authUser->can('Delete:MentoringDecision');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MentoringDecision');
    }

    public function restore(AuthUser $authUser, MentoringDecision $mentoringDecision): bool
    {
        return $authUser->can('Restore:MentoringDecision');
    }

    public function forceDelete(AuthUser $authUser, MentoringDecision $mentoringDecision): bool
    {
        return $authUser->can('ForceDelete:MentoringDecision');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MentoringDecision');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MentoringDecision');
    }

    public function replicate(AuthUser $authUser, MentoringDecision $mentoringDecision): bool
    {
        return $authUser->can('Replicate:MentoringDecision');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MentoringDecision');
    }

}