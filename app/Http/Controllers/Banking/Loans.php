<?php

namespace App\Http\Controllers\Banking;

use App\Abstracts\Http\Controller;
use App\Http\Requests\Banking\Loan as Request;
use App\Http\Requests\Banking\LoanPayment as LoanPaymentRequest;
use App\Jobs\Banking\CreateLoan;
use App\Jobs\Banking\UpdateLoan;
use App\Jobs\Banking\DeleteLoan;
use App\Jobs\Banking\CreateLoanPayment;
use App\Jobs\Banking\DeleteLoanPayment;
use App\Models\Banking\Account;
use App\Models\Banking\Loan;
use App\Models\Banking\LoanPayment;
use App\Models\Setting\Currency;

class Loans extends Controller
{
    /**
     * Loans and investments are the same records with the money flowing the
     * other way; the subclass only swaps this.
     */
    public string $type = 'loan';

    protected function slug(): string
    {
        return Loan::TYPES[$this->type]['slug'];
    }

    protected function label(int $count = 1): string
    {
        return trans_choice('general.' . $this->slug(), $count);
    }

    /**
     * Everything the views need to render either type.
     */
    protected function chrome(array $extra = []): array
    {
        return array_merge([
            'type' => $this->type,
            'slug' => $this->slug(),
            'lang' => $this->slug(),
            'icon' => Loan::TYPES[$this->type]['icon'],
            'bulkAction' => 'App\\BulkActions\\Banking\\' . ucfirst($this->slug()),
        ], $extra);
    }

    public function index()
    {
        $items = Loan::type($this->type)->with('account', 'payments')->collect(['issued_at' => 'desc']);

        $total = Loan::type($this->type)->sum('amount');
        $totalPaid = LoanPayment::whereHas('loan', fn ($q) => $q->type($this->type))->sum('amount');

        return $this->response('banking.loans.index', $this->chrome([
            'items'        => $items,
            'total'        => $total,
            'totalPaid'    => $totalPaid,
            'totalUnpaid'  => $total - $totalPaid,
            'currency'     => default_currency(),
        ]));
    }

    public function create()
    {
        return view('banking.loans.create', $this->chrome([
            'accounts' => $this->accounts(),
            'currency' => Currency::where('code', default_currency())->first(),
        ]));
    }

    public function store(Request $request)
    {
        $request->merge(['type' => $this->type]);

        $response = $this->ajaxDispatch(new CreateLoan($request));

        if ($response['success']) {
            $response['redirect'] = route($this->slug() . '.show', $response['data']->id);

            flash(trans('messages.success.created', ['type' => $this->label()]))->success();
        } else {
            $response['redirect'] = route($this->slug() . '.create');

            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function show(Loan $loan)
    {
        $loan->load('account', 'payments.account', 'payments.transaction');

        return view('banking.loans.show', $this->chrome([
            'loan'     => $loan,
            'accounts' => $this->accounts(),
            'currency' => Currency::where('code', $loan->currency_code)->first(),
        ]));
    }

    public function edit(Loan $loan)
    {
        $loan->load('account');

        return view('banking.loans.edit', $this->chrome([
            'loan'     => $loan,
            'accounts' => $this->accounts(),
            'currency' => Currency::where('code', $loan->currency_code)->first(),
        ]));
    }

    public function update(Request $request, Loan $loan)
    {
        $response = $this->ajaxDispatch(new UpdateLoan($loan, $request));

        if ($response['success']) {
            $response['redirect'] = route($this->slug() . '.show', $loan->id);

            flash(trans('messages.success.updated', ['type' => $this->label()]))->success();
        } else {
            $response['redirect'] = route($this->slug() . '.edit', $loan->id);

            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function destroy(Loan $loan)
    {
        $response = $this->ajaxDispatch(new DeleteLoan($loan));

        $response['redirect'] = route($this->slug() . '.index');

        if ($response['success']) {
            flash(trans('messages.success.deleted', ['type' => $this->label()]))->success();
        } else {
            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function paymentStore(LoanPaymentRequest $request, Loan $loan)
    {
        $request->merge(['loan_id' => $loan->id]);

        $response = $this->ajaxDispatch(new CreateLoanPayment($request));

        $response['redirect'] = route($this->slug() . '.show', $loan->id);

        if ($response['success']) {
            flash(trans('messages.success.created', ['type' => trans($this->slug() . '.payment')]))->success();
        } else {
            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    public function paymentDestroy(Loan $loan, LoanPayment $payment)
    {
        $response = $this->ajaxDispatch(new DeleteLoanPayment($payment));

        $response['redirect'] = route($this->slug() . '.show', $loan->id);

        if ($response['success']) {
            flash(trans('messages.success.deleted', ['type' => trans($this->slug() . '.payment')]))->success();
        } else {
            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    protected function accounts()
    {
        return Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');
    }
}
