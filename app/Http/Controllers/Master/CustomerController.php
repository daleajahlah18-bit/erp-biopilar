<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Customer::class, strtolower('Customer'));
    }
    public function index()
    {
        $customers = Customer::latest()->sortable()->paginate(10);
        return view('master.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('master.customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required|string|unique:customers',
            'customer_name' => 'required|string|max:255',
            'customer_pic' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'payment_terms' => 'required|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        Customer::create($request->all());

        return redirect()->route('master.customers.index')->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        return view('master.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('master.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code,' . $customer->id,
            'customer_name' => 'required|string|max:255',
            'customer_pic' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'payment_terms' => 'required|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        $customer->update($request->all());

        return redirect()->route('master.customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('master.customers.index')->with('success', 'Customer berhasil dihapus.');
    }
}
