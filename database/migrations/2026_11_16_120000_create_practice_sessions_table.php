<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Student practice sessions (dd-practice-sessions.md v1.1).
     */
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Nullable: mixed sessions have no topic; a deleted topic keeps the history.
            $table->foreignId('topic_id')
                ->nullable()
                ->constrained('topics')
                ->nullOnDelete();

            // 3. Business Fields
            $table->string('type', 30); // PracticeSessionType
            $table->timestamp('started_at');
            // Nullable: the session is open until it is completed.
            $table->timestamp('completed_at')->nullable();
            // Nullable: only timed sessions have a limit.
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->unsignedSmallInteger('skipped_count')->default(0);
            // Nullable: known only when the session is completed.
            $table->decimal('score', 5, 2)->nullable();
            $table->json('session_data')->nullable();

            // 7. Timestamps (no soft deletes: sessions are permanent history)
            $table->timestamps();

            // Indexes — `user_id` leads the composites.
            $table->index(['user_id', 'topic_id']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'completed_at']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
