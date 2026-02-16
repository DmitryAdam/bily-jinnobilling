<?php

namespace App\Http\Requests\Banking;

use App\Abstracts\Http\FormRequest;
use App\Utilities\Date;

class Loan extends FormRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if ($this->has('issued_at')) {
            $this->merge([
                'issued_at' => Date::parse($this->get('issued_at'))->format('Y-m-d'),
            ]);
        }
    }

    public function rules()
    {
        // On update (PUT/PATCH), only validate editable fields
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return [
                'issued_at' => 'required|date_format:Y-m-d',
                'contact_name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ];
        }

        return [
            'account_id' => 'required|integer',
            'amount' => 'required|amount',
            'issued_at' => 'required|date_format:Y-m-d',
            'contact_name' => 'required|string|max:255',
            'payment_method' => 'required|string|payment_method',
        ];
    }
}
