<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Customer::class);
        $customers = Customer::with('branch')->latest()->paginate(15);
        return inertia('Customers/Index', ['customers' => $customers]);
    }

    public function create()
    {
        $this->authorize('create', Customer::class);
        return inertia('Customers/Create', [
            'customFields' => \App\Models\Tenant\CustomField::where('module', 'customer')
                ->where('is_active', true)->orderBy('ordering')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Customer::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        $customer = Customer::create($validated);

        // Simpan custom field values
        $customer->saveCustomFieldValues($request->all());

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function storeApi(Request $request)
    {
        $this->authorize('create', Customer::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => 'Pelanggan berhasil ditambahkan.',
        ]);
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        $customer->load(['services' => function ($q) {
            $q->latest()->take(10);
        }, 'sales' => function ($q) {
            $q->latest()->take(10);
        }]);

        return inertia('Customers/Show', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string',
            'is_member' => 'nullable|boolean',
        ]);

        $customer->update($validated);

        // Simpan custom field values
        $customer->saveCustomFieldValues($request->all());

        return back()->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function registerMember(Customer $customer)
    {
        $this->authorize('update', $customer);
        if (!$customer->card_number) {
            $year = date('y');
            $customer->update([
                'is_member' => true,
                'card_number' => 'ACS' . $year . str_pad((string)$customer->id, 6, '0', STR_PAD_LEFT),
            ]);
        } else {
            $customer->update(['is_member' => true]);
        }

        return back()->with('success', 'Kartu member ' . $customer->card_number . ' berhasil diaktifkan.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
