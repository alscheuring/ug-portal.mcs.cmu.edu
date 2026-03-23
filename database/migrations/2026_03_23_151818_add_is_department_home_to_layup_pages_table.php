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
            $table->boolean('is_department_home')->default(false)->after('published_at');
            $table->index(['team_id', 'is_department_home']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layup_pages', function (Blueprint $table) {
            $table->dropIndex(['team_id', 'is_department_home']);
            $table->dropColumn('is_department_home');
        });
    }
};
