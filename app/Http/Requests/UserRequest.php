<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        $isPost = $this->isMethod('post');

        return [
            'name' => $isPost ? ['required', 'string', 'max:100'] : ['sometimes', 'string', 'max:100'],
            'email' => $isPost ? ['required', 'email', 'max:150', 'unique:users,email,' . ($id ?? 'NULL')] : ['sometimes', 'email', 'max:150', 'unique:users,email,' . ($id ?? 'NULL')],
            'password' => $isPost ? ['required', 'string', 'min:6'] : ['sometimes', 'string', 'min:6'],
            'role' => $isPost ? ['required', 'in:admin,organizer,user'] : ['sometimes', 'in:admin,organizer,user'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
