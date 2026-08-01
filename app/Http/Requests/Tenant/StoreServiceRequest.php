<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_id'           => 'required|exists:customers,id',
            'problem_description'   => 'required|string|min:5',
            'condition_note'        => 'nullable|string',
            'checklist_template_id' => 'nullable|exists:checklist_templates,id',
            'checked_items'         => 'nullable|array',
            'jalur_kedatangan_id'   => 'nullable|exists:master_data,id',
            'kategori_perangkat_id' => 'nullable|exists:master_data,id',
            'merek_id'              => 'nullable|exists:master_data,id',
            'tipe_unit'             => 'required|string|max:100',
            'imei_sn'               => 'nullable|string|max:100',
            'sandi_pola'            => 'nullable|string|max:50',
            'kelengkapan'           => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'customer_id.exists'   => 'Data pelanggan tidak valid.',
            'problem_description.required' => 'Keluhan/masalah wajib diisi.',
            'problem_description.min' => 'Keluhan/masalah minimal 5 karakter.',
            'tipe_unit.required' => 'Tipe unit wajib diisi.',
        ];
    }
}
