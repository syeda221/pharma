<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_stock_closings', function (Blueprint $table) {
            $table->id();
            $table->string('closing_code', 50)->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('closing_month', 7); // 'YYYY-MM'
            $table->date('closing_date');
            
            $table->integer('total_items_count')->default(0);
            $table->integer('matched_count')->default(0);
            $table->integer('discrepant_count')->default(0);

            $table->decimal('total_system_pieces', 14, 2)->default(0);
            $table->decimal('total_physical_pieces', 14, 2)->default(0);
            $table->decimal('net_variance_pieces', 14, 2)->default(0);

            $table->enum('adjustment_mode', ['equal', 'selective', 'none'])->default('equal');
            $table->string('reason_type', 100)->nullable();
            $table->text('remarks')->nullable();

            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });

        Schema::create('monthly_stock_closing_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('closing_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('pieces_per_box')->default(1);

            $table->decimal('system_cartons', 10, 2)->default(0);
            $table->decimal('system_pieces', 10, 2)->default(0);
            $table->decimal('system_total_pieces', 14, 2)->default(0);

            $table->decimal('physical_cartons', 10, 2)->default(0);
            $table->decimal('physical_pieces', 10, 2)->default(0);
            $table->decimal('physical_total_pieces', 14, 2)->default(0);

            $table->decimal('variance_pieces', 14, 2)->default(0);
            $table->string('variance_display', 100)->nullable();
            $table->enum('status', ['matched', 'shortage', 'surplus'])->default('matched');

            $table->boolean('is_adjusted')->default(false);
            $table->decimal('old_stock', 14, 2)->default(0);
            $table->decimal('new_stock', 14, 2)->default(0);
            $table->string('item_reason', 255)->nullable();

            $table->timestamps();

            $table->foreign('closing_id')->references('id')->on('monthly_stock_closings')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_stock_closing_items');
        Schema::dropIfExists('monthly_stock_closings');
    }
};
