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

            // Recalculate loan status
            $paid_total = $loan->payments()->sum('amount');

            if ($paid_total <= 0) {
                $loan->update(['status' => 'active']);
            } elseif ($paid_total >= $loan->amount) {
                $loan->update(['status' => 'paid']);
            } else {
                $loan->update(['status' => 'partial']);
            }
        });

        return true;
    }
}
