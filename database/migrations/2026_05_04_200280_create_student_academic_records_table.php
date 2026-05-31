<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->decimal('final_average', 8, 2)->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['academic_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academic_records');
    }
};
