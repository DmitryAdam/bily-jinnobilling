<?php

namespace App\Http\Controllers\Sales;

use App\Abstracts\Http\Controller;
use App\Exports\Sales\Invoices\Invoices as Export;
use App\Http\Requests\Document\Document as Request;
use App\Jobs\Document\CreateDocument;
use App\Jobs\Document\CreateDocumentHistory;
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

        $versions = app(QuotationVersion::class)->getVersionHistory($quotation);

        return view('sales.quotations.show', compact('quotation', 'versions'));
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
     * Move the quotation to another status.
     *
     * @param  Document $quotation
     * @param  string   $status
     *
     * @return Response
     */
    public function mark(Document $quotation, string $status)
    {
        // Statuses the document layer already owns fire their event; the
        // quotation-only ones are set here.
        $marks = [
            'sent'      => [\App\Events\Document\DocumentMarkedSent::class, 'documents.messages.marked_sent'],
            'cancelled' => [\App\Events\Document\DocumentCancelled::class,  'documents.messages.marked_cancelled'],
            'restored'  => [\App\Events\Document\DocumentRestored::class,   'documents.messages.restored'],
            'accepted'  => [null, 'quotations.messages.marked_accepted'],
            'rejected'  => [null, 'quotations.messages.marked_rejected'],
        ];

        abort_unless(isset($marks[$status]), 404);

        [$event, $key] = $marks[$status];

        $message = trans($key, ['type' => trans_choice('general.quotations', 1)]);

        if ($event) {
            event(new $event($quotation));
        } else {
            $quotation->update(['status' => $status]);

            $this->dispatch(new CreateDocumentHistory($quotation, 0, $message));
        }

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
        return view('sales.quotations.revise', compact('quotation'));
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
        $invoice = \DB::transaction(function () use ($quotation) {
            $invoice = $quotation->duplicate();

            $invoice->fill([
                'type'            => Document::INVOICE_TYPE,
                'document_number' => app(\App\Utilities\DocumentNumber::class)
                                        ->getNextNumber(Document::INVOICE_TYPE, $quotation->contact),
                'parent_id'       => 0,
                'version'         => 1,
                'revision_notes'  => null,
            ])->save();

            // The copied rows carry the quotation type on their own column
            foreach (['items', 'item_taxes', 'totals'] as $relation) {
                $invoice->{$relation}()->update(['type' => Document::INVOICE_TYPE]);
            }

            return $invoice;
        });

        flash(trans('quotations.messages.converted_to_invoice'))->success();

        return redirect()->route('invoices.show', ['invoice' => $invoice->id]);
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
