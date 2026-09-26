import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Receipt, Search } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { formatDate } from '@/lib/utils';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  total: number;
  created_at: string;
  user: { name: string } | null;
}

interface InvoicesCreateProps {
  orders: {
    data: Order[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  search: string;
  shippingCost: number;
}

export default function InvoicesCreate({ orders, search, shippingCost }: InvoicesCreateProps) {
  const { data, setData, get } = useForm({ q: search });
  const {
    data: shippingData,
    setData: setShippingData,
    post: createInvoice,
    processing,
  } = useForm({ shipping_cost: shippingCost.toFixed(2) });

  const previousSearch = useRef(search);

  useEffect(() => {
    if (previousSearch.current === data.q) return;

    previousSearch.current = data.q;
    const timeout = window.setTimeout(() => {
      get(route('admin.invoices.create'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      });
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [data.q, get]);

  const handleCreateInvoice = (orderId: number) => {
    if (confirm('Cipta invoice untuk order ini?')) {
      createInvoice(route('admin.invoices.store-from-menu'), {
        data: { order_id: orderId, shipping_cost: shippingData.shipping_cost },
      } as any);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  return (
    <AdminLayout>
      <Head title="Cipta Invoice" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Cipta Invoice</h2>
            <p className="admin-page-copy">Pilih order untuk mencipta invoice.</p>
          </div>
          <Link href={route('admin.invoices.manual.create')} className="admin-btn-primary text-sm">
            Tambah Invoice
          </Link>
        </div>

        <div className="admin-toolbar-card">
          <div className="flex flex-col gap-4 md:flex-row md:items-end">
            <div className="flex w-full max-w-md flex-1 items-center">
              <div className="relative w-full">
                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  type="search"
                  value={data.q}
                  onChange={(e) => setData('q', e.target.value)}
                  placeholder="Cari order..."
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>
            </div>
            <div className="w-full md:max-w-xs">
              <label htmlFor="order-shipping-cost" className="block text-xs font-semibold uppercase tracking-wider text-amber-900 mb-1.5">
                Kos Pos Sebenar (RM)
              </label>
              <input
                id="order-shipping-cost"
                type="number"
                min="0"
                step="0.01"
                value={shippingData.shipping_cost}
                onChange={(event) => setShippingData('shipping_cost', event.target.value)}
                className="w-full rounded-xl border border-amber-200 bg-amber-50/70 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
              />
              <p className="mt-1 text-xs text-slate-500">Nilai ini digunakan untuk invoice order yang dipilih.</p>
            </div>
          </div>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>No. Order</th>
                  <th>Pelanggan</th>
                  <th>Jumlah</th>
                  <th>Tarikh</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {orders.data.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Receipt className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Order Tanpa Invoice</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  orders.data.map((order) => (
                    <tr key={order.id}>
                      <td className="font-medium text-slate-900">{order.order_no}</td>
                      <td>{order.customer_name}</td>
                      <td className="font-medium">{formatCurrency(order.total)}</td>
                      <td>{formatDate(order.created_at)}</td>
                      <td>
                        <button
                          type="button"
                          onClick={() => handleCreateInvoice(order.id)}
                          disabled={processing}
                          className="admin-btn-primary text-xs disabled:opacity-50"
                        >
                          <Receipt className="h-3 w-3" />
                          Cipta Invoice
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {orders.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {orders.links.map((link) => {
                  const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
                  return link.url ? (
                    <Link key={link.label} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>
                      {label}
                    </Link>
                  ) : (
                    <span key={link.label} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">{label}</span>
                  );
                })}
              </div>
            </div>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
