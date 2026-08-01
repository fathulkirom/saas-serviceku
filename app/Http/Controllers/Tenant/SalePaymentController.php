<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoicePdf;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Product;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Service;
use App\Models\Tenant\Commission;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\RequestIdempotency;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalePaymentController extends Controller
{
    public function payDraft(Request $request, Sale $sale)
    {
        $user = Auth::user();
        $idempotencyKey = $this->extractIdempotencyKey($request);

        if (!empty($idempotencyKey)) {
            $existing = RequestIdempotency::query()
                ->where('key', $idempotencyKey)
                ->where('action', 'sale.pay_draft')
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return $this->replayIdempotentSaleResponse($existing);
            }
        }

        if ($sale->status !== Sale::STATUS_DRAFT) return back()->with('error', 'Penjualan ini bukan draft.');

        $validated = $request->validate([
            'payment_method_id' => 'nullable|exists:master_data,id',
            'payment_method' => 'nullable|string',
            'paid_amount' => 'required|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
        ]);

        $paidAmount = $validated['paid_amount'];
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        if (!empty($validated['payment_method_id'])) {
            $method = MasterData::find($validated['payment_method_id']);
            $paymentMethod = $method ? $method->name : $paymentMethod;
        }

        try {
            $sale = DB::transaction(function () use ($sale, $validated, $paymentMethod, $paidAmount, $user, $idempotencyKey) {
                $lockedSale = Sale::with('items')->lockForUpdate()->findOrFail($sale->id);

                if ((int) $lockedSale->branch_id !== (int) $user->branch_id) {
                    throw ValidationException::withMessages([
                        'sale' => 'Penjualan tidak berada pada cabang aktif.',
                    ]);
                }

                if ($lockedSale->status !== Sale::STATUS_DRAFT) {
                    throw ValidationException::withMessages([
                        'sale' => 'Penjualan ini bukan draft.',
                    ]);
                }

                $lockedSale->update([
                    'status' => Sale::STATUS_PAID,
                    'payment_method' => $paymentMethod,
                    'payment_method_id' => $validated['payment_method_id'] ?? null,
                    'paid_amount' => $paidAmount,
                    'change' => max(0, $paidAmount - $lockedSale->total),
                ]);

                foreach ($lockedSale->items as $item) {
                    if (!$this->saleItemAffectsStock($lockedSale, $item)) {
                        continue;
                    }

                    $product = Product::query()->lockForUpdate()->find($item->product_id);
                    if (!$product) {
                        continue;
                    }

                    if ((int) $product->branch_id !== (int) $user->branch_id) {
                        throw ValidationException::withMessages([
                            'items' => 'Produk ' . $product->name . ' bukan milik cabang aktif.',
                        ]);
                    }

                    if ((int) $product->stock_quantity < (int) $item->quantity) {
                        throw ValidationException::withMessages([
                            'items' => 'Stok ' . $product->name . ' tidak mencukupi. Sisa: ' . $product->stock_quantity,
                        ]);
                    }

                    $product->reduceStock((int) $item->quantity);
                    InventoryMutation::create([
                        'branch_id' => $user->branch_id,
                        'product_id' => $product->id,
                        'type' => 'keluar',
                        'quantity' => $item->quantity,
                        'reference_type' => 'sale',
                        'reference_id' => (string) $lockedSale->id,
                        'note' => 'Pembayaran draft #' . $lockedSale->id,
                        'created_by' => $user->id,
                    ]);
                }

                if ($lockedSale->service_id) {
                    $paidService = Service::query()->lockForUpdate()->find($lockedSale->service_id);
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

                if (!empty($idempotencyKey)) {
                    RequestIdempotency::create([
                        'key' => $idempotencyKey,
                        'action' => 'sale.pay_draft',
                        'user_id' => $user->id,
                        'resource_type' => 'sale',
                        'resource_id' => (string) $lockedSale->id,
                    ]);
                }

                return $lockedSale->fresh();
            });
        } catch (QueryException $e) {
            if (!empty($idempotencyKey)) {
                $existing = RequestIdempotency::query()
                    ->where('key', $idempotencyKey)
                    ->where('action', 'sale.pay_draft')
                    ->where('user_id', $user->id)
                    ->first();

                if ($existing) {
                    return $this->replayIdempotentSaleResponse($existing);
                }
            }

            throw $e;
        }

        ActivityLog::log('sale_paid', 'Pembayaran draft #' . $sale->id . ' - Rp ' . number_format($sale->total, 0, ',', '.'), $sale);
        GenerateInvoicePdf::dispatch($sale);

        return redirect()->route('sales.show', $sale->id)->with('success', 'Pembayaran berhasil!');
    }

    public function void(Sale $sale)
    {
        $request = request();
        if (!$sale->isPaid()) return back()->with('error', 'Hanya penjualan lunas yang bisa di-void.');

        $user = Auth::user();
        $idempotencyKey = $this->extractIdempotencyKey($request);

        if (!empty($idempotencyKey)) {
            $existing = RequestIdempotency::query()
                ->where('key', $idempotencyKey)
                ->where('action', 'sale.void')
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return $this->replayIdempotentSaleResponse($existing);
            }
        }

        try {
            DB::transaction(function () use ($sale, $user, $idempotencyKey) {
                $lockedSale = Sale::query()->lockForUpdate()->findOrFail($sale->id);

                if ((int) $lockedSale->branch_id !== (int) $user->branch_id) {
                    throw ValidationException::withMessages([
                        'sale' => 'Penjualan tidak berada pada cabang aktif.',
                    ]);
                }

                foreach ($lockedSale->items as $item) {
                    if ($this->saleItemAffectsStock($lockedSale, $item)) {
                        $product = Product::query()->lockForUpdate()->find($item->product_id);
                        if (!$product) {
                            continue;
                        }

                        if ((int) $product->branch_id !== (int) $user->branch_id) {
                            throw ValidationException::withMessages([
                                'items' => 'Produk ' . $product->name . ' bukan milik cabang aktif.',
                            ]);
                        }

                        $product->increaseStock((int) $item->quantity);

                        InventoryMutation::create([
                            'branch_id' => $lockedSale->branch_id,
                            'product_id' => $item->product_id,
                            'type' => 'masuk', 'quantity' => $item->quantity,
                            'reference_type' => 'void_sale',
                            'reference_id' => (string) $lockedSale->id,
                            'note' => 'Void penjualan #' . $lockedSale->id,
                            'created_by' => $user->id,
                        ]);
                    }
                }

                $lockedSale->update(['status' => Sale::STATUS_CANCEL]);

                if (!empty($idempotencyKey)) {
                    RequestIdempotency::create([
                        'key' => $idempotencyKey,
                        'action' => 'sale.void',
                        'user_id' => $user->id,
                        'resource_type' => 'sale',
                        'resource_id' => (string) $lockedSale->id,
                    ]);
                }
            });
        } catch (QueryException $e) {
            if (!empty($idempotencyKey)) {
                $existing = RequestIdempotency::query()
                    ->where('key', $idempotencyKey)
                    ->where('action', 'sale.void')
                    ->where('user_id', $user->id)
                    ->first();

                if ($existing) {
                    return $this->replayIdempotentSaleResponse($existing);
                }
            }

            throw $e;
        }

        ActivityLog::log('sale', 'Void penjualan #' . $sale->id);
        return back()->with('success', 'Penjualan berhasil di-void. Stok dikembalikan.');
    }

    private function saleItemAffectsStock(Sale $sale, $item): bool
    {
        if (empty($item->product_id)) {
            return false;
        }

        if (!in_array($item->item_type, ['sparepart', 'aksesoris'], true)) {
            return false;
        }

        // Service-linked sale: stok sparepart sudah diproses saat service complete.
        if (!empty($sale->service_id)) {
            return false;
        }

        return true;
    }

    private function extractIdempotencyKey(?Request $request = null): ?string
    {
        $request ??= request();

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
