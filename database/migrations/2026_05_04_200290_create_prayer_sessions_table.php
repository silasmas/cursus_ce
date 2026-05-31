<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('learning_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('meeting_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_sessions');
    }
};
