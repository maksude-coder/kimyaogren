<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Answer options of a question (dd-question-options.md v1.1).
     */
    public function up(): void
    {
        Schema::create('question_options', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys — options are part of their question.
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            // 3. Business Fields
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('order')->default(0);
            // Nullable: only the matching type uses the pair columns.
            $table->string('left_item', 255)->nullable();
            $table->string('right_item', 255)->nullable();
            // Nullable: only wrong options that need an explanation carry feedback.
            $table->text('feedback')->nullable();

            // 7. Timestamps (no audit fields, no soft deletes: part of the question)
            $table->timestamps();

            // Indexes — `question_id` leads both.
            $table->unique(['question_id', 'order']);
            $table->index(['question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
