<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldDelete;
use App\Jobs\Banking\DeleteTransaction;

class DeleteLoan extends Job implements ShouldDelete
{
    public function handle(): bool
    {
        // Prevent deletion if loan has payments
        if ($this->model->payments()->count() > 0) {
            $message = trans($this->model->slug . '.messages.has_payments');

            throw new \Exception($message);
        }

        \DB::transaction(function () {
            // Reverse the principal transaction
            if ($this->model->transaction) {
                $this->dispatch(new DeleteTransaction($this->model->transaction));
            }

            $this->model->delete();
        });

        return true;
    }
}
