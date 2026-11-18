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
        Schema::create('period_closings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50)->default('sales'); // 'sales', etc.
            $table->integer('year');                         // e.g. 2026
            $table->integer('month');                        // 1 to 12
            $table->string('closing_month', 7);              // '2026-11'
            $table->date('start_date');                      // '2026-11-01'
            $table->date('end_date');                        // '2026-11-30'
            $table->enum('status', ['open', 'closed'])->default('closed');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->unsignedBigInteger('reopened_by')->nullable();
            $table->datetime('reopened_at')->nullable();
            $table->text('reopen_reason')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['module', 'year', 'month'], 'uq_module_year_month');
            $table->index(['module', 'status', 'start_date', 'end_date'], 'idx_period_check');

            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reopened_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_closings');
    }
};
