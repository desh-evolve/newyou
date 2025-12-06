<?php
// database/migrations/2024_01_15_000004_create_appointment_notes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('coach_id');
            $table->string('title')->nullable();
            $table->text('note_content');
            $table->enum('note_type', ['general', 'progress', 'goal', 'action_item', 'follow_up', 'private'])->default('general');
            $table->enum('visibility', ['coach_only', 'admin_coach', 'all'])->default('coach_only');
            $table->boolean('is_pinned')->default(false);
            $table->json('attachments')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'inactive', 'delete'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();

            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['appointment_id', 'status']);
            $table->index(['coach_id', 'note_type']);
            $table->index(['is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_notes');
    }
};