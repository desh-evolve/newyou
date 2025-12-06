<?php
// database/migrations/2024_01_15_000002_create_time_slots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes')->default(60);
            $table->enum('slot_status', ['available', 'locked', 'booked', 'blocked'])->default('available');
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_recurring_generated')->default(false);
            $table->enum('status', ['active', 'inactive', 'delete'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();

            $table->foreign('coach_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['slot_date', 'slot_status']);
            $table->index(['coach_id', 'slot_date']);
            $table->unique(['coach_id', 'slot_date', 'start_time'], 'unique_coach_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};