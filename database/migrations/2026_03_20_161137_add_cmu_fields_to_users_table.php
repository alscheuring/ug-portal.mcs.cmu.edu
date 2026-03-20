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
        Schema::table('users', function (Blueprint $table) {
            $table->string('andrew_id')->unique()->nullable()->after('email');
            $table->string('department')->nullable()->after('andrew_id');
            $table->string('year_in_program')->nullable()->after('department');
            $table->string('major')->nullable()->after('year_in_program');
            $table->timestamp('profile_completed_at')->nullable()->after('major');

            // Make password nullable since we're using OAuth
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'andrew_id',
                'department',
                'year_in_program',
                'major',
                'profile_completed_at',
            ]);

            // Restore password as required
            $table->string('password')->nullable(false)->change();
        });
    }
};
