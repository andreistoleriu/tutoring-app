<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->string('click_url');
            $table->enum('type', ['banner', 'card', 'popup']);
            $table->enum('placement', ['header', 'sidebar', 'footer', 'feed', 'modal']);
            $table->json('targeting')->nullable(); // Store targeting criteria
            $table->integer('priority')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('cost_per_click', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'placement']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
