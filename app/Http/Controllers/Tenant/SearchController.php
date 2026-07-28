<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // Search services
        $services = Service::with('customer')
            ->where(function ($q) use ($query) {
                $cleanId = preg_replace('/[^0-9]/', '', $query);
                if ($cleanId) {
                    $q->where('id', 'like', "%{$cleanId}%");
                }
                $q->orWhereHas('customer', function ($c) use ($query) {
                    $c->where('name', 'like', "%{$query}%");
                })->orWhere('tipe_unit', 'like', "%{$query}%");
            })
            ->take(5)->get()
            ->map(fn($s) => [
                'type' => 'service',
                'icon' => '🔧',
                'label' => "Servis #{$s->id} — " . ($s->customer?->name ?? 'Tanpa Pelanggan'),
                'description' => "Unit: " . ($s->tipe_unit ?? '-'),
                'url' => route('services.show', $s->id),
            ]);

        // Search customers
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->take(5)->get()
            ->map(fn($c) => [
                'type' => 'customer',
                'icon' => '👤',
                'label' => $c->name,
                'description' => "Telp: " . ($c->phone ?? '-'),
                'url' => route('customers.show', $c->id),
            ]);

        // Search products
        $products = Product::where('name', 'like', "%{$query}%")
            ->take(5)->get()
            ->map(fn($p) => [
                'type' => 'product',
                'icon' => '📦',
                'label' => $p->name,
                'description' => "Stok: {$p->stock_quantity} | Rp " . number_format($p->selling_price, 0, ',', '.'),
                'url' => route('products.show', $p->id),
            ]);

        return response()->json([
            'results' => $services->concat($customers)->concat($products)->values(),
        ]);
    }
}
