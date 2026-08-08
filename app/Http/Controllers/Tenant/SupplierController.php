<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::where('branch_id', auth()->user()->branch_id)
            ->orderBy('purchase_count', 'desc')->paginate(20);

        return inertia('Inventaris/Suppliers', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'category'       => 'nullable|in:sparepart,tools,aksesoris,umum',
            'notes'          => 'nullable|string|max:500',
        ]);

        Supplier::create(array_merge($validated, [
            'branch_id' => auth()->user()->branch_id,
        ]));

        return back()->with('success', "Supplier '{$validated['name']}' ditambahkan.");
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'category'       => 'nullable|in:sparepart,tools,aksesoris,umum',
            'notes'          => 'nullable|string|max:500',
            'is_active'      => 'boolean',
        ]);

        $supplier->update($validated);
        return back()->with('success', 'Supplier diperbarui.');
    }
}
