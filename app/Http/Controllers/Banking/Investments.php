<?php

namespace App\Http\Controllers\Banking;

use App\Abstracts\Http\Controller;
use App\Http\Requests\Banking\Investment as Request;
use App\Http\Requests\Banking\InvestmentPayment as InvestmentPaymentRequest;
use App\Jobs\Banking\CreateInvestment;
use App\Jobs\Banking\UpdateInvestment;
use App\Jobs\Banking\DeleteInvestment;
use App\Jobs\Banking\CreateInvestmentPayment;
use App\Jobs\Banking\DeleteInvestmentPayment;
use App\Models\Banking\Account;
use App\Models\Banking\Investment;
use App\Models\Banking\InvestmentPayment;
use App\Models\Setting\Currency;

class Investments extends Controller
{
    public function index()
    {
        $investments = Investment::with('account', 'payments')->collect(['issued_at' => 'desc']);

        $totalInvestasi = Investment::sum('amount');
        $totalPaid = InvestmentPayment::sum('amount');
        $totalUnpaid = $totalInvestasi - $totalPaid;

        $currency = default_currency();

        return $this->response('banking.investments.index', compact('investments', 'totalInvestasi', 'totalPaid', 'totalUnpaid', 'currency'));
    }

    public function create()
    {
        $accounts = Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');

        $currency = Currency::where('code', default_currency())->first();

        return view('banking.investments.create', compact('accounts', 'currency'));
    }

    public function store(Request $request)
    {
        $response = $this->ajaxDispatch(new CreateInvestment($request));

        if ($response['success']) {
            $response['redirect'] = route('investments.show', $response['data']->id);

            $message = trans('messages.success.created', ['type' => trans_choice('general.investments', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('investments.create');

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function show(Investment $investment)
    {
        $investment->load('account', 'payments.account', 'payments.transaction');

        $accounts = Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');

        $currency = Currency::where('code', $investment->currency_code)->first();

        return view('banking.investments.show', compact('investment', 'accounts', 'currency'));
    }

    public function edit(Investment $investment)
    {
        $investment->load('account');

        $accounts = Account::enabled()->orderBy('name')->with('currency')->get()->pluck('title', 'id');

        $currency = Currency::where('code', $investment->currency_code)->first();

        return view('banking.investments.edit', compact('investment', 'accounts', 'currency'));
    }

    public function update(Request $request, Investment $investment)
    {
        $response = $this->ajaxDispatch(new UpdateInvestment($investment, $request));

        if ($response['success']) {
            $response['redirect'] = route('investments.show', $investment->id);

            $message = trans('messages.success.updated', ['type' => trans_choice('general.investments', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('investments.edit', $investment->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function destroy(Investment $investment)
    {
        $response = $this->ajaxDispatch(new DeleteInvestment($investment));

        $response['redirect'] = route('investments.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans_choice('general.investments', 1)]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function paymentStore(InvestmentPaymentRequest $request, Investment $investment)
    {
        $request->merge(['investment_id' => $investment->id]);

        $response = $this->ajaxDispatch(new CreateInvestmentPayment($request));

        if ($response['success']) {
            $response['redirect'] = route('investments.show', $investment->id);

            $message = trans('messages.success.created', ['type' => trans('investments.payment')]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('investments.show', $investment->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    public function paymentDestroy(Investment $investment, InvestmentPayment $payment)
    {
        $response = $this->ajaxDispatch(new DeleteInvestmentPayment($payment));

        $response['redirect'] = route('investments.show', $investment->id);

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => trans('investments.payment')]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }
}
