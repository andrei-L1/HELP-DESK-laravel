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
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable(); // REMOVED after('name')
            $table->foreignId('priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('response_time');
            $table->unsignedInteger('resolution_time');
            $table->unsignedInteger('escalate_after')->nullable();
            $table->unsignedInteger('notify_before')->nullable();
            $table->boolean('notify_escalation')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('business_hours_only')->default(true);
            $table->unsignedInteger('calendar_id')->nullable();
            $table->timestamps(); // This creates created_at and updated_at with proper defaults
            $table->softDeletes();
            
            // Add indexes
            $table->index('priority_id');
            $table->index('department_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};