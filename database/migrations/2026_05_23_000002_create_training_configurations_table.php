<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('target_column');
            $table->string('algorithm');
            $table->json('parameters')->nullable();
            $table->json('analysis')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dataset_id', 'created_by']);
            $table->index(['algorithm', 'target_column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_configurations');
    }
};