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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identifier')->unique();
            $table->timestamps();
        });

        Schema::create('provider_country_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->json('requirements');
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->unique(['provider_id', 'country_id']);
        });

        Schema::create('provider_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_country_config_id')->constrained('provider_country_configs');
            $table->foreignId('signature_product_id')->constrained();
            $table->enum('range', ['A','B']);
            $table->unsignedInteger('min');
            $table->unsignedInteger('max')->nullable();
            $table->decimal('provider_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
        Schema::dropIfExists('provider_country_configs');
        Schema::dropIfExists('provider_prices');
    }
};
