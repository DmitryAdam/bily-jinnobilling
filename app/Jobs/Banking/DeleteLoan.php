<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldDelete;
use App\Jobs\Banking\DeleteTransaction;

class DeleteLoan extends Job implements ShouldDelete
{
    public function handle(): bool
    {
        \DB::transaction(function () {
            // Delete all payment transactions first
            foreach ($this->model->payments as $payment) {
                if ($payment->transaction) {
                    $this->dispatch(new DeleteTransaction($payment->transaction));
                }
                $payment->delete();
            }

            // Delete the loan's expense transaction
            if ($this->model->transaction) {
                $this->dispatch(new DeleteTransaction($this->model->transaction));
            }

            $this->model->delete();
        });

        return true;
    }
}
