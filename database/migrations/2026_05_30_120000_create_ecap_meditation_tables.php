<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Cahiers de méditation ECAP (modèle modérateur → remise fidèle → correction).
   */
  public function up(): void
  {
    Schema::create('ecap_meditation_templates', function (Blueprint $table) {
      $table->id();
      $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
      $table->foreignId('course_module_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
      $table->string('title');
      $table->text('instructions')->nullable();
      $table->string('template_file_path')->nullable();
      $table->date('due_on')->nullable();
      $table->boolean('is_published')->default(true);
      $table->timestamps();

      $table->index(['academic_session_id', 'is_published']);
    });

    Schema::create('ecap_meditation_submissions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ecap_meditation_template_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
      $table->text('answer_text')->nullable();
      $table->string('file_path')->nullable();
      $table->string('status', 32)->default('submitted');
      $table->text('moderator_notes')->nullable();
      $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamp('submitted_at')->nullable();
      $table->timestamp('reviewed_at')->nullable();
      $table->timestamps();

      $table->unique(['ecap_meditation_template_id', 'user_id'], 'ecap_meditation_sub_unique');
    });
  }

  /**
   * Supprime les tables cahiers de méditation.
   */
  public function down(): void
  {
    Schema::dropIfExists('ecap_meditation_submissions');
    Schema::dropIfExists('ecap_meditation_templates');
  }
};
