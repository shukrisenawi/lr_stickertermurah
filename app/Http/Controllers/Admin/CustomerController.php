<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());

        $customers = User::query()
            ->where('is_admin', false)
            ->with([
                'defaultCustomerAddress',
                'latestOrder',
            ])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('orders', function (Builder $orderQuery) use ($search): void {
                            $orderQuery->where('customer_phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalCustomers = User::query()
            ->where('is_admin', false)
            ->count();

        $customersWithOrders = User::query()
            ->where('is_admin', false)
            ->has('orders')
            ->count();

        $customersWithAddresses = User::query()
            ->where('is_admin', false)
            ->has('customerAddresses')
            ->count();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'search' => $search,
            'totalCustomers' => $totalCustomers,
            'customersWithOrders' => $customersWithOrders,
            'customersWithAddresses' => $customersWithAddresses,
        ]);
    }

    public function edit(User $customer): Response
    {
        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer->load('customerAddresses'),
        ]);
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $customer->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Maklumat pelanggan berjaya dikemaskini.');
    }

    public function loginAs(Request $request, User $customer): RedirectResponse
    {
        if ($customer->is_admin) {
            return redirect()->route('admin.customers.index')->with('error', 'Tidak boleh log masuk sebagai akaun admin.');
        }

        $request->session()->put('impersonate_admin_id', Auth::id());

        Auth::login($customer);
        $request->session()->regenerate();

        return redirect()->route('member.dashboard')->with('info', 'Anda sedang melihat sebagai '.$customer->name.'. Klik Kembali ke Admin untuk pulang.');
    }
}
