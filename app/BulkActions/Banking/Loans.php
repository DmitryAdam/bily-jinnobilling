<?php

namespace App\BulkActions\Banking;

use App\Abstracts\BulkAction;
use App\Jobs\Banking\DeleteLoan;
use App\Models\Banking\Loan;

class Loans extends BulkAction
{
    public $model = Loan::class;

    public $text = 'general.loans';

    public $path = [
        'group' => 'banking',
        'type' => 'loans',
    ];

    public $actions = [
        'delete' => [
            'icon' => 'delete',
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'permission' => 'delete-banking-loans',
        ],
    ];

    public function destroy($request)
    {
        $loans = $this->getSelectedRecords($request);

        foreach ($loans as $loan) {
            try {
                $this->dispatch(new DeleteLoan($loan));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
