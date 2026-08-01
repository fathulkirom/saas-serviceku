<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoicePdf;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Models\Tenant\Product;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Commission;
use App\Models\Tenant\Service;
use App\Models\Tenant\Indent;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\SystemAlert;
use App\Models\Tenant\RequestIdempotency;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleStoreController extends Controller
{
    public function store(Request $request)
    {
        $isDraft = $request->boolean('as_draft', false);
        $idempotencyKey = $this->extractIdempotencyKey($request);

        $rules = [
            'customer_id' => 'nullable|exists:customers,id',
            'sale_type' => 'required|in:servis,langsung,inden',
            'service_id' => 'nullable|exists:services,id',
            'indent_id' => 'nullable|exists:indents,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.item_type' => 'required|in:sparepart,jasa,aksesoris',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
        ];
        if (!$isDraft) {
            $rules['payment_method_id'] = 'nullable|exists:master_data,id';
            $rules['payment_method'] = 'nullable|string';
            $rules['paid_amount'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);
        $user = Auth::user();

        if (!empty($idempotencyKey)) {
            $existing = RequestIdempotency::query()
                ->where('key', $idempotencyKey)
                ->where('action', 'sale.store')
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return $this->replayIdempotentSaleResponse($existing);
            }
        }

        $this->validateSaleReferencePayload($validated);

        $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['price']);
        $discount = $validated['discount'] ?? 0;
        $total = $subtotal - $discount;

        if ($isDraft) {
            try {
                $sale = DB::transaction(function () use ($validated, $user, $subtotal, $discount, $total, $idempotencyKey) {
                    $this->validateAndLockSaleReferences($validated, $user->branch_id);

                    $draftSale = Sale::create([
                        'branch_id' => $user->branch_id,
                        'customer_id' => $validated['customer_id'] ?? null,
                        'sale_type' => $validated['sale_type'],
                        'status' => Sale::STATUS_DRAFT,
                        'service_id' => $validated['service_id'] ?? null,
                        'indent_id' => $validated['indent_id'] ?? null,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'total' => $total,
                        'payment_method' => 'draft',
                        'paid_amount' => 0,
                        'change' => 0,
                    ]);

                    foreach ($validated['items'] as $item) {
                        SaleItem::create([
                            'sale_id' => $draftSale->id,
                            'product_id' => $item['product_id'] ?? null,
                            'item_type' => $item['item_type'],
                            'description' => $item['description'] ?? '',
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'subtotal' => $item['quantity'] * $item['price'],
                        ]);
                    }

                    if (!empty($idempotencyKey)) {
                        RequestIdempotency::create([
                            'key' => $idempotencyKey,
                            'action' => 'sale.store',
                            'user_id' => $user->id,
                            'resource_type' => 'sale',
                            'resource_id' => (string) $draftSale->id,
                        ]);
                    }

                    return $draftSale;
                });
            } catch (QueryException $e) {
                if (!empty($idempotencyKey)) {
                    $existing = RequestIdempotency::query()
                        ->where('key', $idempotencyKey)
                        ->where('action', 'sale.store')
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existing) {
                        return $this->replayIdempotentSaleResponse($existing);
                    }
                }

                throw $e;
            }

            ActivityLog::log('sale_draft', 'Membuat draft penjualan #' . $sale->id, $sale);
            return redirect()->route('sales.create')->with('success', 'Draft penjualan berhasil disimpan.');
        }

        $paidAmount = $validated['paid_amount'];
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        if (!empty($validated['payment_method_id'])) {
            $method = MasterData::find($validated['payment_method_id']);
            $paymentMethod = $method ? $method->name : $paymentMethod;
        }

        try {
            $sale = DB::transaction(function () use ($validated, $user, $subtotal, $discount, $total, $paymentMethod, $paidAmount, $idempotencyKey) {
                $this->validateAndLockSaleReferences($validated, $user->branch_id);

                $createdSale = Sale::create([
                    'branch_id' => $user->branch_id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'sale_type' => $validated['sale_type'],
                    'status' => Sale::STATUS_PAID,
                    'service_id' => $validated['service_id'] ?? null,
                    'indent_id' => $validated['indent_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'payment_method_id' => $validated['payment_method_id'] ?? null,
                    'paid_amount' => $paidAmount,
                    'change' => max(0, $paidAmount - $total),
                ]);

                foreach ($validated['items'] as $item) {
                    SaleItem::create([
                        'sale_id' => $createdSale->id,
                        'product_id' => $item['product_id'] ?? null,
                        'item_type' => $item['item_type'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['quantity'] * $item['price'],
                    ]);

                    if (!$this->saleItemAffectsStock($createdSale, $item)) {
                        continue;
                    }

                    $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                    if ((int) $product->branch_id !== (int) $user->branch_id) {
                        throw ValidationException::withMessages([
                            'items' => 'Produk ' . $product->name . ' bukan milik cabang aktif.',
                        ]);
                    }

                    if ((int) $product->stock_quantity < (int) $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => 'Stok ' . $product->name . ' tidak mencukupi. Sisa: ' . $product->stock_quantity,
                        ]);
                    }

                    $product->reduceStock((int) $item['quantity']);
                    InventoryMutation::create([
                        'branch_id' => $user->branch_id,
                        'product_id' => $product->id,
                        'type' => 'keluar',
                        'quantity' => $item['quantity'],
                        'reference_type' => 'sale',
                        'reference_id' => (string) $createdSale->id,
                        'note' => 'Penjualan #' . $createdSale->id,
                        'created_by' => $user->id,
                    ]);

                    if ($product->isLowStock()) {
                        SystemAlert::createAlert('low_stock', "Stok {$product->name} menipis", "Sisa stok: {$product->stock_quantity}", 'warning', ['product_id' => $product->id]);
                    }
                }

                if (!empty($validated['service_id'])) {
                    $paidService = Service::query()->lockForUpdate()->find($validated['service_id']);
                    if ($paidService) {
                        $paidService->update([
                            'status' => Service::STATUS_SELESAI,
                            'payment_status' => 'paid',
                            'warranty_days' => $validated['warranty_days'] ?? 0,
                            'warranty_expired_at' => ($validated['warranty_days'] ?? 0) > 0 ? now()->addDays($validated['warranty_days']) : null,
                        ]);
                        Commission::autoCreateForService($paidService);
                    }
                }

                if (!empty($validated['indent_id'])) {
                    $indent = Indent::query()->lockForUpdate()->find($validated['indent_id']);
                    if ($indent) {
                        $indent->update(['status' => 'selesai']);
                    }
                }

                if (!empty($idempotencyKey)) {
                    RequestIdempotency::create([
                        'key' => $idempotencyKey,
                        'action' => 'sale.store',
                        'user_id' => $user->id,
                        'resource_type' => 'sale',
                        'resource_id' => (string) $createdSale->id,
                    ]);
                }

                return $createdSale;
            });
        } catch (QueryException $e) {
            if (!empty($idempotencyKey)) {
                $existing = RequestIdempotency::query()
                    ->where('key', $idempotencyKey)
                    ->where('action', 'sale.store')
                    ->where('user_id', $user->id)
                    ->first();

                if ($existing) {
                    return $this->replayIdempotentSaleResponse($existing);
                }
            }

            throw $e;
        }

        ActivityLog::log('sale_paid', 'Pembayaran #' . $sale->id . ' - Rp ' . number_format($total, 0, ',', '.'), $sale);
        GenerateInvoicePdf::dispatch($sale);
        return redirect()->route('sales.show', $sale->id)->with('success', 'Pembayaran berhasil dicatat.');
    }

    private function saleItemAffectsStock(Sale $sale, array $item): bool
    {
        if (empty($item['product_id'])) {
            return false;
        }

        if (!in_array($item['item_type'], ['sparepart', 'aksesoris'], true)) {
            return false;
        }

        // Service-linked sale: stok sparepart sudah diproses saat service complete.
        if (!empty($sale->service_id)) {
            return false;
        }

        return true;
    }

    public function draftFromService(Service $service)
    {
        $user = Auth::user();

        if ($user->branch_id && (string) $service->branch_id !== (string) $user->branch_id) {
            return back()->with('error', 'Servis tidak berada pada cabang aktif Anda.');
        }

        if (!in_array($service->status, [Service::STATUS_SELESAI, Service::STATUS_SIAP_DIAMBIL])) {
            return back()->with('error', 'Servis belum berstatus selesai.');
        }
        if ($service->sale()->exists()) {
            return redirect()->route('sales.create')->with('info', 'Sudah ada draft penjualan untuk servis ini.');
        }

        $user = Auth::user();
        $spareparts = $service->spareparts()->with('product')->get();
        $subtotal = $service->service_charge;
        $items = [];

        if ($service->service_charge > 0) {
            $items[] = ['item_type' => 'jasa', 'description' => 'Biaya Jasa Servis', 'quantity' => 1, 'price' => $service->service_charge, 'subtotal' => $service->service_charge];
        }
        foreach ($spareparts as $sp) {
            $items[] = ['product_id' => $sp->product_id, 'item_type' => 'sparepart', 'description' => $sp->product?->name ?? 'Sparepart', 'quantity' => $sp->quantity, 'price' => $sp->unit_price, 'subtotal' => $sp->subtotal];
            $subtotal += $sp->subtotal;
        }
        if (empty($items)) {
            $items[] = ['item_type' => 'jasa', 'description' => 'Biaya Servis', 'quantity' => 1, 'price' => 0, 'subtotal' => 0];
        }

        $sale = Sale::create([
            'branch_id' => $user->branch_id, 'customer_id' => $service->customer_id,
            'sale_type' => Sale::SALE_TYPE_SERVIS, 'status' => Sale::STATUS_DRAFT,
            'service_id' => $service->id, 'subtotal' => $subtotal, 'discount' => 0, 'total' => $subtotal,
            'payment_method' => 'draft', 'paid_amount' => 0, 'change' => 0,
        ]);
        foreach ($items as $item) {
            SaleItem::create(array_merge(['sale_id' => $sale->id], $item));
        }

        ActivityLog::log('sale_draft_from_service', 'Draft penjualan dari servis #' . $service->id, $sale);
        return redirect()->route('sales.create')->with('success', 'Draft penjualan berhasil dibuat dari servis.');
    }

    public function updateItems(Request $request, Sale $sale)
    {
        $user = Auth::user();

        if ($user->branch_id && (string) $sale->branch_id !== (string) $user->branch_id) {
            return back()->with('error', 'Penjualan tidak berada pada cabang aktif Anda.');
        }

        if ($sale->isPaid()) return back()->with('error', 'Tidak bisa mengubah item penjualan yang sudah lunas.');

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:sale_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $existingIds = $sale->items->pluck('id')->toArray();
        $keepIds = [];

        foreach ($validated['items'] as $itemData) {
            if (!empty($itemData['id'])) {
                SaleItem::where('id', $itemData['id'])->update(['quantity' => $itemData['quantity'], 'price' => $itemData['price']]);
                $keepIds[] = $itemData['id'];
            } else {
                $newItem = SaleItem::create(['sale_id' => $sale->id, 'product_id' => $itemData['product_id'], 'quantity' => $itemData['quantity'], 'price' => $itemData['price']]);
                $keepIds[] = $newItem->id;
            }
        }

        SaleItem::whereIn('id', array_diff($existingIds, $keepIds))->delete();
        $subtotal = $sale->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity * price'));
        $sale->update(['subtotal' => $subtotal, 'total' => $subtotal - $sale->discount]);
        ActivityLog::log('sale', 'Update item penjualan #' . $sale->id);

        return back()->with('success', 'Item penjualan berhasil diupdate.');
    }

    private function validateSaleReferencePayload(array $validated): void
    {
        $saleType = $validated['sale_type'];
        $serviceId = $validated['service_id'] ?? null;
        $indentId = $validated['indent_id'] ?? null;

        if ($saleType === Sale::SALE_TYPE_SERVIS && empty($serviceId)) {
            throw ValidationException::withMessages([
                'service_id' => 'Sale tipe servis wajib memiliki service_id.',
            ]);
        }

        if ($saleType === Sale::SALE_TYPE_INDEN && empty($indentId)) {
            throw ValidationException::withMessages([
                'indent_id' => 'Sale tipe inden wajib memiliki indent_id.',
            ]);
        }

        if ($saleType !== Sale::SALE_TYPE_SERVIS && !empty($serviceId)) {
            throw ValidationException::withMessages([
                'service_id' => 'service_id hanya boleh diisi untuk sale tipe servis.',
            ]);
        }

        if ($saleType !== Sale::SALE_TYPE_INDEN && !empty($indentId)) {
            throw ValidationException::withMessages([
                'indent_id' => 'indent_id hanya boleh diisi untuk sale tipe inden.',
            ]);
        }
    }

    private function validateAndLockSaleReferences(array $validated, int $branchId): void
    {
        $serviceId = $validated['service_id'] ?? null;
        $indentId = $validated['indent_id'] ?? null;

        if (!empty($serviceId)) {
            $service = Service::query()->lockForUpdate()->findOrFail($serviceId);

            if ((int) $service->branch_id !== $branchId) {
                throw ValidationException::withMessages([
                    'service_id' => 'Servis tidak berada pada cabang yang sama.',
                ]);
            }

            $hasActiveSale = Sale::query()
                ->where('service_id', $service->id)
                ->whereIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_PAID])
                ->exists();

            if ($hasActiveSale) {
                throw ValidationException::withMessages([
                    'service_id' => 'Servis ini sudah memiliki penjualan aktif.',
                ]);
            }
        }

        if (!empty($indentId)) {
            $indent = Indent::query()->lockForUpdate()->findOrFail($indentId);

            if ((int) $indent->branch_id !== $branchId) {
                throw ValidationException::withMessages([
                    'indent_id' => 'Indent tidak berada pada cabang yang sama.',
                ]);
            }

            $hasActiveSale = Sale::query()
                ->where('indent_id', $indent->id)
                ->whereIn('status', [Sale::STATUS_DRAFT, Sale::STATUS_PAID])
                ->exists();

            if ($hasActiveSale) {
                throw ValidationException::withMessages([
                    'indent_id' => 'Indent ini sudah memiliki penjualan aktif.',
                ]);
            }
        }
    }

    private function extractIdempotencyKey(Request $request): ?string
    {
        $key = trim((string) ($request->input('idempotency_key') ?? $request->header('Idempotency-Key', '')));

        if ($key === '') {
            return null;
        }

        return $key;
    }

    private function replayIdempotentSaleResponse(RequestIdempotency $idempotency)
    {
        if ($idempotency->resource_type !== 'sale' || empty($idempotency->resource_id)) {
            return redirect()->route('sales.create')->with('info', 'Permintaan sebelumnya sudah diproses.');
        }

        $sale = Sale::find($idempotency->resource_id);
        if (!$sale) {
            return redirect()->route('sales.create')->with('info', 'Permintaan sebelumnya sudah diproses.');
        }

        if ($sale->isDraft()) {
            return redirect()->route('sales.create')->with('info', 'Permintaan duplikat diabaikan. Draft #' . $sale->id . ' sudah dibuat.');
        }

        return redirect()->route('sales.show', $sale->id)->with('info', 'Permintaan duplikat diabaikan. Penjualan #' . $sale->id . ' sudah tercatat.');
    }
}
