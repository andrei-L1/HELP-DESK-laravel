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
 Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_number', 25)->unique();
    $table->string('subject', 200);
    $table->mediumText('description');

    $table->foreignId('status_id')->constrained('ticket_statuses')->restrictOnDelete();
    $table->foreignId('priority_id')->constrained('ticket_priorities')->restrictOnDelete();
    $table->foreignId('type_id')->nullable()->constrained('ticket_types')->nullOnDelete();
    $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

    $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('resolver_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
    $table->dateTime('first_response_at')->nullable();
    $table->dateTime('resolved_at')->nullable();
    $table->dateTime('closed_at')->nullable();
    $table->dateTime('due_at')->nullable();
    $table->unsignedInteger('version')->default(1);
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
