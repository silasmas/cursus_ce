<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Autorise un même rôle (enseignant / superviseur) sur plusieurs modules.
   */
  public function up(): void
  {
    Schema::table('ecap_staff_assignments', function (Blueprint $table) {
      $table->dropUnique('ecap_staff_assignments_unique');
    });

    Schema::table('ecap_staff_assignments', function (Blueprint $table) {
      $table->unique(
        ['academic_session_id', 'user_id', 'role', 'session_vacation_id', 'course_module_id'],
        'ecap_staff_assignments_unique_v2',
      );
    });
  }

  /**
   * Restaure l'index unique précédent.
   */
  public function down(): void
  {
    Schema::table('ecap_staff_assignments', function (Blueprint $table) {
      $table->dropUnique('ecap_staff_assignments_unique_v2');
    });

    Schema::table('ecap_staff_assignments', function (Blueprint $table) {
      $table->unique(
        ['academic_session_id', 'user_id', 'role', 'session_vacation_id'],
        'ecap_staff_assignments_unique',
      );
    });
  }
};
