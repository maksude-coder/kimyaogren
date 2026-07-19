<?php

declare(strict_types=1);

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
        Schema::create('learning_objectives', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys (dependency order - parent tables first)
            $table->foreignId('topic_id')
                ->constrained('topics')
                ->cascadeOnDelete();

            // 3. Business Fields (required first, then optional)
            $table->string('code', 30);
            $table->text('description');
            $table->unsignedTinyInteger('bloom_level')->default(1);
            $table->unsignedTinyInteger('difficulty')->default(3);
            $table->unsignedSmallInteger('order')->default(0);

            // 4. Status Fields
            $table->boolean('is_active')->default(true);

            // 5. Audit Fields
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 6. Timestamps
            $table->timestamps();

            // 7. Soft Deletes
            $table->softDeletes();

            // Indexes
            // `topic_id` is already indexed as the leading column of the
            // composite unique constraint below — same principle as
            // `grades`/`units`/`topics`.
            $table->unique(['topic_id', 'code']);
            $table->index(['topic_id', 'order']);
            $table->index('bloom_level');
            $table->index('difficulty');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_objectives');
    }
};
