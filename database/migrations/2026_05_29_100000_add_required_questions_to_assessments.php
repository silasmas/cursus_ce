<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Nombre de questions attendu pour un quiz (notamment fin de module ECAP).
   */
  public function up(): void
  {
    Schema::table('assessments', function (Blueprint $table) {
      $table->unsignedSmallInteger('required_questions')
        ->nullable()
        ->after('passing_score');
    });
  }

  /**
   * Supprime la colonne required_questions.
   */
  public function down(): void
  {
    Schema::table('assessments', function (Blueprint $table) {
      $table->dropColumn('required_questions');
    });
  }
};
