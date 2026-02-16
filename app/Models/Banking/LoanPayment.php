<?php

namespace App\Models\Banking;

use App\Abstracts\Model;
use App\Utilities\Modules;

class LoanPayment extends Model
{
    protected $table = 'loan_payments';

    protected $fillable = [
        'company_id',
        'loan_id',
        'transaction_id',
        'account_id',
        'amount',
        'currency_code',
        'currency_rate',
        'paid_at',
        'payment_method',
        'description',
        'reference',
        'created_from',
        'created_by',
    ];

    protected $casts = [
        'amount'  => 'double',
        'paid_at' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo('App\Models\Banking\Loan');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\Banking\Transaction')
                    ->withoutGlobalScope('App\Scopes\Transaction')
                    ->withDefault(['name' => trans('general.na')]);
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Banking\Account');
    }

    public function getPaymentMethodNameAttribute()
    {
        $payment_methods = Modules::getPaymentMethods();

        return $payment_methods[$this->payment_method] ?? $this->payment_method;
    }
}
