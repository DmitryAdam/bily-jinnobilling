<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldDelete;
use App\Jobs\Banking\DeleteTransaction;

class DeleteLoanPayment extends Job implements ShouldDelete
{
    public function handle(): bool
    {
        \DB::transaction(function () {
            $loan = $this->model->loan;

            // Delete the income transaction
            if ($this->model->transaction) {
                $this->dispatch(new DeleteTransaction($this->model->transaction));
            }

            $this->model->delete();

            $loan->refreshStatus();
        });

        return true;
    }
}
