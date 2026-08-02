<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Product;
use App\Models\Tenant\WorkOrder;
use App\Models\Tenant\Device;
use Illuminate\Http\Request;

/**
 * Universal Search Controller — Sprint 7.5A.
 * Single endpoint for CTRL+K global search.
 */
class UniversalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) return response()->json(['results' => []]);

        $results = collect();

        // Customers
        Customer::where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")->orWhere('customer_code', 'like', "%{$q}%")->limit(5)->get()
            ->each(fn($c) => $results->push(['type' => 'customer', 'icon' => '👤', 'label' => $c->name, 'description' => "{$c->phone} · {$c->customer_code}", 'url' => route('customers.show', $c->id)]));

        // Services
        Service::where('id', 'like', "%{$q}%")->orWhere('tracking_code', 'like', "%{$q}%")->with('customer')->limit(5)->get()
            ->each(fn($s) => $results->push(['type' => 'service', 'icon' => '🔧', 'label' => "Service #{$s->id}", 'description' => $s->customer?->name . ' · ' . $s->status, 'url' => route('services.show', $s->id)]));

        // Devices (IMEI/Serial)
        Device::where('imei', 'like', "%{$q}%")->orWhere('serial_number', 'like', "%{$q}%")->with('customer')->limit(5)->get()
            ->each(fn($d) => $results->push(['type' => 'device', 'icon' => '💻', 'label' => "{$d->brand} {$d->model}", 'description' => "IMEI: {$d->imei} · {$d->customer?->name}", 'url' => route('customers.show', $d->customer_id)]));

        // Products
        Product::where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")->orWhere('barcode', 'like', "%{$q}%")->limit(5)->get()
            ->each(fn($p) => $results->push(['type' => 'product', 'icon' => '📦', 'label' => $p->name, 'description' => "SKU: {$p->sku} · Stock: {$p->stock_quantity}", 'url' => route('products.index')]));

        // Sales
        Sale::where('id', 'like', "%{$q}%")->orWhere('invoice_number', 'like', "%{$q}%")->with('customer')->limit(5)->get()
            ->each(fn($s) => $results->push(['type' => 'sale', 'icon' => '🛒', 'label' => "Invoice #{$s->invoice_number}", 'description' => $s->customer?->name . ' · Rp ' . number_format($s->total), 'url' => '#']));

        // WorkOrders
        WorkOrder::where('title', 'like', "%{$q}%")->with('service.customer')->limit(5)->get()
            ->each(fn($w) => $results->push(['type' => 'workorder', 'icon' => '🔩', 'label' => $w->title, 'description' => $w->service?->customer?->name . ' · ' . $w->status, 'url' => '#']));

        return response()->json(['results' => $results->take(15)->values()->toArray()]);
    }
}
