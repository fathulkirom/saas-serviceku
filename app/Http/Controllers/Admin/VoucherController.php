<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::with('tenant')->orderBy('created_at', 'desc')->paginate(20);
        return inertia('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
        ]);
    }

    public function create()
    {
        $tenants = \App\Models\Tenant::orderBy('tenant_name')->get(['id', 'tenant_name']);
        return inertia('Admin/Vouchers/Create', [
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:vouchers,code',
            'type' => 'required|string|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'applicable_for' => 'required|string|in:new,existing,both',
            'tenant_id' => 'nullable|exists:tenants,id',
            'max_uses' => 'nullable|integer|min:0',
            'min_plan_price' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Auto-generate code jika tidak diisi
        if (empty($validated['code'])) {
            $validated['code'] = Voucher::generateCode();
        }

        $validated['created_by'] = Auth::guard('web')->id();
        $validated['is_active'] = $request->boolean('is_active', true);

        Voucher::create($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        $tenants = \App\Models\Tenant::orderBy('tenant_name')->get(['id', 'tenant_name']);
        return inertia('Admin/Vouchers/Create', [
            'voucher' => $voucher,
            'tenants' => $tenants,
            'isEditing' => true,
        ]);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'type' => 'required|string|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'applicable_for' => 'required|string|in:new,existing,both',
            'tenant_id' => 'nullable|exists:tenants,id',
            'max_uses' => 'nullable|integer|min:0',
            'min_plan_price' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $voucher->update($validated);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', 'Voucher berhasil dihapus.');
    }

    public function generateCode()
    {
        $code = Voucher::generateCode();
        return response()->json(['code' => $code]);
    }
}
