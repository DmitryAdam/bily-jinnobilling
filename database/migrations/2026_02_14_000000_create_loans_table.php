<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('account_id');
            $table->integer('transaction_id')->nullable();
            $table->double('amount', 15, 4);
            $table->string('currency_code', 3);
            $table->double('currency_rate', 15, 8)->default(1);
            $table->string('contact_name');
            $table->text('description')->nullable();
            $table->datetime('issued_at');
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->string('status')->default('active');
            $table->string('created_from')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('account_id');
            $table->index('transaction_id');
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('loan_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('account_id');
            $table->double('amount', 15, 4);
            $table->string('currency_code', 3);
            $table->double('currency_rate', 15, 8)->default(1);
            $table->datetime('paid_at');
            $table->string('payment_method');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('created_from')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('loan_id');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
    }
};
