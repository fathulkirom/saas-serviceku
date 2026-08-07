<?php

namespace App\Enterprise\Reporting\Definitions;

use App\Enterprise\Reporting\DimensionDefinition;
use App\Enterprise\Reporting\MetricDefinition;
use App\Enterprise\Reporting\ReportDefinition;
use App\Enterprise\Reporting\ReportFilter;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;

/**
 * Reference Report Definitions for ServiceKU.
 * Each module should define its own reports.
 */
class ServiceReports
{
    /** Daily service summary */
    public static function serviceDaily(): ReportDefinition
    {
        return (new ReportDefinition(
            id: 'service.daily',
            title: 'Ringkasan Servis Harian',
            description: 'Jumlah servis masuk, selesai, dan pendapatan per hari.',
            type: 'summary',
            modelClass: Service::class,
            chartType: 'bar',
            features: ['services'],
        ))
            ->addMetrics([
                new MetricDefinition('total', 'Total Servis', 'count', 'id', format: 'number', order: 1),
                new MetricDefinition('revenue', 'Pendapatan', 'sum', 'total_cost', format: 'currency', color: 'success', order: 2),
            ])
            ->addDimension(new DimensionDefinition('date', 'Tanggal', 'created_at', type: 'date'))
            ->addFilter(new ReportFilter('date_range', 'Tanggal', 'date_range', field: 'created_at', order: 1))
            ->addFilter(new ReportFilter('status', 'Status', 'select', field: 'status', options: [
                ['value' => 'selesai', 'label' => 'Selesai'],
                ['value' => 'dikerjakan', 'label' => 'Dikerjakan'],
                ['value' => 'cancel', 'label' => 'Dibatalkan'],
            ], order: 2));
    }

    /** Service status distribution */
    public static function serviceStatus(): ReportDefinition
    {
        return (new ReportDefinition(
            id: 'service.status',
            title: 'Status Servis',
            description: 'Distribusi servis berdasarkan status.',
            type: 'summary',
            modelClass: Service::class,
            chartType: 'pie',
            features: ['services'],
        ))
            ->addMetric(new MetricDefinition('count', 'Jumlah', 'count', 'id', format: 'number'))
            ->addDimension(new DimensionDefinition('status', 'Status', 'status', type: 'status'));
    }

    /** Sales daily */
    public static function salesDaily(): ReportDefinition
    {
        return (new ReportDefinition(
            id: 'sales.daily',
            title: 'Penjualan Harian',
            description: 'Total penjualan per hari.',
            type: 'summary',
            modelClass: Sale::class,
            chartType: 'line',
            features: ['sales'],
            permissions: ['manage_sales'],
        ))
            ->addMetrics([
                new MetricDefinition('total', 'Total Penjualan', 'sum', 'total', format: 'currency', color: 'success'),
                new MetricDefinition('count', 'Jumlah Transaksi', 'count', 'id', format: 'number'),
            ])
            ->addDimension(new DimensionDefinition('date', 'Tanggal', 'created_at', type: 'date'))
            ->addFilter(new ReportFilter('date_range', 'Tanggal', 'date_range', field: 'created_at'));
    }

    /** Inventory low stock */
    public static function inventoryLowStock(): ReportDefinition
    {
        return (new ReportDefinition(
            id: 'inventory.low_stock',
            title: 'Stok Menipis',
            description: 'Produk dengan stok di bawah minimum.',
            type: 'summary',
            modelClass: Product::class,
            chartType: 'table',
            features: ['products'],
            permissions: ['manage_products'],
        ))
            ->addMetrics([
                new MetricDefinition('stock', 'Stok', 'sum', 'stock_quantity', format: 'number'),
                new MetricDefinition('value', 'Nilai', 'sum', 'cost_price', format: 'currency'),
            ])
            ->addDimension(new DimensionDefinition('name', 'Produk', 'name'));
    }

    /** Finance P&L summary */
    public static function financeProfitLoss(): ReportDefinition
    {
        return (new ReportDefinition(
            id: 'finance.pl',
            title: 'Laba Rugi Ringkas',
            description: 'Ringkasan pendapatan dan pengeluaran.',
            type: 'summary',
            chartType: 'kpi',
            features: ['sales'],
            permissions: ['manage_finance'],
        ))
            ->addMetrics([
                new MetricDefinition('revenue', 'Pendapatan', 'sum', 'total', format: 'currency', color: 'success', icon: '💰'),
                new MetricDefinition('expenses', 'Pengeluaran', 'sum', 'amount', format: 'currency', color: 'danger', icon: '📤'),
                new MetricDefinition('profit', 'Laba', 'sum', 'total', format: 'currency', color: 'primary', icon: '📈', trend: true),
            ]);
    }

    /** @return ReportDefinition[] */
    public static function all(): array
    {
        return [
            self::serviceDaily(),
            self::serviceStatus(),
            self::salesDaily(),
            self::inventoryLowStock(),
            self::financeProfitLoss(),
        ];
    }
}
