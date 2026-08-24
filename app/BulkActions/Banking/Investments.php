<?php

namespace App\BulkActions\Banking;

use App\Abstracts\BulkAction;
use App\Jobs\Banking\DeleteInvestment;
use App\Models\Banking\Investment;

class Investments extends BulkAction
{
    public $model = Investment::class;

    public $text = 'general.investments';

    public $path = [
        'group' => 'banking',
        'type' => 'investments',
    ];

    public $actions = [
        'delete' => [
            'icon' => 'delete',
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'permission' => 'delete-banking-investments',
        ],
    ];

    public function destroy($request)
    {
        $investments = $this->getSelectedRecords($request);

        foreach ($investments as $investment) {
            try {
                $this->dispatch(new DeleteInvestment($investment));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
