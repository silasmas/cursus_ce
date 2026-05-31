<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documents légaux à accepter avant inscription (règlement intérieur ECAP, etc.).
 */
return new class extends Migration
{
  /**
   * Crée la table legal_documents et lie l'acceptation au profil.
   */
  public function up(): void
  {
    Schema::create('legal_documents', function (Blueprint $table) {
      $table->id();
      $table->string('slug')->unique();
      $table->string('title');
      $table->text('summary')->nullable();
      $table->string('file_path');
      $table->string('version', 32)->default('1.0');
      $table->boolean('is_active')->default(false);
      $table->boolean('required_at_registration')->default(true);
      $table->timestamp('published_at')->nullable();
      $table->timestamps();
    });

    Schema::table('profiles', function (Blueprint $table) {
      $table->foreignId('accepted_legal_document_id')
        ->nullable()
        ->after('locale')
        ->constrained('legal_documents')
        ->nullOnDelete();
      $table->timestamp('legal_document_accepted_at')->nullable()->after('accepted_legal_document_id');
    });
  }

  /**
   * Supprime les colonnes et la table.
   */
  public function down(): void
  {
    Schema::table('profiles', function (Blueprint $table) {
      $table->dropConstrainedForeignId('accepted_legal_document_id');
      $table->dropColumn('legal_document_accepted_at');
    });

    Schema::dropIfExists('legal_documents');
  }
};
