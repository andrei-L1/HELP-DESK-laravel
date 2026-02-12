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
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->unique();
            $table->unsignedTinyInteger('level');
            $table->char('color_hex', 7)->nullable();
            $table->smallInteger('sort_order')->default(100);
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_priorities');
    }
};
