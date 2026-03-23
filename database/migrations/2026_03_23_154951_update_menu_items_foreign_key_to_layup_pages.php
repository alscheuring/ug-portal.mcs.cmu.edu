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
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the existing foreign key constraint that references pages
            $table->dropForeign(['page_id']);

            // Add new foreign key constraint that references layup_pages
            $table->foreign('page_id')->references('id')->on('layup_pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drop the layup_pages foreign key constraint
            $table->dropForeign(['page_id']);

            // Restore the original foreign key constraint that references pages
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }
};
