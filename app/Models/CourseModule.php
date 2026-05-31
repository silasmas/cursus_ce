<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseModule extends Model
{
    protected $table = 'course_modules';

    protected $fillable = [
        'course_id', 'name', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class, 'course_module_id')->orderBy('sort_order');
    }

    /**
     * Quiz obligatoire de fin de module ECAP (M5).
     */
    public function moduleExitQuiz(): HasOne
    {
        return $this->hasOne(Assessment::class, 'course_module_id')
            ->where('is_module_exit_quiz', true);
    }
}
