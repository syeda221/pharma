<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('medical_samples')) {
            Schema::create('medical_samples', function (Blueprint $table) {
                $table->id();
                $table->string('sample_no')->unique();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('warehouse_id')->default(1)->index();
                $table->date('sample_date');
                $table->string('doctor_name')->nullable();
                $table->string('mr_name')->nullable();
                $table->decimal('total_pieces', 12, 4)->default(0);
                $table->decimal('wos_amount', 12, 2)->default(0); // Worth of Samples at cost
                $table->text('remarks')->nullable();
                $table->string('status', 30)->default('posted');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('medical_sample_items')) {
            Schema::create('medical_sample_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('medical_sample_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->text('variant_color')->nullable();
                $table->decimal('qty', 12, 4)->default(0); // Pieces given free
                $table->decimal('purchase_price', 12, 2)->default(0); // Cost to company
                $table->decimal('retail_price', 12, 2)->default(0);
                $table->decimal('wos_line_total', 12, 2)->default(0); // qty * purchase_price
                $table->timestamps();

                $table->foreign('medical_sample_id')->references('id')->on('medical_samples')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_sample_items');
        Schema::dropIfExists('medical_samples');
    }
};
