<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('customer_id');
            }
        });

        // Backfill account_id from existing receipt vouchers if available
        try {
            $receiptVouchers = DB::table('voucher_masters')
                ->where('voucher_type', 'receipt')
                ->get();

            foreach ($receiptVouchers as $vm) {
                if (preg_match('/#([A-Za-z0-9\-]+)/', $vm->remarks, $matches)) {
                    $invNo = $matches[1];
                    $detail = DB::table('voucher_details')
                        ->where('voucher_master_id', $vm->id)
                        ->where('debit', '>', 0)
                        ->first();

                    if ($detail && $detail->account_id) {
                        DB::table('sales')
                            ->where('invoice_no', $invNo)
                            ->update(['account_id' => $detail->account_id]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore backfill errors
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'account_id')) {
                $table->dropColumn('account_id');
            }
        });
    }
};
