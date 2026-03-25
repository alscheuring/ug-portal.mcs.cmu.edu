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
        Schema::create('event_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('api_url');
            $table->integer('max_events')->default(50);
            $table->boolean('is_active')->default(true);
            $table->json('import_settings');
            $table->datetime('last_imported_at')->nullable();

            // Foreign keys
            $table->foreignId('team_id')->constrained()->onDelete('cascade');

            $table->timestamps();

            // Indexes
            $table->index(['team_id', 'is_active']);
            $table->index('last_imported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_feeds');
    }
};
