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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('certificate_request_id')->nullable()->constrained();
            $table->string('subject');
            $table->string('category');
            $table->string('status')->default('open');
            $table->string('priority')->default('low');
            $table->string('source')->default('portal');
            $table->string('urgency')->default('low');
            $table->string('impact')->default('low');
            $table->string('group')->nullable();
            $table->string('agent')->nullable();
            $table->text('description');
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
