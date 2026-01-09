<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);
            
            $table->integer('first_response_time')->nullable();
            $table->integer('resolution_time')->nullable();
            
            $table->jsonb('conditions')->nullable();
            
            $table->foreignId('business_hours_id')->nullable()->constrained('business_hours')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};