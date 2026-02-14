<?php

namespace App\Jobs\Banking;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldUpdate;
use App\Models\Banking\Loan;

class UpdateLoan extends Job implements ShouldUpdate
{
    public function handle(): Loan
    {
        \DB::transaction(function () {
            $this->model->update([
                'contact_name' => $this->request->get('contact_name', $this->model->contact_name),
                'description' => $this->request->get('description', $this->model->description),
                'reference' => $this->request->get('reference', $this->model->reference),
            ]);
        });

        return $this->model;
    }
}
