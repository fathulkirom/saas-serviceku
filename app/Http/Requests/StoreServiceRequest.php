<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'problem_description' => ['nullable', 'string'],
            'condition_note' => ['nullable', 'string'],
            'checklist_template_id' => ['nullable', 'exists:checklist_templates,id'],
            'checked_items' => ['nullable', 'array'],
            'jalur_kedatangan_id' => ['nullable', 'exists:master_data,id'],
            'kategori_perangkat_id' => ['nullable', 'exists:master_data,id'],
            'merek_id' => ['nullable', 'exists:master_data,id'],
            'tipe_unit' => ['nullable', 'string', 'max:100'],
            'imei_sn' => ['nullable', 'string', 'max:100'],
            'sandi_pola' => ['nullable', 'string', 'max:50'],
            'kelengkapan' => ['nullable', 'array'],
        ];
    }
}
