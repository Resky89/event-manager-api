<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentPayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['sometimes', 'string', 'max:50'],
            'transaction_ref' => ['required', 'string', 'max:100'],
        ];
    }
}
