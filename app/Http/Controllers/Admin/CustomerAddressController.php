<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAddressController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $tab = $request->string('tab')->toString();

        if (! in_array($tab, ['members', 'non-members'], true)) {
            $tab = 'members';
        }

        $addresses = CustomerAddress::query()
            ->with('user:id,name,email,no_tel')
            ->when($tab === 'members', function (Builder $query): void {
                $query->whereNotNull('user_id');
            })
            ->when($tab === 'non-members', function (Builder $query): void {
                $query->whereNull('user_id');
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('recipient_name', 'like', '%'.$search.'%')
                        ->orWhere('no_hp', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%')
                                ->orWhere('no_tel', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/CustomerAddresses/Index', [
            'addresses' => $addresses,
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function repairAddresses(): RedirectResponse
    {
        $updatedCount = 0;

        DB::transaction(function () use (&$updatedCount): void {
            CustomerAddress::query()
                ->whereNotNull('address')
                ->where('address', '!=', '')
                ->get()
                ->each(function (CustomerAddress $address) use (&$updatedCount): void {
                    $formattedAddress = $this->formatAddressUcwords($address->address);
                    if ($formattedAddress === $address->address) {
                        return;
                    }

                    $address->update(['address' => $formattedAddress]);
                    $updatedCount++;
                });
        });

        return back()->with('success', $updatedCount > 0
            ? $updatedCount.' alamat berjaya ditukar kepada format Ucwords.'
            : 'Semua alamat sudah dalam format Ucwords.');
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/CustomerAddresses/Form', [
            'address' => null,
            'customers' => $this->customerOptions(),
            'tab' => $this->normalizeTab($request->string('tab')->toString()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $data = $this->addressData($validated);

        $address = DB::transaction(function () use ($data): CustomerAddress {
            if ($data['user_id'] !== null && $data['is_default']) {
                $this->clearDefault($data['user_id']);
            }

            return CustomerAddress::query()->create($data);
        });

        return redirect()
            ->route('admin.customer-addresses.index', ['tab' => $this->tabForUser($address->user_id)])
            ->with('success', 'Customer address berjaya ditambah.');
    }

    public function edit(Request $request, CustomerAddress $customerAddress): Response
    {
        return Inertia::render('Admin/CustomerAddresses/Form', [
            'address' => [
                'id' => $customerAddress->id,
                'user_id' => $customerAddress->user_id,
                'recipient_name' => $customerAddress->recipient_name,
                'address' => $customerAddress->address,
                'no_hp' => $customerAddress->no_hp,
                'is_default' => $customerAddress->is_default,
            ],
            'customers' => $this->customerOptions(),
            'tab' => $this->normalizeTab($request->string('tab')->toString()),
        ]);
    }

    public function update(Request $request, CustomerAddress $customerAddress): RedirectResponse
    {
        $validated = $this->validateAddress($request, $customerAddress);
        $data = $this->addressData($validated);
        $oldUserId = $customerAddress->user_id;
        $wasDefault = $customerAddress->is_default;

        DB::transaction(function () use ($customerAddress, $data, $oldUserId, $wasDefault): void {
            if ($data['user_id'] !== null && $data['is_default']) {
                $this->clearDefault($data['user_id'], $customerAddress->id);
            }

            $customerAddress->update($data);

            if ($wasDefault && $oldUserId !== null && $oldUserId !== $data['user_id']) {
                $this->ensureDefault($oldUserId);
            }
        });

        return redirect()
            ->route('admin.customer-addresses.index', ['tab' => $this->tabForUser($data['user_id'])])
            ->with('success', 'Customer address berjaya dikemaskini.');
    }

    public function destroy(CustomerAddress $customerAddress): RedirectResponse
    {
        $userId = $customerAddress->user_id;
        $wasDefault = $customerAddress->is_default;

        DB::transaction(function () use ($customerAddress, $userId, $wasDefault): void {
            $customerAddress->delete();

            if ($wasDefault && $userId !== null) {
                $this->ensureDefault($userId);
            }
        });

        return redirect()
            ->route('admin.customer-addresses.index', ['tab' => $this->tabForUser($userId)])
            ->with('success', 'Customer address berjaya dipadam.');
    }

    private function customerOptions()
    {
        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'no_tel']);
    }

    /** @return array<string, mixed> */
    private function validateAddress(Request $request, ?CustomerAddress $address = null): array
    {
        $userId = $request->input('user_id');
        $rules = [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_admin', false)),
            ],
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'is_default' => ['boolean'],
        ];

        if ($userId !== null && $userId !== '') {
            $uniqueAddress = Rule::unique('customer_addresses', 'address')
                ->where('user_id', (int) $userId);

            if ($address) {
                $uniqueAddress->ignore($address->id);
            }

            $rules['address'][] = $uniqueAddress;
        }

        return $request->validate($rules);
    }

    /** @param array<string, mixed> $validated */
    private function addressData(array $validated): array
    {
        return [
            'user_id' => empty($validated['user_id']) ? null : (int) $validated['user_id'],
            'recipient_name' => $validated['recipient_name'],
            'address' => $validated['address'],
            'no_hp' => $validated['no_hp'] ?? null,
            'is_default' => ! empty($validated['user_id']) && (bool) ($validated['is_default'] ?? false),
        ];
    }

    private function clearDefault(int $userId, ?int $exceptId = null): void
    {
        CustomerAddress::query()
            ->where('user_id', $userId)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->update(['is_default' => false]);
    }

    private function ensureDefault(int $userId): void
    {
        if (CustomerAddress::query()->where('user_id', $userId)->where('is_default', true)->exists()) {
            return;
        }

        CustomerAddress::query()
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->first()?->update(['is_default' => true]);
    }

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, ['members', 'non-members'], true) ? $tab : 'members';
    }

    private function tabForUser(?int $userId): string
    {
        return $userId === null ? 'non-members' : 'members';
    }

    private function formatAddressUcwords(?string $address): string
    {
        $address = preg_replace('/[ \t]+/', ' ', trim((string) $address)) ?? trim((string) $address);

        return Str::title(mb_strtolower($address));
    }
}
