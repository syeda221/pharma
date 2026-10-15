<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'discount_tier')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('discount_tier', 191)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'discount_tier')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('discount_tier', 30)->nullable()->change();
            });
        }
    }
};
