<?php

namespace App\Http\Requests\Banking;

use App\Abstracts\Http\FormRequest;

class LoanPayment extends FormRequest
{
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
