<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPost = $this->isMethod('post');

        $endTimeRules = $isPost ? ['required', 'date', 'after:start_time'] : ['sometimes', 'date'];
        if (!$isPost && $this->has('start_time')) {
            $endTimeRules[] = 'after:start_time';
        }

        return [
            'organizer_id' => ['sometimes', 'exists:users,id'],
            'title' => $isPost ? ['required', 'string', 'max:200'] : ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_time' => $isPost ? ['required', 'date'] : ['sometimes', 'date'],
            'end_time' => $endTimeRules,
        ];
    }
}
