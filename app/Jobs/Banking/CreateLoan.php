<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use App\Jobs\Banking\CreateTransaction;
use App\Models\Banking\Account;
use App\Models\Banking\Loan;
use App\Models\Banking\Transaction;
use App\Traits\Categories;
use App\Traits\Currencies;
use App\Traits\Transactions;

class CreateLoan extends Job implements HasOwner, HasSource, ShouldCreate
{
    use Categories, Currencies, Transactions;

    public function handle(): Loan
    {
        \DB::transaction(function () {
            $account = Account::find($this->request->get('account_id'));

            $currency_code = $account->currency_code;
            $currency_rate = currency($currency_code)->getRate();

            $loan_number = Loan::getNextLoanNumber($this->request['company_id']);
            $contact_name = $this->request->get('contact_name');

            $description = "Piutang {$loan_number} - {$contact_name}";
            $user_description = $this->request->get('description');
            if ($user_description) {
                $description .= " | {$user_description}";
            }

            $expense_transaction = $this->dispatch(new CreateTransaction([
                'company_id' => $this->request['company_id'],
                'type' => Transaction::EXPENSE_TYPE,
                'number' => $this->getNextTransactionNumber(),
                'account_id' => $this->request->get('account_id'),
                'paid_at' => $this->request->get('issued_at'),
                'currency_code' => $currency_code,
                'currency_rate' => $currency_rate,
                'amount' => $this->request->get('amount'),
                'contact_id' => 0,
                'description' => $description,
                'category_id' => $this->getLoanExpenseCategoryId(),
                'payment_method' => $this->request->get('payment_method'),
                'reference' => $this->request->get('reference'),
                'created_from' => $this->request->get('created_from'),
                'created_by' => $this->request->get('created_by'),
            ]));

            $this->model = Loan::create([
                'company_id' => $this->request['company_id'],
                'loan_number' => $loan_number,
                'account_id' => $this->request->get('account_id'),
                'transaction_id' => $expense_transaction->id,
                'amount' => $this->request->get('amount'),
                'currency_code' => $currency_code,
                'currency_rate' => $currency_rate,
                'contact_name' => $contact_name,
                'description' => $this->request->get('description'),
                'issued_at' => $this->request->get('issued_at'),
                'payment_method' => $this->request->get('payment_method'),
                'reference' => $this->request->get('reference'),
                'status' => 'active',
                'created_from' => $this->request->get('created_from'),
                'created_by' => $this->request->get('created_by'),
            ]);
        });

        return $this->model;
    }
}
