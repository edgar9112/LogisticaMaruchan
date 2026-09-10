<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $customers = Customer::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('customers'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(CustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Customer $customer)
    {
        return view('clientes.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer)
    {
        $customer->update(['active' => false]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente desactivado correctamente.');
    }
}