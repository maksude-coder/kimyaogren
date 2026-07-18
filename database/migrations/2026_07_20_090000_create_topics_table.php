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
        Schema::create('topics', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys (dependency order - parent tables first)
            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnDelete();

            // 3. Business Fields (required first, then optional)
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->unsignedTinyInteger('difficulty')->default(3);

            // 4. Status Fields
            $table->unsignedTinyInteger('status')->default(1); // 1=Draft, 2=Published, 3=Archived
            $table->boolean('is_active')->default(true);

            // 5. SEO Fields
            $table->string('meta_title', 60)->nullable();
            $table->text('meta_description')->nullable();

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
            // `unit_id` is already indexed as the leading column of the
            // composite unique constraint below — same principle as `units`.
            $table->unique(['unit_id', 'slug']);
            $table->index(['unit_id', 'order']);
            $table->index(['status', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
