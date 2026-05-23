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
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('rows_count');
            $table->unsignedInteger('columns_count');
            $table->string('status')->default('ready');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
