<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->decimal('points_awarded', 8, 2)->nullable();
            $table->text('grader_feedback')->nullable();
            $table->timestamps();

            $table->unique(['assessment_attempt_id', 'question_id'], 'attempt_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
