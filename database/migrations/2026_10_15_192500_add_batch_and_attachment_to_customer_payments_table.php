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
        Schema::table('customer_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_payments', 'batch_no')) {
                $table->string('batch_no')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('customer_payments', 'receipt_no')) {
                $table->string('receipt_no')->nullable()->unique()->after('batch_no');
            }
            if (!Schema::hasColumn('customer_payments', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('amount')
                      ->comment('Cash in Hand or Bank Account ID');
            }
            if (!Schema::hasColumn('customer_payments', 'cheque_no')) {
                $table->string('cheque_no')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('customer_payments', 'cheque_date')) {
                $table->date('cheque_date')->nullable()->after('cheque_no');
            }
            if (!Schema::hasColumn('customer_payments', 'attachment')) {
                $table->string('attachment')->nullable()->after('note')
                      ->comment('Path to uploaded image/pdf receipt slip');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn([
                'batch_no',
                'receipt_no',
                'account_id',
                'cheque_no',
                'cheque_date',
                'attachment',
            ]);
        });
    }
};
