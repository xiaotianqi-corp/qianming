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
        Schema::create('signature_products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('container', ['archivo','nube','combo','token','app']);
            $table->unsignedTinyInteger('validity_years');
            $table->enum('subscriber_type', [
                'natural',
                'legal_representative',
                'company_member'
            ]);
            $table->decimal('pvp', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_products');
    }
};
