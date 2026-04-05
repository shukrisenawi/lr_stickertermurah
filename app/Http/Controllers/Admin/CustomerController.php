<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());

        $customers = User::query()
            ->where('is_admin', false)
            ->with([
                'defaultCustomerAddress:id,user_id,address,updated_at',
                'latestOrder:id,user_id,customer_name,customer_phone,created_at,total',
            ])
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('orders', function (Builder $orderQuery) use ($search): void {
                            $orderQuery->where('customer_phone', 'like', '%' . $search . '%');
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

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
            'totalCustomers' => $totalCustomers,
            'customersWithOrders' => $customersWithOrders,
            'customersWithAddresses' => $customersWithAddresses,
        ]);
    }
}
