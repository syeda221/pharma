<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure default dedicated account setting exists
        $exists = DB::table('settings')->where('key', 'doctor_fund_account_name')->first();
        if ($exists) {
            DB::table('settings')->where('key', 'doctor_fund_account_name')->update([
                'value'       => 'SHAHZAIB RANA',
                'group'       => 'doctor_funds',
                'label'       => 'Doctor Fund Account Name',
                'description' => 'Dedicated account name used for Doctor Fund recoveries and investments',
                'updated_at'  => now(),
            ]);
        } else {
            DB::table('settings')->insert([
                'key'         => 'doctor_fund_account_name',
                'value'       => 'SHAHZAIB RANA',
                'type'        => 'string',
                'group'       => 'doctor_funds',
                'label'       => 'Doctor Fund Account Name',
                'description' => 'Dedicated account name used for Doctor Fund recoveries and investments',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        Cache::forget('setting_doctor_fund_account_name');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not delete setting on rollback
    }
};
