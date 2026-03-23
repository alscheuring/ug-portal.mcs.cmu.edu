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
        Schema::create('layup_page_sidebar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layup_page_id')->constrained()->onDelete('cascade');
            $table->foreignId('sidebar_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['layup_page_id', 'sidebar_id']);
            $table->index(['layup_page_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layup_page_sidebar');
    }
};
