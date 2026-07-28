<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'category'     => 'required|string',
            'expense_date' => 'required|date',
            'note'         => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Deskripsi pengeluaran wajib diisi.',
            'amount.required'      => 'Jumlah nominal wajib diisi.',
            'amount.min'           => 'Jumlah nominal tidak boleh negatif.',
        ];
    }
}
