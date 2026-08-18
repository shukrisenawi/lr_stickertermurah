<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());

        if ($search === '') {
            return response()->json(['results' => []]);
        }

        $addresses = CustomerAddress::query()
            ->with('user:id,name,email,no_tel')
            ->where(function (Builder $query) use ($search): void {
                $query->where('recipient_name', 'like', '%'.$search.'%')
                    ->orWhere('no_hp', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('no_tel', 'like', '%'.$search.'%');
                    });
            })
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (CustomerAddress $address): array => [
                'id' => $address->id,
                'recipient_name' => $address->recipient_name,
                'address' => $address->address,
                'no_hp' => $address->no_hp,
                'user' => $address->user ? [
                    'id' => $address->user->id,
                    'name' => $address->user->name,
                    'email' => $address->user->email,
                    'no_tel' => $address->user->no_tel,
                ] : null,
            ])
            ->values();

        return response()->json(['results' => $addresses]);
    }

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
                        ->orWhere('no_tel', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('customerAddresses', function (Builder $addressQuery) use ($search): void {
                            $addressQuery->where('no_hp', 'like', '%'.$search.'%');
                        })
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

    public function destroy(User $customer): RedirectResponse
    {
        if ($customer->is_admin) {
            return redirect()->route('admin.customers.index')->with('error', 'Akaun admin tidak boleh dipadam.');
        }

        $customer->load('customerProjects');

        foreach ($customer->customerProjects as $project) {
            Storage::delete(array_merge(
                $project->preview_paths ?: [$project->preview_path],
                $project->source_paths ?: [$project->source_path],
            ));
        }

        if ($customer->avatar_path) {
            Storage::disk('public')->delete($customer->avatar_path);
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Pelanggan berjaya dipadam.');
    }

    public function loginAs(Request $request, User $customer): RedirectResponse
    {
        if ($customer->is_admin) {
            return redirect()->route('admin.customers.index')->with('error', 'Tidak boleh log masuk sebagai akaun admin.');
        }

        $adminId = Auth::id();

        Auth::login($customer);
        $request->session()->regenerate();
        $request->session()->put('impersonate_admin_id', $adminId);

        return redirect()->route('member.dashboard');
    }
}
