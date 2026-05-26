import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search, Users, ShoppingBag, MapPin } from 'lucide-react';
import { useState } from 'react';

interface Customer {
  id: number;
  name: string;
  email: string;
  orders_count: number;
  orders_sum_total: number | null;
  default_customer_address: { address: string } | null;
  latest_order: { order_no: string } | null;
}

interface CustomersIndexProps {
  customers: {
    data: Customer[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  search: string;
  totalCustomers: number;
  customersWithOrders: number;
  customersWithAddresses: number;
}

export default function CustomersIndex({ customers, search, totalCustomers, customersWithOrders, customersWithAddresses }: CustomersIndexProps) {
  const { data, setData, get } = useForm({ q: search });
  const [searching, setSearching] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearching(true);
    get(route('admin.customers.index'), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  const formatCurrency = (amount: number | null) => {
    if (!amount) return 'RM 0.00';
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  return (
    <AdminLayout>
      <Head title="Senarai Pelanggan" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Pelanggan</h2>
            <p className="admin-page-copy">Urus maklumat pelanggan berdaftar.</p>
          </div>
        </div>

        {/* KPI Cards */}
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="admin-kpi-label">Jumlah Pelanggan</p>
                <p className="admin-kpi-value">{totalCustomers}</p>
              </div>
              <div className="admin-kpi-icon bg-blue-500">
                <Users className="h-6 w-6" />
              </div>
            </div>
          </div>
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="admin-kpi-label">Dengan Order</p>
                <p className="admin-kpi-value">{customersWithOrders}</p>
              </div>
              <div className="admin-kpi-icon bg-emerald-500">
                <ShoppingBag className="h-6 w-6" />
              </div>
            </div>
          </div>
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="admin-kpi-label">Dengan Alamat</p>
                <p className="admin-kpi-value">{customersWithAddresses}</p>
              </div>
              <div className="admin-kpi-icon bg-amber-500">
                <MapPin className="h-6 w-6" />
              </div>
            </div>
          </div>
        </div>

        {/* Search */}
        <div className="admin-toolbar-card">
          <form onSubmit={handleSearch} className="flex flex-1 items-center gap-3">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={data.q}
                onChange={(e) => setData('q', e.target.value)}
                placeholder="Cari nama, email, telefon..."
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <button type="submit" disabled={searching} className="admin-btn-primary text-sm">
              {searching ? 'Mencari...' : 'Cari'}
            </button>
          </form>
        </div>

        {/* Table */}
        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Order</th>
                  <th>Jumlah Belanja</th>
                  <th>Alamat</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {customers.data.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Users className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Pelanggan</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  customers.data.map((customer) => (
                    <tr key={customer.id}>
                      <td className="font-medium text-slate-900">{customer.name}</td>
                      <td>{customer.email}</td>
                      <td>{customer.orders_count}</td>
                      <td className="font-medium">{formatCurrency(customer.orders_sum_total)}</td>
                      <td className="text-slate-500 max-w-[200px] truncate">
                        {customer.default_customer_address?.address || '-'}
                      </td>
                      <td>
                        <span className="text-sm text-slate-400">
                          {customer.latest_order?.order_no || '-'}
                        </span>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {customers.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {customers.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                  )
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
