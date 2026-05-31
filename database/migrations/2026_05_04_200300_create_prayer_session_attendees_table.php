<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_session_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prayer_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('attended')->default(false);
            $table->timestamps();

            $table->unique(['prayer_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_session_attendees');
    }
};
