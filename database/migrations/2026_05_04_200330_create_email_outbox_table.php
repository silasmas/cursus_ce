<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('to_email');
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_outbox');
    }
};
