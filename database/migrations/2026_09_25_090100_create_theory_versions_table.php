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
        Schema::create('theory_versions', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            $table->foreignId('theory_article_id')
                ->constrained('theory_articles')
                ->cascadeOnDelete();

            // 3. Business Fields
            $table->unsignedInteger('version_number');
            $table->string('title', 200);
            $table->longText('content');
            $table->string('format', 20)->default('html');
            $table->string('change_summary', 500)->nullable();
            $table->unsignedInteger('word_count')->default(0);

            // 4. Status Fields
            $table->boolean('is_published_version')->default(false);

            // 6. Audit Fields
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();

            // 7. Timestamps — versions are immutable, so no updated_at.
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->unique(['theory_article_id', 'version_number']);
            $table->index(['theory_article_id', 'is_published_version']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theory_versions');
    }
};
