<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('loan_number')->nullable()->after('company_id');
            $table->unique(['company_id', 'loan_number']);
        });

        // Backfill existing loans with loan numbers
        $loans = \DB::table('loans')->orderBy('id')->get();
        $counter = 1;

        foreach ($loans as $loan) {
            \DB::table('loans')
                ->where('id', $loan->id)
                ->update(['loan_number' => 'LOAN-' . str_pad($counter, 5, '0', STR_PAD_LEFT)]);
            $counter++;
        }
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'loan_number']);
            $table->dropColumn('loan_number');
        });
    }
};
