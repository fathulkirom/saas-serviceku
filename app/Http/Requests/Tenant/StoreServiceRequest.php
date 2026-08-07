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
            'prioritas'             => 'nullable|in:normal,cepat,express',
            'estimasi_selesai'      => 'nullable|date|after:today',
            'photos'                => 'nullable|array|max:10',
            'photos.*'              => 'image|mimes:jpg,jpeg,png,webp|max:10240',
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
            'prioritas.in' => 'Prioritas harus salah satu: Normal, Cepat, atau Express.',
            'estimasi_selesai.after' => 'Estimasi selesai harus setelah hari ini.',
            'photos.max' => 'Maksimal 10 foto.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Foto harus dalam format JPG, PNG, atau WebP.',
            'photos.*.max' => 'Ukuran foto maksimal 10MB per file.',
        ];
    }
}
