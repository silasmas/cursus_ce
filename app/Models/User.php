<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && (
                $this->hasRole(config('filament-shield.super_admin.name', 'super_admin'), 'admin')
                || $this->hasRole(config('filament-shield.panel_user.name', 'panel_user'), 'admin')
            );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Accès aux cursus (statuts par programme).
     */
    public function programAccesses(): HasMany
    {
        return $this->hasMany(ProgramAccess::class);
    }

    public function mentorProfile(): HasOne
    {
        return $this->hasOne(MentorProfile::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function learningGroupMemberships(): HasMany
    {
        return $this->hasMany(LearningGroupMember::class);
    }

    /**
     * Assignations où l'utilisateur est mentor.
     */
    public function mentorAssignments(): HasMany
    {
        return $this->hasMany(MentorAssignment::class, 'mentor_id');
    }

    /**
     * Assignations où l'utilisateur est mentoré.
     */
    public function menteeAssignments(): HasMany
    {
        return $this->hasMany(MentorAssignment::class, 'mentee_id');
    }

    /**
     * Tentatives de tests de l'utilisateur.
     */
    public function assessmentAttempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    /**
     * Remises de TP de l'utilisateur.
     */
    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Indique si l'utilisateur est mentor actif.
     */
    public function isMentor(): bool
    {
        return $this->mentorProfile !== null
            && $this->mentorAssignments()->where('status', 'active')->exists();
    }
}
