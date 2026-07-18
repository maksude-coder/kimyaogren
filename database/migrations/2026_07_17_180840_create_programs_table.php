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
        Schema::create('programs', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. UUID (public identifier)
            $table->uuid('uuid')->unique();

            // 3. Foreign Keys (dependency order - parent tables first)
            $table->foreignId('current_version_id')
                ->nullable()
                ->constrained('program_versions')
                ->nullOnDelete();

            // 4. Business Fields (required first, then optional)
            $table->string('code', 30)->unique();
            $table->string('name', 150)->unique();
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->char('primary_language', 5)->default('tr');
            $table->json('supported_languages')->default(json_encode(['tr']));
            $table->unsignedTinyInteger('min_grade')->default(9);
            $table->unsignedTinyInteger('max_grade')->default(12);

            // 4. Status Fields
            $table->unsignedTinyInteger('status')->default(1); // 1=Draft, 2=Published, 3=Archived
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);

            // 5. Versioning
            $table->unsignedInteger('version')->default(1);

            // 5. SEO Fields
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->string('og_image_path', 500)->nullable();

            // 6. Settings
            $table->json('settings')->default(json_encode([]));

            // 7. Audit Fields
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            // 8. Timestamps
            $table->timestamps();

            // 9. Soft Deletes
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'is_active']);
            $table->index('display_order');
            $table->index('deleted_at');
            $table->index(['min_grade', 'max_grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};