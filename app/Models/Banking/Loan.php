<?php

namespace App\Models\Banking;

use App\Abstracts\Model;

class Loan extends Model
{
    protected $table = 'loans';

    protected $fillable = [
        'company_id',
        'account_id',
        'transaction_id',
        'amount',
        'currency_code',
        'currency_rate',
        'contact_name',
        'description',
        'issued_at',
        'payment_method',
        'reference',
        'status',
        'created_from',
        'created_by',
    ];

    protected $casts = [
        'amount'    => 'double',
        'issued_at' => 'datetime',
    ];

    protected $appends = [
        'paid_amount',
        'remaining_amount',
    ];

    public $sortable = [
        'issued_at',
        'contact_name',
        'amount',
        'status',
    ];

    public function account()
    {
        return $this->belongsTo('App\Models\Banking\Account');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\Banking\Transaction')
                    ->withoutGlobalScope('App\Scopes\Transaction')
                    ->withDefault(['name' => trans('general.na')]);
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Banking\LoanPayment');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'paid');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getLineActionsAttribute()
    {
        $actions = [];

        $actions[] = [
            'title' => trans('general.show'),
            'icon' => 'visibility',
            'url' => route('loans.show', $this->id),
            'permission' => 'read-banking-loans',
            'attributes' => [
                'id' => 'index-line-actions-show-loan-' . $this->id,
            ],
        ];

        $actions[] = [
            'type' => 'delete',
            'icon' => 'delete',
            'route' => 'loans.destroy',
            'permission' => 'delete-banking-loans',
            'attributes' => [
                'id' => 'index-line-actions-delete-loan-' . $this->id,
            ],
            'model' => $this,
        ];

        return $actions;
    }
}
