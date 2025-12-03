<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('custom_price', 10, 2)->nullable()->comment('Override service price');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['package_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_services');
    }
};