<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->string('title');                          // Short label shown in the picker list
            $table->text('content');                          // The full response body
            $table->string('shortcut')->nullable()->unique(); // e.g. /greeting, /refund
            $table->string('category')->nullable();           // Grouping label (e.g. "Billing", "Technical")
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_shared')->default(true);      // true = visible to all staff, false = private
            $table->unsignedInteger('use_count')->default(0); // Track popularity
            $table->timestamps();

            $table->index(['is_shared', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canned_responses');
    }
};
