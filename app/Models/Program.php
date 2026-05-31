<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Program extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'sort_order',
        'is_active',
        'type',
        'is_mandatory',
        'is_open',
        'optional_at_registration',
        'scheduled_open_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_open' => 'boolean',
            'optional_at_registration' => 'boolean',
            'scheduled_open_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function settings(): HasOne
    {
        return $this->hasOne(ProgramSetting::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function academicSessions(): HasMany
    {
        return $this->hasMany(AcademicSession::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function certificateTemplates(): HasMany
    {
        return $this->hasMany(CertificateTemplate::class);
    }

    public function mentorAssignments(): HasMany
    {
        return $this->hasMany(MentorAssignment::class);
    }
}
