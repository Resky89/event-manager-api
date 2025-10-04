<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPost = $this->isMethod('post');

        return [
            // event_id only on create; not allowed on update to match API contract
            'event_id' => $isPost ? ['required', 'exists:events,id'] : ['prohibited'],
            'type' => $isPost ? ['required', 'string', 'max:100'] : ['sometimes', 'string', 'max:100'],
            'price' => $isPost ? ['required', 'numeric', 'min:0'] : ['sometimes', 'numeric', 'min:0'],
            'quantity' => $isPost ? ['required', 'integer', 'min:0'] : ['sometimes', 'integer', 'min:0'],
        ];
    }
}
