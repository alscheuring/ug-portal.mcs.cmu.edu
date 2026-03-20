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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->string('title');
            $table->string('link_type')->default('page'); // 'page', 'external', 'divider'
            $table->foreignId('page_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('external_url')->nullable();
            $table->boolean('opens_in_new_tab')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->text('description')->nullable();
            $table->string('css_class')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'sort_order']);
            $table->index(['menu_id', 'is_visible']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
