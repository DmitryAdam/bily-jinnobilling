<?php

namespace App\Models\Banking;

use App\Abstracts\Model;

class Loan extends Model
{
    protected $table = 'loans';

    /**
     * A loan pays money out and takes it back; an investment does the reverse.
     * Everything else about the two is identical.
     */
    public const TYPES = [
        'loan' => [
            'slug'             => 'loans',
            'prefix'           => 'LOAN-',
            'icon'             => 'account_balance_wallet',
            'principal_type'   => Transaction::EXPENSE_TYPE,
            'payment_type'     => Transaction::INCOME_TYPE,
            'category'         => 'loan',
            'payment_category' => 'loan-payment',
        ],
        'investment' => [
            'slug'             => 'investments',
            'prefix'           => 'INVEST-',
            'icon'             => 'savings',
            'principal_type'   => Transaction::INCOME_TYPE,
            'payment_type'     => Transaction::EXPENSE_TYPE,
            'category'         => 'investment',
            'payment_category' => 'investment-payment',
        ],
    ];

    protected $fillable = [
        'company_id',
        'type',
        'loan_number',
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
        'loan_number',
        'issued_at',
        'contact_name',
        'amount',
        'status',
    ];

    public function getConfigAttribute(): array
    {
        return static::TYPES[$this->type] ?? static::TYPES['loan'];
    }

    public function getSlugAttribute(): string
    {
        return $this->config['slug'];
    }

    public static function getNextNumber(string $type = 'loan'): string
    {
        $prefix = (static::TYPES[$type] ?? static::TYPES['loan'])['prefix'];

        // ponytail: reads every number for the type; a max() on a numeric
        // column would scale, but loans are counted in hundreds, not millions
        $highest = static::type($type)->pluck('loan_number')
            ->map(fn ($number) => (int) str_replace($prefix, '', (string) $number))
            ->max();

        return $prefix . str_pad(($highest ?? 0) + 1, 5, '0', STR_PAD_LEFT);
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

    public function refreshStatus(): void
    {
        $paid = $this->payments()->sum('amount');

        $this->update(['status' => match (true) {
            $paid <= 0             => 'active',
            $paid >= $this->amount => 'paid',
            default                => 'partial',
        }]);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
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
        $slug = $this->slug;

        $actions = [];

        $actions[] = [
            'title' => trans('general.show'),
            'icon' => 'visibility',
            'url' => route($slug . '.show', $this->id),
            'permission' => 'read-banking-' . $slug,
            'attributes' => [
                'id' => 'index-line-actions-show-' . $this->type . '-' . $this->id,
            ],
        ];

        $actions[] = [
            'title' => trans('general.edit'),
            'icon' => 'edit',
            'url' => route($slug . '.edit', $this->id),
            'permission' => 'update-banking-' . $slug,
            'attributes' => [
                'id' => 'index-line-actions-edit-' . $this->type . '-' . $this->id,
            ],
        ];

        $actions[] = [
            'type' => 'delete',
            'icon' => 'delete',
            'route' => $slug . '.destroy',
            'permission' => 'delete-banking-' . $slug,
            'attributes' => [
                'id' => 'index-line-actions-delete-' . $this->type . '-' . $this->id,
            ],
            'model' => $this,
        ];

        return $actions;
    }
}
