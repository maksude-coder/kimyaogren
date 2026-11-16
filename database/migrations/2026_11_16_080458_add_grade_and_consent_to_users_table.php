<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Student grade, graduate flag and consent record (dd-users.md v1.1).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable: teachers, admins and graduates have no grade.
            $table->foreignId('grade_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_graduate')->default(false)->after('status');
            // Nullable: accounts created by an admin never went through the registration form.
            $table->timestamp('terms_accepted_at')->nullable()->after('is_graduate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grade_id');
            $table->dropColumn(['is_graduate', 'terms_accepted_at']);
        });
    }
};
