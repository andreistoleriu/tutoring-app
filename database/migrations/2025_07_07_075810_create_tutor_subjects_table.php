<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->text('experience_description')->nullable();
            $table->timestamps();

            $table->unique(['tutor_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_subjects');
    }
};
