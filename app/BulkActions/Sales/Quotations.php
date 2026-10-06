<?php

namespace App\BulkActions\Sales;

use App\Abstracts\BulkAction;
use App\Exports\Sales\Invoices\Invoices as Export;
use App\Jobs\Document\DeleteDocument;
use App\Models\Document\Document;

class Quotations extends BulkAction
{
    public $model = Document::class;

    public $text = 'general.quotations';

    public $path = [
        'group' => 'sales',
        'type' => 'quotations',
    ];

    public $actions = [
        'delete' => [
            'icon'          => 'delete',
            'name'          => 'general.delete',
            'message'       => 'bulk_actions.message.delete',
            'permission'    => 'delete-sales-quotations',
        ],
        'export' => [
            'icon'          => 'file_download',
            'name'          => 'general.export',
            'message'       => 'bulk_actions.message.export',
            'type'          => 'download',
        ],
        'download' => [
            'icon'          => 'download',
            'name'          => 'general.download',
            'message'       => 'bulk_actions.message.download',
            'type'          => 'download',
        ],
    ];

    public function destroy($request)
    {
        $quotations = $this->getSelectedRecords($request, ['items', 'item_taxes', 'histories', 'totals']);

        foreach ($quotations as $quotation) {
            try {
                $this->dispatch(new DeleteDocument($quotation));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }

    public function export($request)
    {
        $export = new Export($this->getSelectedInput($request));
        $export->type = Document::QUOTATION_TYPE;

        return $this->exportExcel($export, trans_choice('general.quotations', 2));
    }

    public function download($request)
    {
        $selected = $this->getSelectedRecords($request);

        $file_name = Document::QUOTATION_TYPE . '-' . date('Y-m-d-H-i-s');

        return $this->downloadPdf($selected, '\App\Jobs\Document\DownloadDocument', $file_name, trans_choice('general.quotations', 2));
    }
}
