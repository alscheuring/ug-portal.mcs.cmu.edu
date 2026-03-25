<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('summary')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->string('location')->nullable();
            $table->string('info_url')->nullable();
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_published')->default(false);
            $table->enum('source_type', ['manual', 'imported'])->default('manual');
            $table->string('external_id')->nullable();

            // Foreign keys
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('event_feed_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index(['team_id', 'is_published']);
            $table->index(['start_datetime', 'end_datetime']);
            $table->index(['source_type']);
            $table->unique(['external_id', 'event_feed_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
