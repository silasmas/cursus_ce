<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * Session académique (génération ECAP ou autre programme).
 */
class AcademicSession extends Model
{
    protected $fillable = [
        'program_id',
        'name',
        'code',
        'generation_number',
        'starts_on',
        'ends_on',
        'registration_opens_at',
        'registration_closes_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'is_active' => 'boolean',
            'generation_number' => 'integer',
        ];
    }

    /**
     * Indique si les inscriptions sont actuellement ouvertes.
     */
    public function isRegistrationOpen(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->registration_opens_at !== null && $now->lt($this->registration_opens_at)) {
            return false;
        }

        if ($this->registration_closes_at !== null && $now->gt($this->registration_closes_at)) {
            return false;
        }

        return true;
    }

    /**
     * Indique si la session est une génération ECAP.
     */
    public function isEcap(): bool
    {
        return $this->program?->slug === 'ecap';
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function learningGroups(): HasMany
    {
        return $this->hasMany(LearningGroup::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function defenses(): HasMany
    {
        return $this->hasMany(Defense::class);
    }

    public function studentAcademicRecords(): HasMany
    {
        return $this->hasMany(StudentAcademicRecord::class);
    }

    public function inscriptionProfiles(): HasMany
    {
        return $this->hasMany(Profile::class, 'academic_session_id');
    }

    /**
     * Calendrier des modules de la session.
     */
    public function moduleSchedules(): HasMany
    {
        return $this->hasMany(SessionModuleSchedule::class)->orderBy('sort_order');
    }

    /**
     * Vacations présentiel proposées à l'inscription.
     */
    public function sessionVacations(): HasMany
    {
        return $this->hasMany(SessionVacation::class)->orderBy('sort_order');
    }

    /**
     * Périodes pédagogiques de la génération (cours, TFE, défenses).
     */
    public function sessionPeriods(): HasMany
    {
        return $this->hasMany(SessionPeriod::class)->orderBy('sort_order');
    }

    /**
     * Acteurs de vacation affectés à cette session.
     */
    public function ecapStaffAssignments(): HasMany
    {
        return $this->hasMany(EcapStaffAssignment::class);
    }

    /**
     * Questions des fidèles adressées aux acteurs de vacation.
     */
    public function vacationQuestions(): HasMany
    {
        return $this->hasMany(VacationQuestion::class);
    }
}
