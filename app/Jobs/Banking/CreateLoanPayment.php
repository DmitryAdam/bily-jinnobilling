<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use App\Jobs\Banking\CreateTransaction;
use App\Models\Banking\Account;
use App\Models\Banking\Loan;
use App\Models\Banking\LoanPayment;
use App\Models\Banking\Transaction;
use App\Traits\Categories;
use App\Traits\Currencies;
use App\Traits\Transactions;

class CreateLoanPayment extends Job implements HasOwner, HasSource, ShouldCreate
{
    use Categories, Currencies, Transactions;

    public function handle(): LoanPayment
    {
        \DB::transaction(function () {
            $loan = Loan::find($this->request->get('loan_id'));
            $account_id = $this->request->get('account_id', $loan->account_id);
            $account = Account::find($account_id);

            $currency_code = $account->currency_code;
            $currency_rate = currency($currency_code)->getRate();

            $description = "Bayar Piutang {$loan->loan_number} - {$loan->contact_name}";
            $user_description = $this->request->get('description');
            if ($user_description) {
                $description .= " | {$user_description}";
            }

            $income_transaction = $this->dispatch(new CreateTransaction([
                'company_id' => $this->request['company_id'],
                'type' => Transaction::INCOME_TYPE,
                'number' => $this->getNextTransactionNumber(),
                'account_id' => $account_id,
                'paid_at' => $this->request->get('paid_at'),
                'currency_code' => $currency_code,
                'currency_rate' => $currency_rate,
                'amount' => $this->request->get('amount'),
                'contact_id' => 0,
                'description' => $description,
                'category_id' => $this->getLoanIncomeCategoryId(),
                'payment_method' => $this->request->get('payment_method'),
                'reference' => $this->request->get('reference'),
                'created_from' => $this->request->get('created_from'),
                'created_by' => $this->request->get('created_by'),
            ]));

            $this->model = LoanPayment::create([
                'company_id' => $this->request['company_id'],
                'loan_id' => $loan->id,
                'transaction_id' => $income_transaction->id,
                'account_id' => $account_id,
                'amount' => $this->request->get('amount'),
                'currency_code' => $currency_code,
                'currency_rate' => $currency_rate,
                'paid_at' => $this->request->get('paid_at'),
                'payment_method' => $this->request->get('payment_method'),
                'description' => $this->request->get('description'),
                'reference' => $this->request->get('reference'),
                'created_from' => $this->request->get('created_from'),
                'created_by' => $this->request->get('created_by'),
            ]);

            // Update loan status - refresh payments to include the newly created one
            $loan->load('payments');
            $paid_total = $loan->payments->sum('amount');

            if ($paid_total >= $loan->amount) {
                $loan->update(['status' => 'paid']);
            } else {
                $loan->update(['status' => 'partial']);
            }
        });

        return $this->model;
    }
}
