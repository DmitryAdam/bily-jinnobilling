<?php

namespace Tests\Feature\Banking;

use App\Jobs\Banking\CreateAccount;
use App\Jobs\Banking\CreateLoan;
use App\Jobs\Banking\CreateLoanPayment;
use App\Models\Banking\Account;
use App\Models\Banking\Loan;
use App\Models\Banking\Transaction;
use Tests\Feature\FeatureTestCase;

/**
 * Loans and investments share one table and one controller. These cover the
 * part that is not shared: the money flows the opposite way, and each type
 * numbers itself independently.
 */
class LoansTest extends FeatureTestCase
{
    public function testItShouldSeeBothIndexPages()
    {
        $this->loginAs()->get(route('loans.index'))->assertStatus(200);
        $this->loginAs()->get(route('investments.index'))->assertStatus(200);
    }

    public function testALoanPaysOutAndAnInvestmentTakesIn()
    {
        $loan = $this->createRecord('loan');
        $investment = $this->createRecord('investment');

        $this->assertSame(
            Transaction::EXPENSE_TYPE,
            Transaction::withoutGlobalScope('App\Scopes\Transaction')->find($loan->transaction_id)->type
        );

        $this->assertSame(
            Transaction::INCOME_TYPE,
            Transaction::withoutGlobalScope('App\Scopes\Transaction')->find($investment->transaction_id)->type
        );
    }

    public function testEachTypeNumbersItselfIndependently()
    {
        $this->assertSame('LOAN-00001', $this->createRecord('loan')->loan_number);
        $this->assertSame('LOAN-00002', $this->createRecord('loan')->loan_number);
        $this->assertSame('INVEST-00001', $this->createRecord('investment')->loan_number);
    }

    public function testPaymentsMoveTheStatusAndReverseTheFlow()
    {
        $loan = $this->createRecord('loan', ['amount' => 1000]);

        $payment = $this->dispatch(new CreateLoanPayment($this->paymentRequest($loan, 400)));

        $this->assertSame('partial', $loan->fresh()->status);
        $this->assertSame(
            Transaction::INCOME_TYPE,
            Transaction::withoutGlobalScope('App\Scopes\Transaction')->find($payment->transaction_id)->type
        );

        $this->dispatch(new CreateLoanPayment($this->paymentRequest($loan, 600)));

        $this->assertSame('paid', $loan->fresh()->status);
    }

    public function testAnIndexOnlyListsItsOwnType()
    {
        $this->createRecord('loan');
        $this->createRecord('investment');

        $this->assertSame(1, Loan::type('loan')->count());
        $this->assertSame(1, Loan::type('investment')->count());
    }

    /**
     * A fresh install never has the legacy tables, so the migration's data-move
     * branch is only exercised here. This is the path real installs take.
     */
    public function testTheMergeMigrationMovesLegacyInvestments()
    {
        $account = $this->dispatch(new CreateAccount(Account::factory()->enabled()->raw()));

        \Schema::create('investments', function ($table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('investment_number')->nullable();
            $table->integer('account_id');
            $table->integer('transaction_id')->nullable();
            $table->double('amount');
            $table->string('currency_code');
            $table->double('currency_rate');
            $table->string('contact_name');
            $table->text('description')->nullable();
            $table->dateTime('issued_at');
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->string('status');
            $table->string('created_from')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        \Schema::create('investment_payments', function ($table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('investment_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('account_id');
            $table->double('amount');
            $table->string('currency_code');
            $table->double('currency_rate');
            $table->dateTime('paid_at');
            $table->string('payment_method')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('created_from')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        $legacy = [
            'company_id' => $this->company->id, 'investment_number' => 'INVEST-00007',
            'account_id' => $account->id, 'amount' => 9000, 'currency_code' => 'USD',
            'currency_rate' => 1, 'contact_name' => 'Legacy Investor',
            'issued_at' => now(), 'status' => 'partial',
            'payment_method' => 'offline-payments.cash.1',
        ];

        $id = \DB::table('investments')->insertGetId($legacy);

        \DB::table('investment_payments')->insert([
            'company_id' => $this->company->id, 'investment_id' => $id,
            'account_id' => $account->id, 'amount' => 2000, 'currency_code' => 'USD',
            'currency_rate' => 1, 'paid_at' => now(),
            'payment_method' => 'offline-payments.cash.1',
        ]);

        (require base_path('database/migrations/2026_08_25_000000_merge_investments_into_loans.php'))->up();

        $moved = Loan::type('investment')->where('loan_number', 'INVEST-00007')->first();

        $this->assertNotNull($moved, 'the legacy investment did not survive the merge');
        $this->assertSame('Legacy Investor', $moved->contact_name);
        $this->assertEquals(2000, $moved->payments()->sum('amount'));
        $this->assertFalse(\Schema::hasTable('investments'));
        $this->assertFalse(\Schema::hasTable('investment_payments'));
    }

    protected function createRecord(string $type, array $overrides = []): Loan
    {
        $account = $this->dispatch(new CreateAccount(Account::factory()->enabled()->raw()));

        return $this->dispatch(new CreateLoan(array_merge([
            'company_id'     => $this->company->id,
            'type'           => $type,
            'account_id'     => $account->id,
            'amount'         => 5000,
            'issued_at'      => now()->format('Y-m-d'),
            'contact_name'   => 'Test Contact',
            'payment_method' => 'offline-payments.cash.1',
        ], $overrides)));
    }

    protected function paymentRequest(Loan $loan, float $amount): array
    {
        return [
            'company_id'     => $this->company->id,
            'loan_id'        => $loan->id,
            'account_id'     => $loan->account_id,
            'amount'         => $amount,
            'paid_at'        => now()->format('Y-m-d'),
            'payment_method' => 'offline-payments.cash.1',
        ];
    }
}
