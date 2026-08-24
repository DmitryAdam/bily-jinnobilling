<?php

namespace App\Models\Banking;

use App\Abstracts\Model;

class Investment extends Model
{
    protected $table = 'investments';

    protected $fillable = [
        'company_id',
        'investment_number',
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
        'investment_number',
        'issued_at',
        'contact_name',
        'amount',
        'status',
    ];

    public static function getNextInvestmentNumber($company_id = null): string
    {
        $company_id = $company_id ?: company_id();

        $last = \DB::table('investments')
            ->where('company_id', $company_id)
            ->orderBy('investment_number', 'desc')
            ->value('investment_number');

        if ($last) {
            $number = (int) str_replace('INVEST-', '', $last) + 1;
        } else {
            $number = 1;
        }

        return 'INVEST-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

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
        return $this->hasMany('App\Models\Banking\InvestmentPayment');
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
            'url' => route('investments.show', $this->id),
            'permission' => 'read-banking-investments',
            'attributes' => [
                'id' => 'index-line-actions-show-investment-' . $this->id,
            ],
        ];

        $actions[] = [
            'title' => trans('general.edit'),
            'icon' => 'edit',
            'url' => route('investments.edit', $this->id),
            'permission' => 'update-banking-investments',
            'attributes' => [
                'id' => 'index-line-actions-edit-investment-' . $this->id,
            ],
        ];

        $actions[] = [
            'type' => 'delete',
            'icon' => 'delete',
            'route' => 'investments.destroy',
            'permission' => 'delete-banking-investments',
            'attributes' => [
                'id' => 'index-line-actions-delete-investment-' . $this->id,
            ],
            'model' => $this,
        ];

        return $actions;
    }
}
