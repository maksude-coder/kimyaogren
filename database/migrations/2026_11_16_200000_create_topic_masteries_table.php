<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A student's mastery of a topic (dd-topic-masteries.md v1.1). The
     * spaced-repetition columns exist but are not used yet (Sprint 3.3 plan § 2, rule 6).
     */
    public function up(): void
    {
        Schema::create('topic_masteries', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('topic_id')
                ->constrained('topics')
                ->cascadeOnDelete();

            // 3. Business Fields
            $table->decimal('mastery_percentage', 5, 2)->default(0);
            $table->unsignedTinyInteger('mastery_level')->default(0);
            $table->unsignedInteger('total_attempts')->default(0);
            $table->unsignedInteger('correct_attempts')->default(0);
            $table->timestamp('last_practiced_at')->nullable();

            // 4. Spaced repetition (SM-2), unused until that feature is built
            $table->timestamp('next_review_at')->nullable();
            $table->decimal('ease_factor', 4, 2)->default(2.50);
            $table->unsignedSmallInteger('interval_days')->default(0);
            $table->unsignedSmallInteger('repetitions')->default(0);
            $table->boolean('is_due')->default(false);

            // 5. Timestamps; no soft deletes: mastery is computed state.
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'topic_id'], 'uk_masteries_user_topic');
            $table->index(['user_id', 'is_due', 'next_review_at'], 'idx_masteries_user_due');
            $table->index(['user_id', 'mastery_level'], 'idx_masteries_user_level');
            $table->index('next_review_at', 'idx_masteries_next_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_masteries');
    }
};
