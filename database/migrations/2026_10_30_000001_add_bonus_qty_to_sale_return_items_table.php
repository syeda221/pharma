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
        if (!Schema::hasColumn('sale_return_items', 'bonus_qty')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->decimal('bonus_qty', 12, 4)->default(0)->after('qty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sale_return_items', 'bonus_qty')) {
            Schema::table('sale_return_items', function (Blueprint $table) {
                $table->dropColumn('bonus_qty');
            });
        }
    }
};
