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
            $config = $loan->config;

            $account_id = $this->request->get('account_id', $loan->account_id);
            $account = Account::find($account_id);

            $currency_code = $account->currency_code;
            $currency_rate = currency($currency_code)->getRate();

            $description = trans($config['slug'] . '.payment_description', [
                'number' => $loan->loan_number,
                'name'   => $loan->contact_name,
            ]);

            if ($note = $this->request->get('description')) {
                $description .= " | {$note}";
            }

            $transaction = $this->dispatch(new CreateTransaction([
                'company_id' => $this->request['company_id'],
                'type' => $config['payment_type'],
                'number' => $this->getNextTransactionNumber(),
                'account_id' => $account_id,
                'paid_at' => $this->request->get('paid_at'),
                'currency_code' => $currency_code,
                'currency_rate' => $currency_rate,
                'amount' => $this->request->get('amount'),
                'contact_id' => 0,
                'description' => $description,
                'category_id' => $this->getAutoCategoryId($config['payment_category']),
                'payment_method' => $this->request->get('payment_method'),
                'reference' => $this->request->get('reference'),
                'created_from' => $this->request->get('created_from'),
                'created_by' => $this->request->get('created_by'),
            ]));

            $this->model = LoanPayment::create([
                'company_id' => $this->request['company_id'],
                'loan_id' => $loan->id,
                'transaction_id' => $transaction->id,
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

            $loan->refreshStatus();
        });

        return $this->model;
    }
}
