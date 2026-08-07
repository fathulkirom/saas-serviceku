<?php

namespace App\Enterprise\Form\Definitions;

use App\Enterprise\Form\FormDefinition;
use App\Enterprise\Form\FormField;
use App\Enterprise\Form\FormSection;
use App\Enterprise\Form\FormAction;

/**
 * ServiceCreateForm — Example: Service creation form using the Form Engine.
 * All other module forms follow the same pattern.
 */
class ServiceCreateForm
{
    public static function define(): FormDefinition
    {
        return (new FormDefinition(
            id: 'service.create',
            title: 'Buat Servis Baru',
            method: 'POST',
            endpoint: '/services',
            layout: 'default',
            features: ['services'],
            denyBusinessTypes: ['retail_only'],
        ))
            ->addSection(new FormSection(
                id: 'customer',
                label: 'Informasi Pelanggan',
                icon: '👤',
                cols: 2,
            ))
            ->addSection(new FormSection(
                id: 'device',
                label: 'Informasi Perangkat',
                icon: '📱',
                cols: 2,
            ))
            ->addSection(new FormSection(
                id: 'service',
                label: 'Detail Servis',
                icon: '🔧',
                cols: 2,
                collapsible: true,
                collapsed: false,
            ))
            ->addFields([
                // Customer section
                new FormField('customer_id', type: 'autocomplete', label: 'Pelanggan',
                    required: true, asyncUrl: '/api/customers/search',
                    optionLabel: 'name', optionValue: 'id',
                    section: 'customer', cols: 12, order: 1),

                // Device section
                new FormField('device_type', type: 'text', label: 'Tipe Unit',
                    required: true, placeholder: 'Contoh: iPhone 15 Pro Max',
                    section: 'device', cols: 6, order: 2),
                new FormField('imei_sn', type: 'text', label: 'IMEI / Serial Number',
                    section: 'device', cols: 6, order: 3),
                new FormField('kategori_perangkat_id', type: 'select', label: 'Kategori',
                    section: 'device', cols: 6, order: 4),
                new FormField('merek_id', type: 'select', label: 'Merek',
                    section: 'device', cols: 6, order: 5),
                new FormField('warna', type: 'text', label: 'Warna',
                    section: 'device', cols: 4, order: 6),
                new FormField('kelengkapan', type: 'textarea', label: 'Kelengkapan',
                    section: 'device', cols: 8, order: 7),

                // Service section
                new FormField('problem_description', type: 'textarea', label: 'Keluhan Kerusakan',
                    required: true, section: 'service', cols: 12, order: 8),
                new FormField('condition_note', type: 'textarea', label: 'Kondisi Unit',
                    section: 'service', cols: 12, order: 9),
                new FormField('service_charge', type: 'currency', label: 'Biaya Servis',
                    section: 'service', cols: 6, order: 10,
                    permissions: ['manage_finance']),
                new FormField('jalur_kedatangan_id', type: 'select', label: 'Jalur Kedatangan',
                    section: 'service', cols: 6, order: 11),
                new FormField('technician_id', type: 'select', label: 'Teknisi',
                    section: 'service', cols: 6, order: 12,
                    roles: ['owner', 'admin', 'cs']),
                new FormField('warranty_days', type: 'number', label: 'Garansi (hari)',
                    default: 30, min: 0, max: 365,
                    section: 'service', cols: 3, order: 13),
                new FormField('photos', type: 'gallery', label: 'Foto Unit',
                    accept: 'image/*', multiple: true, maxSize: 10,
                    section: 'service', cols: 12, order: 14),
            ])
            ->addAction(new FormAction('save', 'Simpan', variant: 'primary', shortcut: 'Ctrl+S', order: 1))
            ->addAction(new FormAction('save_and_new', 'Simpan & Baru', variant: 'secondary', shortcut: 'Ctrl+Shift+S', order: 2))
            ->addAction(new FormAction('save_draft', 'Draft', variant: 'outline', order: 3));
    }
}
