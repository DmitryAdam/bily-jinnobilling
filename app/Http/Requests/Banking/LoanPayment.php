<?php

namespace App\Http\Requests\Banking;

use App\Abstracts\Http\FormRequest;
use App\Utilities\Date;

class LoanPayment extends FormRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if ($this->has('paid_at')) {
            $this->merge([
                'paid_at' => Date::parse($this->get('paid_at'))->format('Y-m-d'),
            ]);
        }
    }

    public function rules()
    {
        return [
            'loan_id' => 'required|integer',
            'account_id' => 'required|integer',
            'amount' => 'required|amount',
            'paid_at' => 'required|date_format:Y-m-d',
            'payment_method' => 'required|string|payment_method',
        ];
    }
}
