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
        Schema::table('teams', function (Blueprint $table) {
            $table->string('quick_links_title')->default('Quick Links');
            $table->longText('quick_links_content')->nullable();
            $table->string('help_box_title')->default('Need Help?');
            $table->longText('help_box_content')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'quick_links_title',
                'quick_links_content',
                'help_box_title',
                'help_box_content',
            ]);
        });
    }
};
