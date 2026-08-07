<?php

namespace App\Enterprise\Data\Definitions;

use App\Enterprise\Data\DataDefinition;
use App\Enterprise\Data\ColumnDefinition;
use App\Enterprise\Data\FilterDefinition;
use App\Enterprise\Data\BulkAction;

/**
 * ServiceListDefinition — Reference: Service index table.
 * All other list definitions follow this pattern.
 */
class ServiceListDefinition
{
    public static function define(): DataDefinition
    {
        return (new DataDefinition(
            id: 'service.index',
            title: 'Daftar Servis',
            modelClass: \App\Models\Tenant\Service::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            rowKey: 'id',
            views: ['table', 'card'],
        ))
            ->addColumns([
                new ColumnDefinition('tracking_code', 'Kode', type: 'text', sortable: true, bold: true, width: '110px', order: 1),
                new ColumnDefinition('customer_name', 'Pelanggan', type: 'text', sortable: true, searchable: true, bold: true, order: 2),
                new ColumnDefinition('device_type', 'Tipe Unit', type: 'text', sortable: true, searchable: true, order: 3),
                new ColumnDefinition('status', 'Status', type: 'badge', sortable: true, filterable: true, align: 'center', width: '130px',
                    statusMap: 'service_status', order: 4),
                new ColumnDefinition('technician_name', 'Teknisi', type: 'text', sortable: true, order: 5),
                new ColumnDefinition('total_cost', 'Biaya', type: 'currency', sortable: true, align: 'right', width: '120px',
                    permissions: ['manage_finance'], order: 6),
                new ColumnDefinition('created_at', 'Masuk', type: 'datetime', sortable: true, width: '140px', order: 7),
                new ColumnDefinition('actions', '', type: 'actions', align: 'center', width: '80px', pinnable: true, order: 99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type: 'select', quick: true,
                options: [
                    ['value' => 'menunggu_alokasi', 'label' => 'Menunggu Alokasi'],
                    ['value' => 'diterima', 'label' => 'Diterima'],
                    ['value' => 'dikerjakan', 'label' => 'Dikerjakan'],
                    ['value' => 'selesai', 'label' => 'Selesai'],
                    ['value' => 'siap_diambil', 'label' => 'Siap Diambil'],
                    ['value' => 'cancel', 'label' => 'Dibatalkan'],
                ], order: 1))
            ->addFilter(new FilterDefinition('created_at', 'Tanggal Masuk', type: 'date_range', quick: true, order: 2))
            ->addFilter(new FilterDefinition('technician_id', 'Teknisi', type: 'select', order: 3))
            ->addBulkAction(new BulkAction('delete', 'Hapus', variant: 'danger', confirm: true,
                confirmMessage: 'Yakin hapus servis terpilih?', permissions: ['delete_models']))
            ->addBulkAction(new BulkAction('export', 'Export', variant: 'default'))
            ->addBulkAction(new BulkAction('assign', 'Assign Teknisi', variant: 'default',
                permissions: ['assign_technician']))
            ->addBulkAction(new BulkAction('change_status', 'Ubah Status', variant: 'default'));
    }
}

/**
 * CustomerListDefinition — Reference: Customer index table.
 */
class CustomerListDefinition
{
    public static function define(): DataDefinition
    {
        return (new DataDefinition(
            id: 'customer.index',
            title: 'Daftar Pelanggan',
            modelClass: \App\Models\Tenant\Customer::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
        ))
            ->addColumns([
                new ColumnDefinition('customer_code', 'Kode', type: 'text', sortable: true, bold: true, width: '100px', order: 1),
                new ColumnDefinition('name', 'Nama', type: 'text', sortable: true, searchable: true, bold: true, order: 2),
                new ColumnDefinition('phone', 'Telepon', type: 'text', sortable: true, searchable: true, order: 3),
                new ColumnDefinition('email', 'Email', type: 'text', sortable: true, searchable: true, order: 4),
                new ColumnDefinition('is_member', 'Member', type: 'boolean', sortable: true, align: 'center', width: '80px', order: 5),
                new ColumnDefinition('service_count', 'Total Servis', type: 'number', sortable: true, align: 'center', width: '100px', order: 6),
                new ColumnDefinition('created_at', 'Terdaftar', type: 'date', sortable: true, width: '120px', order: 7),
                new ColumnDefinition('actions', '', type: 'actions', align: 'center', width: '80px', order: 99),
            ])
            ->addFilter(new FilterDefinition('is_member', 'Member', type: 'select', quick: true,
                options: [['value' => '1', 'label' => 'Ya'], ['value' => '0', 'label' => 'Tidak']], order: 1))
            ->addFilter(new FilterDefinition('created_at', 'Tanggal Daftar', type: 'date_range', quick: true, order: 2))
            ->addBulkAction(new BulkAction('delete', 'Hapus', variant: 'danger', confirm: true, permissions: ['delete_models']))
            ->addBulkAction(new BulkAction('export', 'Export CSV'));
    }
}
