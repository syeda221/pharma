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
        Schema::table('customer_types', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_types', 'discount_medical')) {
                $table->decimal('discount_medical', 5, 2)->default(0.00)->after('discount_percentage');
            }
            if (!Schema::hasColumn('customer_types', 'discount_doctor')) {
                $table->decimal('discount_doctor', 5, 2)->default(0.00)->after('discount_medical');
            }
            if (!Schema::hasColumn('customer_types', 'discount_distribution')) {
                $table->decimal('discount_distribution', 5, 2)->default(0.00)->after('discount_doctor');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'custom_discount_medical')) {
                $table->decimal('custom_discount_medical', 5, 2)->nullable()->after('custom_discount_percentage');
            }
            if (!Schema::hasColumn('customers', 'custom_discount_doctor')) {
                $table->decimal('custom_discount_doctor', 5, 2)->nullable()->after('custom_discount_medical');
            }
            if (!Schema::hasColumn('customers', 'custom_discount_distribution')) {
                $table->decimal('custom_discount_distribution', 5, 2)->nullable()->after('custom_discount_doctor');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'discount_tier')) {
                $table->string('discount_tier', 30)->nullable()->after('total_extradiscount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_types', function (Blueprint $table) {
            $table->dropColumn(['discount_medical', 'discount_doctor', 'discount_distribution']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['custom_discount_medical', 'custom_discount_doctor', 'custom_discount_distribution']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['discount_tier']);
        });
    }
};
