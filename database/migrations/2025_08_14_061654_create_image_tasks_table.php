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
        Schema::create('image_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_request_id')->constrained()->cascadeOnDelete();
            $table->text('image_url');
            $table->enum('status', ['pending','processed','failed'])->default('pending')->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_tasks');
    }
};
