<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->index();
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->decimal('passing_score', 5, 2)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
