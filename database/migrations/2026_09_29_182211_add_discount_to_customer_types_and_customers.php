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
        if (!Schema::hasColumn('customer_types', 'discount_percentage')) {
            Schema::table('customer_types', function (Blueprint $table) {
                $table->decimal('discount_percentage', 5, 2)->default(0.00)->after('description');
            });
        }

        if (!Schema::hasColumn('customers', 'custom_discount_percentage')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('custom_discount_percentage', 5, 2)->nullable()->after('customer_type');
            });
        }

        // Seed / Update Doctor customer type with 10% default discount
        \Illuminate\Support\Facades\DB::table('customer_types')->updateOrInsert(
            ['name' => 'Doctor'],
            [
                'description' => 'Doctor customer with 10% discount',
                'discount_percentage' => 10.00,
                'is_static' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('customer_types', 'discount_percentage')) {
            Schema::table('customer_types', function (Blueprint $table) {
                $table->dropColumn('discount_percentage');
            });
        }

        if (Schema::hasColumn('customers', 'custom_discount_percentage')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('custom_discount_percentage');
            });
        }
    }
};
