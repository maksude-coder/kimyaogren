<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per answer a student gives (dd-question-attempts.md v1.1).
     * `exam_attempt_id` is added with the exam tables.
     */
    public function up(): void
    {
        Schema::create('question_attempts', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // RESTRICT: a question that has been answered cannot be force-deleted.
            $table->foreignId('question_id')
                ->constrained('questions')
                ->restrictOnDelete();

            // Nullable: an attempt may stand alone or outlive its session.
            $table->foreignId('practice_session_id')
                ->nullable()
                ->constrained('practice_sessions')
                ->nullOnDelete();

            // Nullable: text answers have no option; a removed option keeps the attempt.
            $table->foreignId('selected_option_id')
                ->nullable()
                ->constrained('question_options')
                ->nullOnDelete();

            // 3. Business Fields
            // Nullable: only short answer / essay types use it.
            $table->text('answer_text')->nullable();
            $table->boolean('is_correct');
            // Nullable: SM-2 quality is set once spaced repetition exists.
            $table->unsignedTinyInteger('quality')->nullable();
            $table->unsignedSmallInteger('time_spent_seconds')->nullable();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamp('answered_at');

            // Immutable record: no timestamps, audit fields or soft deletes.

            // Indexes
            $table->unique(['practice_session_id', 'question_id', 'attempt_number'], 'uk_attempts_session_question');
            $table->index(['user_id', 'question_id']);
            $table->index(['user_id', 'answered_at']);
            $table->index(['practice_session_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_attempts');
    }
};
