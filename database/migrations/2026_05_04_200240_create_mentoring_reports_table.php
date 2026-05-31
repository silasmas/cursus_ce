<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentoring_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('report_kind')->index();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->longText('body');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_reports');
    }
};
