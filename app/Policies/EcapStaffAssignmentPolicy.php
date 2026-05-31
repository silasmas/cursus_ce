<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EcapStaffAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class EcapStaffAssignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EcapStaffAssignment');
    }

    public function view(AuthUser $authUser, EcapStaffAssignment $ecapStaffAssignment): bool
    {
        return $authUser->can('View:EcapStaffAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EcapStaffAssignment');
    }

    public function update(AuthUser $authUser, EcapStaffAssignment $ecapStaffAssignment): bool
    {
        return $authUser->can('Update:EcapStaffAssignment');
    }

    public function delete(AuthUser $authUser, EcapStaffAssignment $ecapStaffAssignment): bool
    {
        return $authUser->can('Delete:EcapStaffAssignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EcapStaffAssignment');
    }

    public function restore(AuthUser $authUser, EcapStaffAssignment $ecapStaffAssignment): bool
    {
        return $authUser->can('Restore:EcapStaffAssignment');
    }

    public function forceDelete(AuthUser $authUser, EcapStaffAssignment $ecapStaffAssignment): bool
    {
        return $authUser->can('ForceDelete:EcapStaffAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EcapStaffAssignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EcapStaffAssignment');
    }

    public function replicate(AuthUser $authUser, EcapStaffAssignment $ecapStaffAssignment): bool
    {
        return $authUser->can('Replicate:EcapStaffAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EcapStaffAssignment');
    }

}