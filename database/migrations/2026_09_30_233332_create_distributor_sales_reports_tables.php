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
        Schema::create('distributor_sales_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distributor_id');
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->date('report_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('distributor_sales_report_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('given_quantity')->default(0);
            $table->integer('sold_quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_sales_report_items');
        Schema::dropIfExists('distributor_sales_reports');
    }
};
