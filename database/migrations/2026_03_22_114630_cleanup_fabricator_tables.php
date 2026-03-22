<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old Fabricator tables if they exist
        Schema::dropIfExists('page_builder_blocks');

        // Clear old page data and remove Fabricator-specific columns
        if (Schema::hasTable('pages')) {
            // First, clear any menu items that reference pages to avoid foreign key constraint issues
            DB::table('menu_items')->whereNotNull('page_id')->update(['page_id' => null]);

            // Delete old pages since we're starting fresh with Layup
            DB::table('pages')->delete();

            // Remove Fabricator-specific columns from pages table
            Schema::table('pages', function (Blueprint $table) {
                if (Schema::hasColumn('pages', 'blocks')) {
                    $table->dropColumn('blocks');
                }
                if (Schema::hasColumn('pages', 'layout')) {
                    $table->dropColumn('layout');
                }
                if (Schema::hasColumn('pages', 'page_blocks')) {
                    $table->dropColumn('page_blocks');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration is mainly for cleanup and is not easily reversible
        // since we're removing old data. In a real rollback scenario, you would
        // need to restore from backups.
    }
};
