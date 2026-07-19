<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `current_version_id` is split into its own migration, after both
     * `programs` and `program_versions` exist, so the FK can be declared
     * correctly regardless of database driver (MySQL requires the
     * referenced table to exist when the FK constraint is added; SQLite
     * does not enforce this, but the same migration works for both).
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('current_version_id')
                ->nullable()
                ->constrained('program_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_version_id');
        });
    }
};
