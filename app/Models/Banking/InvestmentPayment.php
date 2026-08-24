<?php

namespace App\Models\Banking;

use App\Abstracts\Model;
use App\Utilities\Modules;

class InvestmentPayment extends Model
{
    protected $table = 'investment_payments';

    protected $fillable = [
        'company_id',
        'investment_id',
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

    public function investment()
    {
        return $this->belongsTo('App\Models\Banking\Investment');
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
