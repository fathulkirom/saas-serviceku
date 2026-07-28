<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:100',
            'address'  => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'name.max'      => 'Nama pelanggan maksimal 100 karakter.',
        ];
    }
}
