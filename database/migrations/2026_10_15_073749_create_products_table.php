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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name', 200)->nullable();
            $table->string('slug', 220)->unique()->nullable();

            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();

            $table->string('main_image')->nullable();

            $table->decimal('original_price', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);

            $table->unsignedInteger('qty')->default(0);

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
