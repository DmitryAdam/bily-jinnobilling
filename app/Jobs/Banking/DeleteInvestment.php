<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldDelete;
use App\Jobs\Banking\DeleteTransaction;

class DeleteInvestment extends Job implements ShouldDelete
{
    public function handle(): bool
    {
        // Prevent deletion if investment has repayments
        if ($this->model->payments()->count() > 0) {
            $message = trans('investments.messages.has_payments');

            throw new \Exception($message);
        }

        \DB::transaction(function () {
            // Delete the investment's income transaction (money leaves the account)
            if ($this->model->transaction) {
                $this->dispatch(new DeleteTransaction($this->model->transaction));
            }

            $this->model->delete();
        });

        return true;
    }
}
