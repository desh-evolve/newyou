<?php
// database/migrations/2024_01_15_000005_create_appointment_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'appointment_created',
                'appointment_confirmed',
                'appointment_cancelled',
                'appointment_reminder',
                'appointment_completed',
                'payment_received',
                'payment_failed',
                'slot_available',
                'general'
            ])->default('general');
            $table->enum('channel', ['database', 'email', 'sms', 'push'])->default('database');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'inactive', 'delete'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            
            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_notifications');
    }
};