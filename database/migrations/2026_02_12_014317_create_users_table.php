<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // legacy column for auth scaffolding/tests
            $table->string('username', 60)->unique();
            $table->string('email', 120)->unique();
            $table->timestamp('email_verified_at')->nullable(); // legacy column
            $table->string('password');
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('middle_name', 120)->nullable();
            $table->string('display_name', 80)->nullable();
            // Store role relationship without enforcing a DB-level foreign key,
            // to keep tests using in-memory SQLite simple and avoid FK constraint issues.
            $table->unsignedBigInteger('role_id')->default(1);
            $table->string('phone', 30)->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('timezone', 40)->default('Asia/Manila');
            $table->dateTime('last_login')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('email_verified')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_id', 'is_active', 'deleted_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
