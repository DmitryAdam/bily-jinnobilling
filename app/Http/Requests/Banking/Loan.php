<?php

namespace App\Http\Requests\Banking;

use App\Abstracts\Http\FormRequest;

class Loan extends FormRequest
{
    public function rules()
    {
        return [
            'account_id' => 'required|integer',
            'amount' => 'required|amount',
            'issued_at' => 'required|date_format:Y-m-d',
            'contact_name' => 'required|string|max:255',
            'payment_method' => 'required|string|payment_method',
        ];
    }
}
