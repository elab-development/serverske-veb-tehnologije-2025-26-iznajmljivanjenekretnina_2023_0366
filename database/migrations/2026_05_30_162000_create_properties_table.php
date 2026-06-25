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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2);
            $table->string('city');
            $table->string('address');
            $table->decimal('area', 8, 2);
            $table->decimal('rooms', 3, 1)->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('floor')->nullable();
            $table->unsignedTinyInteger('total_floors')->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->string('listing_type');
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
