<?php

namespace App\Exports\Sales\Invoices;

use App\Exports\Sales\Invoices\Sheets\Invoices as Base;
use App\Exports\Sales\Invoices\Sheets\InvoiceItems;
use App\Exports\Sales\Invoices\Sheets\InvoiceItemTaxes;
use App\Exports\Sales\Invoices\Sheets\InvoiceHistories;
use App\Exports\Sales\Invoices\Sheets\InvoiceTotals;
use App\Exports\Sales\Invoices\Sheets\InvoiceTransactions;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class Invoices implements WithMultipleSheets
{
    use Exportable;

    public $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public $type = 'invoice';

    public function sheets(): array
    {
        $sheets = array_map(function ($sheet) {
            $sheet->type = $this->type;

            return $sheet;
        }, [
            new Base($this->ids),
            new InvoiceItems($this->ids),
            new InvoiceItemTaxes($this->ids),
            new InvoiceHistories($this->ids),
            new InvoiceTotals($this->ids),
        ]);

        // quotations have no payments
        if ($this->type == 'invoice') {
            $sheets[] = new InvoiceTransactions($this->ids);
        }

        return $sheets;
    }
}
