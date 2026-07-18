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
        Schema::create('grades', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys (dependency order - parent tables first)
            $table->foreignId('program_id')
                ->constrained('programs')
                ->cascadeOnDelete();

            // 3. Business Fields (required first, then optional)
            $table->unsignedTinyInteger('number');
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);

            // 4. Status Fields
            $table->boolean('is_active')->default(true);

            // 5. SEO Fields
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();

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

            // Indexes
            // `program_id` is already indexed as the leading column of the
            // composite unique constraint below — no separate index needed
            // (same principle as programs.idx_programs_status_active).
            $table->unique(['program_id', 'number']);
            $table->index('order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
