<?php

namespace App\Http\Controllers\Sales;

use App\Abstracts\Http\Controller;
use App\Exports\Sales\Invoices\Invoices as Export;
use App\Http\Requests\Document\Document as Request;
use App\Jobs\Document\CreateDocument;
use App\Jobs\Document\DeleteDocument;
use App\Jobs\Document\DuplicateDocument;
use App\Jobs\Document\DownloadDocument;
use App\Jobs\Document\SendDocument;
use App\Jobs\Document\UpdateDocument;
use App\Models\Document\Document;
use App\Traits\Documents;
use App\Utilities\QuotationVersion;

class Quotations extends Controller
{
    use Documents;

    public string $type = Document::QUOTATION_TYPE;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->setActiveTabForDocuments();

        $quotations = Document::quotation()->with('contact', 'items', 'items.taxes', 'item_taxes', 'last_history', 'totals', 'histories', 'media')->collect(['document_number' => 'desc']);

        $total_quotations = Document::quotation()->count();

        return $this->response('sales.quotations.index', compact('quotations', 'total_quotations'));
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function show(Document $quotation)
    {
        $quotation->load([
            'items.taxes.tax',
            'items.item',
            'totals',
            'contact',
            'currency',
            'category',
            'histories',
            'media',
        ]);

        $quotationVersion = app(QuotationVersion::class);
        $versions = $quotationVersion->getVersionHistory($quotation);
        $displayNumber = $quotationVersion->getDisplayNumber($quotation);

        return view('sales.quotations.show', compact('quotation', 'versions', 'displayNumber'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('sales.quotations.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $response = $this->ajaxDispatch(new CreateDocument($request));

        if ($response['success']) {
            $response['redirect'] = route('quotations.show', ['quotation' => $response['data']->id]);

            $message = trans('messages.success.created', ['type' => trans_choice('general.quotations', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('quotations.create');

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function duplicate(Document $quotation)
    {
        $clone = $this->dispatch(new DuplicateDocument($quotation));

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.quotations', 1)]);

        flash($message)->success();

        return redirect()->route('quotations.edit', $clone->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function edit(Document $quotation)
    {
        return view('sales.quotations.edit', compact('quotation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Document $quotation
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Document $quotation, Request $request)
    {
        $response = $this->ajaxDispatch(new UpdateDocument($quotation, $request));

        if ($response['success']) {
            $response['redirect'] = route('quotations.show', ['quotation' => $response['data']->id]);

            $message = trans('messages.success.updated', ['type' => trans_choice('general.quotations', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('quotations.edit', $quotation->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function destroy(Document $quotation)
    {
        $response = $this->ajaxDispatch(new DeleteDocument($quotation));

        $response['redirect'] = route('quotations.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans_choice('general.quotations', 1)]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Export the specified resource.
     *
     * @return Response
     */
    public function export()
    {
        return $this->exportExcel(new Export, trans_choice('general.quotations', 2));
    }

    /**
     * Mark the quotation as sent.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function markSent(Document $quotation)
    {
        event(new \App\Events\Document\DocumentMarkedSent($quotation));

        $message = trans('documents.messages.marked_sent', ['type' => trans_choice('general.quotations', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    /**
     * Mark the quotation as accepted.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function markAccepted(Document $quotation)
    {
        event(new \App\Events\Document\QuotationAccepted($quotation));

        $message = trans('quotations.messages.marked_accepted', ['type' => trans_choice('general.quotations', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    /**
     * Mark the quotation as rejected.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function markRejected(Document $quotation)
    {
        event(new \App\Events\Document\QuotationRejected($quotation));

        $message = trans('quotations.messages.marked_rejected', ['type' => trans_choice('general.quotations', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    /**
     * Mark the quotation as cancelled.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function markCancelled(Document $quotation)
    {
        event(new \App\Events\Document\DocumentCancelled($quotation));

        $message = trans('documents.messages.marked_cancelled', ['type' => trans_choice('general.quotations', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    /**
     * Restore the quotation.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function restoreQuotation(Document $quotation)
    {
        event(new \App\Events\Document\DocumentRestored($quotation));

        $message = trans('documents.messages.restored', ['type' => trans_choice('general.quotations', 1)]);

        flash($message)->success();

        return redirect()->back();
    }

    /**
     * Show the revision form with notes input.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function reviseForm(Document $quotation)
    {
        $quotationVersion = app(QuotationVersion::class);
        $displayNumber = $quotationVersion->getDisplayNumber($quotation);

        return view('sales.quotations.revise', compact('quotation', 'displayNumber'));
    }

    /**
     * Create a new revision (version) of the quotation.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function revise(Document $quotation)
    {
        $revisionNotes = request('revision_notes', '');

        $newVersion = app(QuotationVersion::class)->createNewVersion($quotation, $revisionNotes);

        $message = trans('quotations.messages.revised');

        flash($message)->success();

        return redirect()->route('quotations.edit', $newVersion->id);
    }

    /**
     * Generate the next quotation number.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateNumber()
    {
        $number = app(\App\Utilities\DocumentNumber::class)->getNextNumber('quotation', null);

        return response()->json(['number' => $number]);
    }

    /**
     * Convert the quotation to an invoice.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function convertToInvoice(Document $quotation)
    {
        $quotation->load(['items.taxes', 'totals']);

        $invoiceData = $quotation->toArray();
        $invoiceData['type'] = Document::INVOICE_TYPE;
        $invoiceData['status'] = 'draft';
        unset(
            $invoiceData['id'],
            $invoiceData['version'],
            $invoiceData['parent_id'],
            $invoiceData['created_at'],
            $invoiceData['updated_at'],
            $invoiceData['deleted_at'],
            $invoiceData['attachment'],
            $invoiceData['amount_without_tax'],
            $invoiceData['discount'],
            $invoiceData['paid'],
            $invoiceData['received_at'],
            $invoiceData['status_label'],
            $invoiceData['sent_at'],
            $invoiceData['reconciled'],
            $invoiceData['contact_location']
        );

        // Build request-like data for CreateDocument
        $request = new \App\Http\Requests\Document\Document();
        $request->merge($invoiceData);

        // Add items data
        $items = [];
        foreach ($quotation->items as $item) {
            $itemData = [
                'item_id' => $item->item_id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount_rate,
                'tax_ids' => $item->taxes->pluck('tax_id')->toArray(),
            ];
            $items[] = $itemData;
        }
        $request->merge(['items' => $items]);

        $response = $this->ajaxDispatch(new CreateDocument($request));

        if ($response['success']) {
            $message = trans('quotations.messages.converted_to_invoice');

            flash($message)->success();

            return redirect()->route('invoices.show', ['invoice' => $response['data']->id]);
        }

        flash($response['message'])->error()->important();

        return redirect()->back();
    }

    /**
     * Print the quotation.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function printQuotation(Document $quotation)
    {
        event(new \App\Events\Document\DocumentPrinting($quotation));

        $view = view($quotation->template_path, compact('quotation'));

        return mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
    }

    /**
     * Download the PDF file of quotation.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function pdfQuotation(Document $quotation)
    {
        event(new \App\Events\Document\DocumentPrinting($quotation));

        return $this->dispatch(new DownloadDocument($quotation, null, null, false, 'download'));
    }

    /**
     * Send the quotation via email.
     *
     * @param  Document $quotation
     *
     * @return Response
     */
    public function emailQuotation(Document $quotation)
    {
        if (empty($quotation->contact_email)) {
            return redirect()->back();
        }

        $response = $this->ajaxDispatch(new SendDocument($quotation));

        if ($response['success']) {
            $message = trans('documents.messages.email_sent', ['type' => trans_choice('general.quotations', 1)]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return redirect()->back();
    }
}
