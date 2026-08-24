<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use App\Jobs\Banking\CreateTransaction;
use App\Models\Banking\Account;
use App\Models\Banking\Investment;
use App\Models\Banking\InvestmentPayment;
use App\Models\Banking\Transaction;
use App\Traits\Categories;
use App\Traits\Currencies;
use App\Traits\Transactions;

class CreateInvestmentPayment extends Job implements HasOwner, HasSource, ShouldCreate
{
    use Categories, Currencies, Transactions;

    public function handle(): InvestmentPayment
    {
        \DB::transaction(function () {
            $investment = Investment::find($this->request->get('investment_id'));
            $account_id = $this->request->get('account_id', $investment->account_id);
            $account = Account::find($account_id);

            $currency_code = $account->currency_code;
            $currency_rate = currency($currency_code)->getRate();

            $description = "Bayar Investasi {$investment->investment_number} - {$investment->contact_name}";
            $user_description = $this->request->get('description');
            if ($user_description) {
                $description .= " | {$user_description}";
            }

            $expense_transaction = $this->dispatch(new CreateTransaction([
                'company_id' => $this->request['company_id'],
                'type' => Transaction::EXPENSE_TYPE,
                'number' => $this->getNextTransactionNumber(),
                'account_id' => $account_id,
                'paid_at' => $this->request->get('paid_at'),
                'currency_code' => $currency_code,
                'currency_rate' => $currency_rate,
                'amount' => $this->request->get('amount'),
                'contact_id' => 0,
                'description' => $description,
                'category_id' => $this->getInvestmentExpenseCategoryId(),
                'payment_method' => $this->request->get('payment_method'),
                'reference' => $this->request->get('reference'),
                'created_from' => $this->request->get('created_from'),
                'created_by' => $this->request->get('created_by'),
            ]));

            $this->model = InvestmentPayment::create([
                'company_id' => $this->request['company_id'],
                'investment_id' => $investment->id,
                'transaction_id' => $expense_transaction->id,
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

            // Update investment status - refresh payments to include the newly created one
            $investment->load('payments');
            $paid_total = $investment->payments->sum('amount');

            if ($paid_total >= $investment->amount) {
                $investment->update(['status' => 'paid']);
            } else {
                $investment->update(['status' => 'partial']);
            }
        });

        return $this->model;
    }
}
