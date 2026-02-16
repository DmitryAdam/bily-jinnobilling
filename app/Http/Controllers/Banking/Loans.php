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
    public function index()
    {
        $loans = Loan::with('account', 'payments')->collect(['issued_at' => 'desc']);

        $allLoans = Loan::with('payments')->where('company_id', company_id())->get();
        $totalPiutang = $allLoans->sum('amount');
        $totalPaid = $allLoans->sum(fn($l) => $l->paid_amount);
        $totalUnpaid = $totalPiutang - $totalPaid;

        $currency = default_currency();

        return $this->response('banking.loans.index', compact('loans', 'totalPiutang', 'totalPaid', 'totalUnpaid', 'currency'));
    }

    public function create()
    {
        $accounts = Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');

        $currency = Currency::where('code', default_currency())->first();

        return view('banking.loans.create', compact('accounts', 'currency'));
    }

    public function store(Request $request)
    {
        $response = $this->ajaxDispatch(new CreateLoan($request));

        if ($response['success']) {
            $response['redirect'] = route('loans.show', $response['data']->id);

            $message = trans('messages.success.created', ['type' => trans_choice('general.loans', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('loans.create');

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function show(Loan $loan)
    {
        $loan->load('account', 'payments.account', 'payments.transaction');

        $accounts = Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');

        $currency = Currency::where('code', $loan->currency_code)->first();

        return view('banking.loans.show', compact('loan', 'accounts', 'currency'));
    }

    public function edit(Loan $loan)
    {
        $loan->load('account');

        $accounts = Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');

        $currency = Currency::where('code', $loan->currency_code)->first();

        return view('banking.loans.edit', compact('loan', 'accounts', 'currency'));
    }

    public function update(Request $request, Loan $loan)
    {
        $response = $this->ajaxDispatch(new UpdateLoan($loan, $request));

        if ($response['success']) {
            $response['redirect'] = route('loans.show', $loan->id);

            $message = trans('messages.success.updated', ['type' => trans_choice('general.loans', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('loans.edit', $loan->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function destroy(Loan $loan)
    {
        $response = $this->ajaxDispatch(new DeleteLoan($loan));

        $response['redirect'] = route('loans.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans_choice('general.loans', 1)]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function paymentStore(LoanPaymentRequest $request, Loan $loan)
    {
        $request->merge(['loan_id' => $loan->id]);

        $response = $this->ajaxDispatch(new CreateLoanPayment($request));

        if ($response['success']) {
            $response['redirect'] = route('loans.show', $loan->id);

            $message = trans('messages.success.created', ['type' => trans('loans.payment')]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('loans.show', $loan->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function paymentDestroy(Loan $loan, LoanPayment $payment)
    {
        $response = $this->ajaxDispatch(new DeleteLoanPayment($payment));

        $response['redirect'] = route('loans.show', $loan->id);

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans('loans.payment')]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }
}
