<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Investments were a copy of loans with the money flowing the other way.
     * One table, one `type` column.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('loans', 'type')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->string('type')->default('loan')->after('company_id');
            });
        }

        if (! Schema::hasTable('investments')) {
            return;
        }

        foreach (DB::table('investments')->orderBy('id')->cursor() as $investment) {
            $row = (array) $investment;

            unset($row['id']);
            $row['type'] = 'investment';
            $row['loan_number'] = $row['investment_number'] ?? null;
            unset($row['investment_number']);

            $loan_id = DB::table('loans')->insertGetId($row);

            foreach (DB::table('investment_payments')->where('investment_id', $investment->id)->cursor() as $payment) {
                $payment_row = (array) $payment;

                unset($payment_row['id'], $payment_row['investment_id']);
                $payment_row['loan_id'] = $loan_id;

                DB::table('loan_payments')->insert($payment_row);
            }
        }

        Schema::dropIfExists('investment_payments');
        Schema::dropIfExists('investments');
    }

    /**
     * Splitting the rows back out would mean re-deriving which loan came from
     * where. Refusing beats silently losing them.
     */
    public function down(): void
    {
        throw new \RuntimeException(
            'Irreversible: investments were merged into loans. Restore from a backup instead.'
        );
    }
};
