import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, LogIn, MessageCircle, Package, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { formatDate } from '@/lib/utils';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  status: string;
  total: number;
  pricing_status: string;
  created_at: string;
  user: { id: number; name: string; is_admin: boolean } | null;
  invoice: { id: number; payment_status: string } | null;
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
  const { data, setData, get, delete: destroy } = useForm({
    q: filters.search,
    status: filters.status,
  });

  const previousSearch = useRef(filters.search);

  useEffect(() => {
    if (previousSearch.current === data.q) return;

    previousSearch.current = data.q;
    const timeout = window.setTimeout(() => {
      get(route('admin.orders.index'), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      });
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [data.q, get]);

  const handleDelete = (id: number, orderNo: string) => {
    if (confirm(`Adakah anda pasti mahu memadam order ${orderNo}?`)) {
      destroy(route('admin.orders.destroy', id));
    }
  };

  const getStatusColor = (status: string) => {
    if (status === 'completed') return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    if (status === 'shipped') return 'bg-sky-100 text-sky-700 border-sky-200';
    if (status === 'cancelled') return 'bg-rose-100 text-rose-700 border-rose-200';
    return 'bg-amber-100 text-amber-700 border-amber-200';
  };

  const statusLabels: Record<string, string> = {
    pending: 'Menunggu semakan',
    paid: 'Bayaran diterima',
    partial: 'Bayaran separa',
    processing: 'Sedang diproses',
    shipped: 'Sedang dihantar',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  };

  const getStatusLabel = (order: Order) => {
    if (order.status === 'pending' && order.invoice && order.invoice.payment_status !== 'paid') {
      return 'Menunggu pembayaran';
    }

    return statusLabels[order.status] ?? order.status;
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
          <Link href={route('admin.orders.create')} className="admin-btn-primary text-sm">
            <Plus className="h-4 w-4" />
            Tambah Order
          </Link>
        </div>

        {/* Search & Filter Toolbar */}
        <div className="admin-toolbar-card flex flex-wrap items-center justify-start gap-3">
          <div className="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
            {[
              { value: 'pending', label: 'Aktif' },
              { value: 'completed', label: 'Selesai' },
            ].map((tab) => (
              <Link
                key={tab.value}
                href={route('admin.orders.index', { q: data.q, status: tab.value })}
                preserveState
                preserveScroll
                onClick={() => setData('status', tab.value)}
                className={`rounded-lg px-4 py-2 text-sm font-semibold transition ${
                  data.status === tab.value
                    ? 'bg-white text-brand-700 shadow-sm'
                    : 'text-slate-500 hover:text-slate-800'
                }`}
              >
                {tab.label}
              </Link>
            ))}
          </div>
          <div className="ml-auto flex w-full max-w-md items-center">
            <div className="relative w-full">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={data.q}
                onChange={(e) => setData('q', e.target.value)}
                placeholder="Cari order no, nama, telefon..."
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>
          </div>
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
                  orders.data.map((order) => {
                    const phoneDigits = order.customer_phone.replace(/\D/g, '');
                    const whatsappPhone = phoneDigits.startsWith('60')
                      ? phoneDigits
                      : phoneDigits.startsWith('0')
                        ? `60${phoneDigits.slice(1)}`
                        : `60${phoneDigits}`;
                    const whatsappLink = phoneDigits.length >= 9
                      ? whatsappWebUrl(whatsappPhone, `Assalamualaikum ${order.customer_name}, saya dari StickerTermurah. Saya nak bertanya tentang order ${order.order_no}.`)
                      : null;

                    return (
                      <tr key={order.id}>
                        <td className="font-medium text-slate-900">{order.order_no}</td>
                        <td>{order.customer_name}</td>
                        <td className="text-slate-500">{order.customer_phone}</td>
                        <td className="font-medium">{formatCurrency(order.total)}</td>
                        <td>
                          <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${getStatusColor(order.status)}`}>
                            {getStatusLabel(order)}
                          </span>
                          <span className="mt-1 block text-[11px] text-slate-400">{pricingLabels[order.pricing_status] ?? ''}</span>
                        </td>
                        <td className="text-slate-500">{formatDate(order.created_at)}</td>
                        <td>
                          <div className="flex flex-wrap items-center gap-1">
                            {whatsappLink && (
                              <Tooltip>
                                <TooltipTrigger asChild>
                                  <a
                                    href={whatsappLink}
                                    target={WHATSAPP_TARGET}
                                    aria-label={`WhatsApp ${order.customer_name}`}
                                    className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                  >
                                    <MessageCircle className="h-4 w-4" />
                                  </a>
                                </TooltipTrigger>
                                <TooltipContent>WhatsApp {order.customer_name}</TooltipContent>
                              </Tooltip>
                            )}
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <Link
                                  href={route('admin.orders.show', order.id)}
                                  aria-label={`Lihat order ${order.order_no}`}
                                  className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-brand-600 transition hover:bg-brand-50"
                                >
                                  <Eye className="h-4 w-4" />
                                </Link>
                              </TooltipTrigger>
                              <TooltipContent>Lihat order {order.order_no}</TooltipContent>
                            </Tooltip>
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <Link
                                  href={route('admin.orders.edit', order.id)}
                                  aria-label={`Edit order ${order.order_no}`}
                                  className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-brand-600"
                                >
                                  <Pencil className="h-4 w-4" />
                                </Link>
                              </TooltipTrigger>
                              <TooltipContent>Edit order {order.order_no}</TooltipContent>
                            </Tooltip>
                            {order.user && !order.user.is_admin && (
                              <Tooltip>
                                <TooltipTrigger asChild>
                                  <Link
                                    href={route('admin.customers.login-as', order.user.id)}
                                    method="post"
                                    as="button"
                                    type="button"
                                    aria-label={`Login sebagai ${order.user.name}`}
                                    className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-brand-600 bg-brand-600 text-white transition hover:bg-brand-700"
                                  >
                                    <LogIn className="h-4 w-4" />
                                  </Link>
                                </TooltipTrigger>
                                <TooltipContent>Login sebagai {order.user.name}</TooltipContent>
                              </Tooltip>
                            )}
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <button
                                  type="button"
                                  onClick={() => handleDelete(order.id, order.order_no)}
                                  aria-label={`Padam order ${order.order_no}`}
                                  className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-rose-600 transition hover:bg-rose-50"
                                >
                                  <Trash2 className="h-4 w-4" />
                                </button>
                              </TooltipTrigger>
                              <TooltipContent>Padam order {order.order_no}</TooltipContent>
                            </Tooltip>
                          </div>
                        </td>
                      </tr>
                    );
                  })
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
