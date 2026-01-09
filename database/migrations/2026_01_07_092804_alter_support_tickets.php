<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('category', 50)->default('general')->change();
            
            $table->foreignId('assigned_to')->nullable()->after('agent')->constrained('users')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->after('assigned_to')->constrained('agent_groups')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('due_by')->nullable();
            $table->timestamp('fr_due_by')->nullable();
            
            $table->boolean('is_escalated')->default(false);
            $table->boolean('fr_escalated')->default(false);
            
            $table->jsonb('cc_emails')->nullable();
            $table->jsonb('tags')->nullable();
            
            $table->index(['status', 'priority', 'category']);
            $table->index(['assigned_to', 'status']);
            $table->index(['group_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('ticket_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->nullOnDelete();
            $table->string('type', 20)->default('reply');
            $table->text('body');
            $table->boolean('is_private')->default(false);
            $table->boolean('incoming')->default(false);
            $table->jsonb('to_emails')->nullable();
            $table->jsonb('cc_emails')->nullable();
            $table->timestamps();
            
            $table->index(['ticket_id', 'created_at']);
        });

        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ticket_conversations')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['ticket_id', 'created_at']);
        });

        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->jsonb('common_solutions')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('category', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canned_responses');
        Schema::dropIfExists('ticket_categories');
        Schema::dropIfExists('ticket_activities');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_conversations');
        
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['group_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['sla_policy_id']);
            
            $table->dropColumn([
                'assigned_to', 'group_id', 'location_id', 'sla_policy_id',
                'first_responded_at', 'resolved_at', 'due_by', 'fr_due_by',
                'is_escalated', 'fr_escalated', 'cc_emails', 'tags'
            ]);
        });
    }
};