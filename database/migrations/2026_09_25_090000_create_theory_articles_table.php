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
        Schema::create('theory_articles', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            $table->foreignId('topic_id')
                ->constrained('topics')
                ->cascadeOnDelete();

            // 3. Business Fields
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->text('excerpt')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->unsignedTinyInteger('difficulty')->default(3);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedSmallInteger('read_time_minutes')->nullable();
            $table->string('featured_image_path', 500)->nullable();

            // Legacy WordPress identity (ADR-004)
            $table->string('legacy_slug', 220)->nullable()->unique();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();

            // 4. Status Fields
            $table->unsignedTinyInteger('status')->default(1); // 1=Draft, 2=Review, 3=Published, 4=Archived
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();

            // 5. SEO Fields
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('canonical_url', 500)->nullable();

            // 6. Audit Fields
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();

            // 7. Timestamps
            $table->timestamps();

            // 8. Soft Deletes
            $table->softDeletes();

            // Indexes — `topic_id` leads the composite unique below.
            $table->unique(['topic_id', 'slug']);
            $table->index(['topic_id', 'status', 'is_active']);
            $table->index(['topic_id', 'order']);
            $table->index('published_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theory_articles');
    }
};
