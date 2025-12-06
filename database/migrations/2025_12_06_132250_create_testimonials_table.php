<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 255);
            $table->string('designation', 255)->nullable();
            $table->string('company', 255)->nullable();
            $table->text('testimonial');
            $table->tinyInteger('rating')->default(5)->comment('1-5 rating');
            $table->string('image', 255)->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('show_on_website')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->integer('approved_by')->default(0)->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('status', ['active', 'delete'])->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->integer('created_by')->default(0)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->default(0)->nullable();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'approval_status']);
            $table->index(['show_on_website', 'approval_status']);
            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};