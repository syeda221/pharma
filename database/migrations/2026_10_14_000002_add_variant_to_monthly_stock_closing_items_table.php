<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_stock_closing_items', function (Blueprint $table) {
            $table->string('variant_key', 255)->nullable()->after('product_id');
            $table->string('variant_name', 255)->nullable()->after('variant_key');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_stock_closing_items', function (Blueprint $table) {
            $table->dropColumn(['variant_key', 'variant_name']);
        });
    }
};
