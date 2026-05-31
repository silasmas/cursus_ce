<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Remplace le statut texte par des indicateurs booléens (switches admin).
   */
  public function up(): void
  {
    Schema::table('program_accesses', function (Blueprint $table) {
      $table->boolean('is_pending')->default(false)->after('program_id');
      $table->boolean('is_open')->default(false)->after('is_pending');
      $table->boolean('is_completed')->default(false)->after('is_open');
      $table->boolean('is_waived')->default(false)->after('is_completed');
      $table->boolean('needs_admin_validation')->default(false)->after('is_waived');
    });

    DB::table('program_accesses')->orderBy('id')->each(function (object $row): void {
      $flags = match ($row->status) {
        'open', 'in_progress' => ['is_open' => true],
        'completed' => ['is_completed' => true],
        'waived' => ['is_waived' => true],
        'declared_completed' => ['needs_admin_validation' => true],
        default => ['is_pending' => true],
      };

      DB::table('program_accesses')->where('id', $row->id)->update($flags);
    });

    Schema::table('program_accesses', function (Blueprint $table) {
      $table->dropIndex(['user_id', 'status']);
      $table->dropColumn('status');
      $table->index(['user_id', 'is_open']);
      $table->index(['user_id', 'needs_admin_validation']);
    });
  }

  /**
   * Restaure la colonne status texte.
   */
  public function down(): void
  {
    Schema::table('program_accesses', function (Blueprint $table) {
      $table->string('status', 30)->nullable()->after('program_id');
    });

    DB::table('program_accesses')->orderBy('id')->each(function (object $row): void {
      $status = match (true) {
        (bool) $row->is_waived => 'waived',
        (bool) $row->is_completed => 'completed',
        (bool) $row->needs_admin_validation => 'declared_completed',
        (bool) $row->is_open => 'open',
        default => 'pending',
      };

      DB::table('program_accesses')->where('id', $row->id)->update(['status' => $status]);
    });

    Schema::table('program_accesses', function (Blueprint $table) {
      $table->dropIndex(['user_id', 'is_open']);
      $table->dropIndex(['user_id', 'needs_admin_validation']);
      $table->dropColumn([
        'is_pending',
        'is_open',
        'is_completed',
        'is_waived',
        'needs_admin_validation',
      ]);
      $table->string('status', 30)->nullable(false)->change();
      $table->index(['user_id', 'status']);
    });
  }
};
