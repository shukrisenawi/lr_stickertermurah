import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, MessageCircle, Package, Pencil, Plus, Search, Trash2, Truck, X } from 'lucide-react';
import { useState } from 'react';
import { formatDate } from '@/lib/utils';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  status: string;
  tracking_no: string | null;
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
  const { data, setData, get, delete: destroy } = useForm({
    q: filters.search,
    status: filters.status,
  });
  const trackingForm = useForm({
    tracking_no: '',
  });

  const [searching, setSearching] = useState(false);
  const [trackingOrder, setTrackingOrder] = useState<Order | null>(null);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearching(true);
    get(route('admin.orders.index'), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  const handleDelete = (id: number, orderNo: string) => {
    if (confirm(`Adakah anda pasti mahu memadam order ${orderNo}?`)) {
      destroy(route('admin.orders.destroy', id));
    }
  };

  const openTrackingModal = (order: Order) => {
    trackingForm.setData('tracking_no', order.tracking_no ?? '');
    trackingForm.clearErrors();
    setTrackingOrder(order);
  };

  const closeTrackingModal = () => {
    if (trackingForm.processing) return;

    setTrackingOrder(null);
    trackingForm.setData('tracking_no', '');
    trackingForm.clearErrors();
  };

  const handleTrackingSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    if (!trackingOrder || trackingForm.data.tracking_no.trim() === '') return;

    trackingForm.put(route('admin.orders.tracking.update', trackingOrder.id), {
      preserveScroll: true,
      onSuccess: () => {
        setTrackingOrder(null);
        trackingForm.setData('tracking_no', '');
      },
    });
  };

  const getStatusColor = (status: string) => {
    return status === 'completed'
      ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
      : 'bg-amber-100 text-amber-700 border-amber-200';
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
              { value: 'pending', label: 'Pending' },
              { value: 'completed', label: 'Completed' },
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
          <form onSubmit={handleSearch} className="ml-auto flex items-center gap-3">
            <div className="relative w-full max-w-md">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={data.q}
                onChange={(e) => setData('q', e.target.value)}
                placeholder="Cari order no, nama, telefon..."
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>
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
                            {order.status === 'completed' ? 'Complete' : 'Pending'}
                          </span>
                          <span className="mt-1 block text-[11px] text-slate-400">{pricingLabels[order.pricing_status] ?? ''}</span>
                          {order.tracking_no && (
                            <span className="mt-1 block max-w-40 truncate text-[11px] font-medium text-sky-600" title={order.tracking_no}>
                              Tracking: {order.tracking_no}
                            </span>
                          )}
                        </td>
                        <td className="text-slate-500">{formatDate(order.created_at)}</td>
                        <td>
                          <div className="flex flex-wrap items-center gap-1">
                            {whatsappLink && (
                              <a
                                href={whatsappLink}
                                target={WHATSAPP_TARGET}
                                aria-label={`WhatsApp ${order.customer_name}`}
                                title={`WhatsApp ${order.customer_name}`}
                                className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                              >
                                <MessageCircle className="h-4 w-4" />
                              </a>
                            )}
                            <button
                              type="button"
                              onClick={() => openTrackingModal(order)}
                              aria-label={`${order.tracking_no ? 'Kemaskini' : 'Tambah'} tracking ${order.order_no}`}
                              title={order.tracking_no ? 'Kemaskini no. tracking' : 'Tambah no. tracking'}
                              className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-600 transition hover:bg-sky-100"
                            >
                              <Truck className="h-4 w-4" />
                            </button>
                            <Link
                              href={route('admin.orders.show', order.id)}
                              aria-label={`Lihat order ${order.order_no}`}
                              title={`Lihat order ${order.order_no}`}
                              className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-brand-600 transition hover:bg-brand-50"
                            >
                              <Eye className="h-4 w-4" />
                            </Link>
                            <Link
                              href={route('admin.orders.edit', order.id)}
                              aria-label={`Edit order ${order.order_no}`}
                              title={`Edit order ${order.order_no}`}
                              className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-brand-600"
                            >
                              <Pencil className="h-4 w-4" />
                            </Link>
                            <button
                              type="button"
                              onClick={() => handleDelete(order.id, order.order_no)}
                              aria-label={`Padam order ${order.order_no}`}
                              title={`Padam order ${order.order_no}`}
                              className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-rose-600 transition hover:bg-rose-50"
                            >
                              <Trash2 className="h-4 w-4" />
                            </button>
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

        {trackingOrder && (
          <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tracking-modal-title"
            onMouseDown={(event) => {
              if (event.target === event.currentTarget) closeTrackingModal();
            }}
          >
            <div className="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
              <div className="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                  <h2 id="tracking-modal-title" className="font-bold text-slate-900">
                    {trackingOrder.tracking_no ? 'Kemaskini No. Tracking' : 'Tambah No. Tracking'}
                  </h2>
                  <p className="mt-1 text-sm text-slate-500">Order {trackingOrder.order_no}</p>
                </div>
                <button
                  type="button"
                  onClick={closeTrackingModal}
                  disabled={trackingForm.processing}
                  className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50"
                  aria-label="Tutup modal tracking"
                >
                  <X className="h-5 w-5" />
                </button>
              </div>
              <form onSubmit={handleTrackingSubmit}>
                <div className="space-y-4 px-5 py-5 sm:px-6">
                  <div>
                    <label htmlFor="order-tracking-no" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                      No. Tracking
                    </label>
                    <input
                      id="order-tracking-no"
                      type="text"
                      value={trackingForm.data.tracking_no}
                      onChange={(event) => trackingForm.setData('tracking_no', event.target.value)}
                      placeholder="Contoh: JNT123456"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {trackingForm.errors.tracking_no && <p className="mt-1.5 text-xs text-rose-600">{trackingForm.errors.tracking_no}</p>}
                  </div>
                  <p className="rounded-2xl border border-sky-200 bg-sky-50 p-3 text-sm leading-6 text-sky-800">
                    Selepas disimpan, status order ini akan ditetapkan secara automatik kepada <strong>completed</strong>.
                  </p>
                </div>
                <div className="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                  <button type="button" onClick={closeTrackingModal} disabled={trackingForm.processing} className="admin-btn-secondary w-full text-sm sm:w-auto">
                    Batal
                  </button>
                  <button
                    type="submit"
                    disabled={trackingForm.processing || trackingForm.data.tracking_no.trim() === ''}
                    className="admin-btn-primary w-full text-sm disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                  >
                    {trackingForm.processing ? 'Menyimpan...' : 'Simpan Tracking'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
