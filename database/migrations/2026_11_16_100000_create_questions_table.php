<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Question bank (dd-questions.md v1.1).
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            // RESTRICT: a topic that has questions cannot be force-deleted (DEBT-005).
            $table->foreignId('topic_id')
                ->constrained('topics')
                ->restrictOnDelete();

            // Nullable: the objective tag is optional.
            $table->foreignId('learning_objective_id')
                ->nullable()
                ->constrained('learning_objectives')
                ->nullOnDelete();

            // 3. Business Fields
            $table->string('type', 30); // QuestionType
            $table->text('content');
            // Nullable: a draft may not have its solution yet; required to approve.
            $table->text('explanation')->nullable();
            $table->unsignedTinyInteger('difficulty')->default(3); // QuestionDifficulty
            $table->unsignedTinyInteger('bloom_level')->default(2); // BloomLevel

            // 4. Status Fields
            $table->unsignedTinyInteger('status')->default(1); // 1=Draft, 2=Review, 3=Approved, 4=Deprecated
            $table->boolean('is_active')->default(true);

            // Source metadata — nullable: only past YKS questions have them.
            $table->unsignedSmallInteger('yks_year')->nullable();
            $table->unsignedTinyInteger('yks_session')->nullable(); // 1=TYT, 2=AYT
            $table->string('source', 100)->nullable();
            $table->json('tags')->nullable();

            // 6. Audit Fields
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 7. Timestamps
            $table->timestamps();

            // 8. Soft Deletes
            $table->softDeletes();

            // Indexes — `topic_id` leads the composite index below.
            $table->index(['topic_id', 'is_active']);
            $table->index('status');
            $table->index('difficulty');
            $table->index('type');
            $table->index(['yks_year', 'yks_session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
