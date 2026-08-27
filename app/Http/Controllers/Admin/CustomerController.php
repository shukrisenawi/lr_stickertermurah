<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    private const DEFAULT_CUSTOMER_PASSWORD = '123';

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

        $createdCustomer = null;
        $createdCustomerId = $request->session()->pull('created_customer_id');
        if (filled($createdCustomerId)) {
            $customer = User::query()
                ->where('is_admin', false)
                ->find((int) $createdCustomerId);

            if ($customer) {
                $createdCustomer = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ];
            }
        }

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'search' => $search,
            'totalCustomers' => $totalCustomers,
            'customersWithOrders' => $customersWithOrders,
            'customersWithAddresses' => $customersWithAddresses,
            'createdCustomer' => $createdCustomer,
        ]);
    }

    public function create(Request $request): Response
    {
        $lookup = $request->filled('no_tel')
            ? $this->lookupPhone($request->string('no_tel')->toString())
            : null;

        return Inertia::render('Admin/Customers/Create', [
            'lookup' => $lookup,
            'initialPhone' => $request->string('no_tel')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['nullable', 'string', 'in:matched,new'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'no_tel' => ['required', 'string', 'max:30'],
            'address_id' => ['nullable', 'integer'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $mode = $validated['mode'] ?? 'new';
        $phone = $this->normalizePhone($validated['no_tel']);
        if ($phone === null) {
            return back()->withErrors(['no_tel' => 'Nombor telefon tidak sah.'])->withInput();
        }

        if (User::query()->where('no_tel', $phone)->exists()) {
            return back()->withErrors(['no_tel' => 'Nombor telefon ini sudah berdaftar.'])->withInput();
        }

        if ($mode === 'matched') {
            $matchedAddresses = $this->findAddressesByPhone($phone);
            $selectedAddress = $matchedAddresses->firstWhere('id', (int) ($validated['address_id'] ?? 0));

            if (! $selectedAddress) {
                return back()->withErrors(['address_id' => 'Sila pilih alamat customer yang betul.'])->withInput();
            }
        } else {
            if (blank($validated['recipient_name'] ?? null)) {
                return back()->withErrors(['recipient_name' => 'Nama penerima diperlukan.'])->withInput();
            }

            if (blank($validated['address'] ?? null)) {
                return back()->withErrors(['address' => 'Alamat penghantaran diperlukan.'])->withInput();
            }
        }

        $customer = DB::transaction(function () use ($validated, $phone, $mode): User {
            if ($mode === 'matched') {
                $matchedAddresses = $this->findAddressesByPhone($phone);
                $selectedAddress = $matchedAddresses->firstWhere('id', (int) ($validated['address_id'] ?? 0));

                $customer = User::query()->create([
                    'name' => $selectedAddress->recipient_name ?: 'Pelanggan',
                    'no_tel' => $phone,
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make(self::DEFAULT_CUSTOMER_PASSWORD),
                    'must_change_password' => true,
                    'is_admin' => false,
                ]);

                $matchedAddresses->each(fn (CustomerAddress $address) => $address->update([
                    'user_id' => $customer->id,
                    'is_default' => $address->id === $selectedAddress->id,
                ]));

                return $customer;
            }

            $customer = User::query()->create([
                'name' => $validated['recipient_name'],
                'no_tel' => $phone,
                'email' => $validated['email'] ?? null,
                'password' => Hash::make(self::DEFAULT_CUSTOMER_PASSWORD),
                'must_change_password' => true,
                'is_admin' => false,
            ]);

            CustomerAddress::query()->create([
                'user_id' => $customer->id,
                'recipient_name' => $validated['recipient_name'],
                'address' => $validated['address'],
                'no_hp' => $phone,
                'is_default' => true,
            ]);

            return $customer;
        });

        return redirect()->route('admin.customers.index')->with([
            'success' => 'Customer berjaya didaftarkan.',
            'created_customer_id' => $customer->id,
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
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $customer->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
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

    public function resetPassword(User $customer): RedirectResponse
    {
        if ($customer->is_admin) {
            return redirect()->route('admin.customers.index')->with('error', 'Kata laluan akaun admin tidak boleh ditetapkan semula melalui menu customer.');
        }

        $customer->forceFill([
            'password' => Hash::make(self::DEFAULT_CUSTOMER_PASSWORD),
            'must_change_password' => true,
        ])->setRememberToken(Str::random(60));

        $customer->save();

        return redirect()->route('admin.customers.index')->with('success', "Kata laluan {$customer->name} berjaya ditetapkan semula kepada 123.");
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '60'.substr($digits, 1);
        }

        return preg_match('/^60\d{8,12}$/', $digits) === 1 ? $digits : null;
    }

    /** @return array{phone:string|null,account_exists:bool,addresses:array<int,array{id:int,recipient_name:string|null,address:string,no_hp:string|null,is_default:bool}>} */
    private function lookupPhone(string $input): array
    {
        $phone = $this->normalizePhone($input);

        if ($phone === null) {
            return ['phone' => null, 'account_exists' => false, 'addresses' => []];
        }

        return [
            'phone' => $phone,
            'account_exists' => User::query()->where('no_tel', $phone)->exists(),
            'addresses' => $this->findAddressesByPhone($phone)
                ->map(fn (CustomerAddress $address): array => [
                    'id' => $address->id,
                    'recipient_name' => $address->recipient_name,
                    'address' => $address->address,
                    'no_hp' => $address->no_hp,
                    'is_default' => $address->is_default,
                ])
                ->values()
                ->all(),
        ];
    }

    private function findAddressesByPhone(string $phone)
    {
        return CustomerAddress::query()
            ->whereNull('user_id')
            ->get()
            ->filter(fn (CustomerAddress $address): bool => $this->normalizePhone($address->no_hp) === $phone)
            ->values();
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

        return redirect()->route($customer->must_change_password ? 'member.profile.password' : 'member.dashboard');
    }
}
