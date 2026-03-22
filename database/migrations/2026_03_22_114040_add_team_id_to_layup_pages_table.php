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
        Schema::table('layup_pages', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();

            // Add indexes for performance
            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layup_pages', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['author_id']);
            $table->dropColumn(['team_id', 'author_id', 'published_at']);
        });
    }
};
