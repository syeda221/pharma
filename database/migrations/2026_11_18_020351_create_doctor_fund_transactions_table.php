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
        Schema::create('doctor_fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique(); // e.g. DF-REC-202611-0001 or DF-INV-202611-0001
            $table->enum('type', ['receiving', 'investment']); // receiving = Inflow from Distributor, investment = Outflow on Doctor
            
            // Dedicated Bank / Fund Account Info
            $table->unsignedBigInteger('account_id')->nullable(); // Optional link to accounts table
            $table->string('bank_account_name')->default('Doctor Promotion Fund Account'); // Name or title of the dedicated account
            
            // Distributor (Zone) & Doctor (Customer)
            $table->unsignedBigInteger('zone_id'); // Foreign key to zones table (represents distributor)
            $table->unsignedBigInteger('customer_id')->nullable(); // Foreign key to customers table (represents doctor)
            
            // Financial details
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->string('payment_method')->default('Cash'); // Cash, Cheque, Online Transfer, Deposit Slip, etc.
            $table->string('reference_no')->nullable(); // Cheque / Slip / Transaction ID
            $table->string('purpose_tag')->nullable(); // Marketing Support, Clinic Support, Incentive, Travel/CME, Gift/Sample, etc.
            
            // Attachment support (Image / PDF)
            $table->string('attachment')->nullable(); // Path to uploaded file
            $table->string('attachment_name')->nullable(); // Original file name
            $table->string('attachment_mime')->nullable(); // image/png, application/pdf, etc.
            
            // Description & Audit
            $table->text('narration')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();

            // Indexes for lightning fast queries
            $table->index('type');
            $table->index('zone_id');
            $table->index('customer_id');
            $table->index('transaction_date');
            $table->index('voucher_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_fund_transactions');
    }
};
