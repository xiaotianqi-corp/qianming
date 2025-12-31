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
        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('country_id')->constrained();
            $table->enum('type', [
                'natural',
                'legal_representative',
                'company_member'
            ]);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('document_type');
            $table->string('document_number');
            $table->string('status')->default('draft');
            $table->date('birth_date')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });

        Schema::create('identity_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('identity_id')->constrained();
            $table->string('type');
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identities');
        Schema::dropIfExists('identity_documents');
    }
};
