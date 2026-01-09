<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('escalate_to')->default(false);
            $table->integer('unassigned_for')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('agent_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_observer')->default(false);
            $table->timestamps();
            
            $table->unique(['agent_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_group_members');
        Schema::dropIfExists('agent_groups');
    }
};