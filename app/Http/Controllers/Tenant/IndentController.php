<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Indent;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class IndentController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('servis-tools.index')->with('info', 'Manajemen indent sudah dipindah ke Servis Tools.');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Indent::class);
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'nullable|exists:services,id',
            'product_name' => 'required|string|max:255',
            'qty' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cost_estimate' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();

        $this->ensureCustomerBranchAccess((int) $validated['customer_id'], $user?->branch_id);

        $validated['branch_id'] = $user->branch_id;
        $validated['qty'] = $validated['qty'] ?? 1;
        $validated['status'] = Indent::STATUS_PENDING;

        $indent = DB::transaction(function () use ($validated, $user) {
            $service = null;

            if (!empty($validated['service_id'])) {
                $service = Service::query()->lockForUpdate()->findOrFail($validated['service_id']);

                if ((int) $service->branch_id !== (int) $user->branch_id) {
                    throw ValidationException::withMessages([
                        'service_id' => 'Servis tidak berada pada cabang yang sama.',
                    ]);
                }

                if (!empty($service->indent_id)) {
                    throw ValidationException::withMessages([
                        'service_id' => 'Servis sudah terhubung dengan indent lain.',
                    ]);
                }

                if (!$service->canTransitionTo(Service::STATUS_INDENT)) {
                    throw ValidationException::withMessages([
                        'service_id' => 'Status servis tidak bisa dipindahkan ke indent.',
                    ]);
                }
            }

            $indent = Indent::create($validated);

            if ($service) {
                $service->update([
                    'status' => Service::STATUS_INDENT,
                    'indent_id' => $indent->id,
                ]);
            }

            return $indent;
        });

        ActivityLog::log('indent_created', 'Inden #' . $indent->id . ' - ' . $validated['product_name'], $indent);

        return redirect()->route('indents.index')->with('success', 'Inden berhasil dibuat.');
    }

    public function destroy(Indent $indent)
    {
        $this->authorize('delete', $indent);
        $this->ensureIndentBranchAccess($indent, auth()->user()?->branch_id);

        DB::transaction(function () use ($indent) {
            $lockedIndent = Indent::query()->lockForUpdate()->findOrFail($indent->id);

            if ($lockedIndent->service_id) {
                $service = Service::query()->lockForUpdate()->find($lockedIndent->service_id);

                if ($service && (int) $service->indent_id === (int) $lockedIndent->id) {
                    if (!$service->canTransitionTo(Service::STATUS_DIKERJAKAN)) {
                        throw ValidationException::withMessages([
                            'service_id' => 'Servis tidak bisa dikembalikan ke status dikerjakan dari status saat ini.',
                        ]);
                    }

                    $service->update([
                        'status' => Service::STATUS_DIKERJAKAN,
                        'indent_id' => null,
                        'dikerjakan_at' => $service->dikerjakan_at ?? now(),
                    ]);
                }
            }

            $lockedIndent->delete();
        });

        ActivityLog::log('indent_deleted', 'Menghapus inden #' . $indent->id, $indent);
        return redirect()->route('indents.index')->with('success', 'Inden berhasil dihapus.');
    }

    public function update(Request $request, Indent $indent)
    {
        $this->authorize('update', $indent);
        $this->ensureIndentBranchAccess($indent, auth()->user()?->branch_id);
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [Indent::STATUS_PENDING, Indent::STATUS_DIPROSES, Indent::STATUS_SELESAI, Indent::STATUS_BATAL]),
            'cost_estimate' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
        ]);

        [$updatedIndent, $oldStatus] = DB::transaction(function () use ($indent, $validated) {
            $lockedIndent = Indent::query()->lockForUpdate()->findOrFail($indent->id);
            $oldStatus = $lockedIndent->status;

            $lockedIndent->update($validated);

            // Jika indent selesai/dibatalkan, service dikembalikan ke dikerjakan dan relasi indent dilepas.
            if (in_array($validated['status'], [Indent::STATUS_SELESAI, Indent::STATUS_BATAL], true) && $lockedIndent->service_id) {
                $service = Service::query()->lockForUpdate()->find($lockedIndent->service_id);

                if ($service && (int) $service->indent_id === (int) $lockedIndent->id) {
                    if (!$service->canTransitionTo(Service::STATUS_DIKERJAKAN)) {
                        throw ValidationException::withMessages([
                            'status' => 'Status servis tidak dapat dikembalikan ke dikerjakan dari status saat ini.',
                        ]);
                    }

                    $service->update([
                        'status' => Service::STATUS_DIKERJAKAN,
                        'indent_id' => null,
                        'dikerjakan_at' => $service->dikerjakan_at ?? now(),
                    ]);
                }
            }

            return [$lockedIndent->fresh(), $oldStatus];
        });

        ActivityLog::log('indent_updated', 'Status inden #' . $updatedIndent->id . ': ' . $oldStatus . ' → ' . $validated['status'], $updatedIndent);

        return back()->with('success', 'Status inden diperbarui.');
    }

    /**
     * Cetak nota DP inden.
     */
    public function printNota(Indent $indent)
    {
        $this->authorize('view', $indent);
        $this->ensureIndentBranchAccess($indent, auth()->user()?->branch_id);

        $indent->load(['customer', 'service', 'branch']);

        $storeName = \App\Models\Tenant\TenantSetting::getValue('store_name', 'ServiceKU');
        $storeAddress = \App\Models\Tenant\TenantSetting::getValue('address', '');
        $storePhone = \App\Models\Tenant\TenantSetting::getValue('phone', '');
        $storeLogo = \App\Models\Tenant\TenantSetting::getValue('logo', '');
        $whatsappNumber = \App\Models\Tenant\TenantSetting::getValue('whatsapp_number', '');
        $paperSize = \App\Models\Tenant\TenantSetting::getValue('paper_size', 'a4');

        $pdf = Pdf::loadView('pdfs.indent-nota', [
            'indent' => $indent,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
            'storeLogo' => $storeLogo,
            'whatsappNumber' => $whatsappNumber,
        ]);

        $paperSizeMap = [
            'thermal_80' => [0, 0, 226.77, 1000],
            'thermal_58' => [0, 0, 164.41, 1000],
            'a5' => 'a5',
            'a4' => 'a4',
        ];

        $paperConfig = $paperSizeMap[$paperSize] ?? 'a4';
        if (is_array($paperConfig)) {
            $pdf->setPaper($paperConfig, 'portrait');
        } else {
            $pdf->setPaper($paperConfig, 'portrait');
        }

        return $pdf->stream('nota-inden-' . $indent->id . '.pdf');
    }

    private function ensureCustomerBranchAccess(int $customerId, $userBranchId): void
    {
        if (!$userBranchId) {
            return;
        }

        $customer = Customer::query()->findOrFail($customerId);

        if ($customer->branch_id && (string) $customer->branch_id !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'customer_id' => 'Pelanggan tidak berada pada cabang aktif Anda.',
            ]);
        }
    }

    private function ensureIndentBranchAccess(Indent $indent, $userBranchId): void
    {
        if (!$userBranchId || !$indent->branch_id) {
            return;
        }

        if ((string) $indent->branch_id !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'indent' => 'Inden tidak berada pada cabang aktif Anda.',
            ]);
        }
    }
}
