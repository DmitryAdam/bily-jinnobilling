<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldDelete;
use App\Jobs\Banking\DeleteTransaction;

class DeleteInvestmentPayment extends Job implements ShouldDelete
{
    public function handle(): bool
    {
        \DB::transaction(function () {
            $investment = $this->model->investment;

            // Delete the expense transaction
            if ($this->model->transaction) {
                $this->dispatch(new DeleteTransaction($this->model->transaction));
            }

            $this->model->delete();

            // Recalculate investment status
            $paid_total = $investment->payments()->sum('amount');

            if ($paid_total <= 0) {
                $investment->update(['status' => 'active']);
            } elseif ($paid_total >= $investment->amount) {
                $investment->update(['status' => 'paid']);
            } else {
                $investment->update(['status' => 'partial']);
            }
        });

        return true;
    }
}
