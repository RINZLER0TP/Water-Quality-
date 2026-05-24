<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_configuration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('algorithm', 50);
            $table->string('target_column', 150);
            $table->json('parameters')->nullable();
            $table->string('status', 20)->index();
            $table->string('model_path')->nullable();
            $table->string('log_path')->nullable();
            $table->longText('execution_log')->nullable();
            $table->longText('error_message')->nullable();
            $table->json('metrics')->nullable();
            $table->json('confusion_matrix')->nullable();
            $table->unsignedSmallInteger('cross_validation_folds')->default(10);
            $table->unsignedInteger('random_seed')->default(42);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('training_time_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_jobs');
    }
};
