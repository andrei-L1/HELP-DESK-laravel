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
        Schema::table('tickets', function (Blueprint $table) {
            $table->dateTime('sla_warning_sent_at')->nullable()->after('sla_policy_id');
            $table->dateTime('sla_breach_sent_at')->nullable()->after('sla_warning_sent_at');
            $table->boolean('is_sla_breached')->default(false)->after('sla_breach_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_warning_sent_at', 'sla_breach_sent_at', 'is_sla_breached']);
        });
    }
};
