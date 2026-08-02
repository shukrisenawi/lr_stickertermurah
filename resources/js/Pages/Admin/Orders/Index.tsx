import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search, Eye, Package } from 'lucide-react';
import { useState } from 'react';
import { formatDate } from '@/lib/utils';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  status: string;
  total: number;
  pricing_status: string;
  created_at: string;
  user: { name: string } | null;
  invoice: { id: number } | null;
}

interface OrdersIndexProps {
  orders: {
    data: Order[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  filters: {
    search: string;
    status: string;
  };
}

export default function OrdersIndex({ orders, filters }: OrdersIndexProps) {
  const { data, setData, get } = useForm({
    q: filters.search,
    status: filters.status,
  });

  const [searching, setSearching] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearching(true);
    get(route('admin.orders.index'), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
      case 'paid': return 'bg-blue-100 text-blue-700 border-blue-200';
      case 'processing': return 'bg-purple-100 text-purple-700 border-purple-200';
      case 'shipped': return 'bg-sky-100 text-sky-700 border-sky-200';
      case 'completed': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
      case 'cancelled': return 'bg-rose-100 text-rose-700 border-rose-200';
      default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  const pricingLabels: Record<string, string> = {
    auto_priced: 'Harga tersedia',
    pending_admin: 'Perlu harga admin',
    awaiting_customer_approval: 'Tunggu kelulusan',
    approved: 'Sedia invoice',
  };

  return (
    <AdminLayout>
      <Head title="Senarai Order" />
      <div className="space-y-6">
        {/* Page Header */}
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Order</h2>
            <p className="admin-page-copy">Urus dan semak semua tempahan pelanggan.</p>
          </div>
        </div>

        {/* Search & Filter Toolbar */}
        <div className="admin-toolbar-card">
          <form onSubmit={handleSearch} className="flex flex-1 items-center gap-3">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={data.q}
                onChange={(e) => setData('q', e.target.value)}
                placeholder="Cari order no, nama, telefon..."
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <select
              value={data.status}
              onChange={(e) => {
                setData('status', e.target.value);
                handleSearch(e as unknown as React.FormEvent);
              }}
              className="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none"
            >
              <option value="">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <button
              type="submit"
              disabled={searching}
              className="admin-btn-primary text-sm"
            >
              {searching ? 'Mencari...' : 'Cari'}
            </button>
          </form>
        </div>

        {/* Orders Table */}
        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>No. Order</th>
                  <th>Pelanggan</th>
                  <th>Telefon</th>
                  <th>Jumlah</th>
                  <th>Status</th>
                  <th>Tarikh</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {orders.data.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Package className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Order</p>
                        <p className="admin-table-empty-copy">Tiada order yang sepadan dengan carian anda.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  orders.data.map((order) => (
                    <tr key={order.id}>
                      <td className="font-medium text-slate-900">{order.order_no}</td>
                      <td>{order.customer_name}</td>
                      <td className="text-slate-500">{order.customer_phone}</td>
                      <td className="font-medium">{formatCurrency(order.total)}</td>
                      <td>
                        <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${getStatusColor(order.status)}`}>
                          {order.status}
                        </span>
                        <span className="mt-1 block text-[11px] text-slate-400">{pricingLabels[order.pricing_status] ?? ''}</span>
                      </td>
                      <td className="text-slate-500">{formatDate(order.created_at)}</td>
                      <td>
                        <Link
                          href={route('admin.orders.show', order.id)}
                          className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-brand-600 hover:bg-brand-50 transition"
                        >
                          <Eye className="h-4 w-4" />
                          Lihat
                        </Link>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {orders.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {orders.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                        link.active
                          ? 'bg-brand-600 text-white'
                          : 'text-slate-600 hover:bg-slate-50'
                      }`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span
                      key={i}
                      className="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
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
