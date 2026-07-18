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
        Schema::create('program_versions', function (Blueprint $table) {
            // 1. Primary Key
            $table->id();

            // 2. Foreign Keys
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();

            // 3. Business Fields
            $table->unsignedInteger('version_number');
            $table->json('snapshot');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();

            // 4. Timestamps (only created_at, versions are immutable)
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->unique(['program_id', 'version_number'], 'uk_program_versions_number');
            $table->index('program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_versions');
    }
};