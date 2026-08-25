import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, BadgeCheck, Clock3, Download, FileText, MapPin, Package, Phone, Receipt, Truck, User } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';

interface UploadedFile {
  id: string;
  item_label: string;
  name: string;
  url: string;
  preview_url: string | null;
  is_image: boolean;
}

interface OrderItem {
  id: number;
  design: { name: string } | null;
  project: { id: number; title: string } | null;
  size: { name: string } | null;
  quantity: number;
  unit_price: number;
  subtotal: number;
  line_total?: number;
}

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  material: string;
  status: string;
  tracking_no: string | null;
  subtotal: number;
  total: number;
  pricing_status: string;
  price_note: string | null;
  custom_request: string | null;
  created_at: string;
  user: { id: number; name: string; email: string } | null;
  invoice: { id: number; invoice_no: string; amount: number } | null;
  items: OrderItem[];
}

interface OrderShowProps {
  order: Order;
  uploadedFiles: UploadedFile[];
  editMode: boolean;
}

export default function OrderShow({ order, uploadedFiles, editMode }: OrderShowProps) {
  const { data, setData, put, processing } = useForm({
    status: order.status,
    tracking_no: order.tracking_no || '',
  });
  const quoteForm = useForm({
    amount: order.total > 0 ? String(order.total) : '',
    price_note: order.price_note || '',
  });

  const handleUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.orders.update', order.id));
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
    auto_priced: 'Harga automatik',
    pending_admin: 'Menunggu harga admin',
    awaiting_customer_approval: 'Menunggu kelulusan customer',
    approved: 'Harga diluluskan customer',
  };

  const handleQuote = (e: React.FormEvent) => {
    e.preventDefault();
    quoteForm.post(route('admin.orders.quote', order.id), { preserveScroll: true });
  };

  return (
    <AdminLayout>
      <Head title={`Order ${order.order_no}`} />
      <div className="space-y-6">
        {/* Back Link */}
        <Link
          href={route('admin.orders.index')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          Kembali ke Senarai Order
        </Link>

        {/* Order Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <div className="admin-icon-badge">
              <Package className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-xl font-bold text-slate-900">{order.order_no}</h1>
              <p className="text-sm text-slate-500">{formatDateTime(order.created_at)}</p>
            </div>
          </div>
          <span className={`inline-flex rounded-full border px-4 py-1.5 text-xs font-semibold uppercase tracking-wider ${getStatusColor(order.status)}`}>
            {order.status}
          </span>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          {/* Customer Info */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Maklumat Pelanggan</h3>
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <User className="h-4 w-4 text-slate-400" />
                <span className="text-sm text-slate-900">{order.customer_name}</span>
              </div>
              <div className="flex items-center gap-3">
                <Phone className="h-4 w-4 text-slate-400" />
                <span className="text-sm text-slate-900">{order.customer_phone}</span>
              </div>
              <div className="flex items-start gap-3">
                <MapPin className="h-4 w-4 text-slate-400 mt-0.5" />
                <span className="text-sm text-slate-900">{order.customer_address}</span>
              </div>
              {order.user && (
                <div className="flex items-center gap-3 pt-2 border-t border-slate-100">
                  <span className="text-xs text-slate-500">Akaun:</span>
                  <span className="text-sm text-slate-900">{order.user.name} ({order.user.email})</span>
                </div>
              )}
            </div>
          </div>

          {/* Order Summary */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Ringkasan Order</h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Material</span>
                <span className="text-sm font-medium text-slate-900">{order.material}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Subtotal</span>
                <span className="text-sm text-slate-900">{formatCurrency(order.subtotal)}</span>
              </div>
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm font-semibold text-slate-900">Jumlah</span>
                <span className="text-lg font-bold text-brand-600">{formatCurrency(order.total)}</span>
              </div>
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm text-slate-500">Status harga</span>
                <span className="text-right text-sm font-semibold text-amber-700">{pricingLabels[order.pricing_status] ?? order.pricing_status}</span>
              </div>
            </div>
          </div>

          {/* Update Form */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Kemaskini Status</h3>
            <form onSubmit={handleUpdate} className="space-y-4">
              <div>
                <label htmlFor="status" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                <select
                  id="status"
                  value={data.status}
                  onChange={(e) => setData('status', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                >
                  <option value="pending">Pending</option>
                  <option value="paid">Paid</option>
                  <option value="processing">Processing</option>
                  <option value="shipped">Shipped</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div>
                <label htmlFor="tracking_no" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">No. Tracking</label>
                <input
                  id="tracking_no"
                  type="text"
                  value={data.tracking_no}
                  onChange={(e) => setData('tracking_no', e.target.value)}
                  placeholder="Contoh: JNT123456"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>
              <button
                type="submit"
                disabled={processing}
                className="admin-btn-primary w-full text-sm"
              >
                {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
              </button>
            </form>
          </div>
        </div>

        <div className="admin-flat-card p-6">
          <div className="flex items-start gap-3">
            {order.pricing_status === 'awaiting_customer_approval' || order.pricing_status === 'pending_admin' ? <Clock3 className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" /> : <BadgeCheck className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />}
            <div>
              <h3 className="text-lg font-bold text-slate-900">Tetapkan Harga Order</h3>
              <p className="mt-1 text-sm text-slate-500">Untuk saiz atau kuantiti yang tiada dalam jadual harga, masukkan jumlah dan hantar kepada customer untuk kelulusan.</p>
            </div>
          </div>
          <form onSubmit={handleQuote} className="mt-5 grid grid-cols-1 gap-4 md:grid-cols-[220px_1fr_auto] md:items-end">
            <div>
              <label htmlFor="quote-amount" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah harga (RM)</label>
              <input
                id="quote-amount"
                type="number"
                min="0.01"
                step="0.01"
                value={quoteForm.data.amount}
                onChange={(e) => quoteForm.setData('amount', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="Contoh: 85.00"
              />
              {quoteForm.errors.amount && <p className="mt-1 text-xs text-rose-600">{quoteForm.errors.amount}</p>}
            </div>
            <div>
              <label htmlFor="quote-note" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nota kepada customer (pilihan)</label>
              <input
                id="quote-note"
                type="text"
                value={quoteForm.data.price_note}
                onChange={(e) => quoteForm.setData('price_note', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="Contoh: Termasuk caj setup die-cut"
              />
            </div>
            <button type="submit" disabled={quoteForm.processing || !!order.invoice} className="admin-btn-primary text-sm disabled:opacity-50">
              {quoteForm.processing ? 'Menghantar...' : 'Hantar Harga'}
            </button>
          </form>
        </div>

        {/* Order Items */}
        <div className="admin-flat-card">
          <div className="admin-card-header">
            <div className="flex items-center gap-3">
              <div className="admin-icon-badge">
                <Package className="h-5 w-5" />
              </div>
              <h3 className="text-lg font-bold text-slate-900">Item Order</h3>
            </div>
          </div>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Design</th>
                  <th>Saiz</th>
                  <th>Kuantiti</th>
                  <th>Harga Unit</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {order.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.design?.name || item.project?.title || 'Design sendiri'}</td>
                    <td>{item.size?.name || 'Saiz custom'}</td>
                    <td>{item.quantity}</td>
                    <td>{formatCurrency(item.unit_price)}</td>
                    <td className="font-medium">{formatCurrency(item.line_total ?? item.subtotal)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {!editMode && (
          <section className="admin-flat-card p-6">
            <div className="flex items-start gap-3">
              <div className="admin-icon-badge">
                <FileText className="h-5 w-5" />
              </div>
              <div>
                <h3 className="text-lg font-bold text-slate-900">Fail Design Dihantar</h3>
                <p className="mt-1 text-sm text-slate-500">Lihat atau download fail yang customer muat naik semasa membuat order.</p>
              </div>
            </div>

            {uploadedFiles.length > 0 ? (
              <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {uploadedFiles.map((file) => (
                  file.is_image ? (
                    <a
                      key={file.id}
                      href={file.url}
                      target="_blank"
                      rel="noreferrer"
                      className="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-brand-300 hover:shadow-md"
                    >
                      <img src={file.preview_url ?? file.url} alt={file.name} loading="lazy" className="h-40 w-full bg-slate-100 object-contain" />
                      <span className="block border-t border-slate-100 px-3 py-2">
                        <span className="block text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{file.item_label}</span>
                        <span className="mt-0.5 block truncate text-xs font-medium text-slate-700 group-hover:text-brand-700">{file.name}</span>
                      </span>
                    </a>
                  ) : (
                    <a
                      key={file.id}
                      href={file.url}
                      target="_blank"
                      rel="noreferrer"
                      className="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-brand-300 hover:bg-brand-50"
                    >
                      <FileText className="h-8 w-8 shrink-0 text-brand-500" />
                      <span className="min-w-0">
                        <span className="block text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{file.item_label}</span>
                        <span className="mt-0.5 block truncate text-xs font-medium text-slate-700">{file.name}</span>
                      </span>
                      <Download className="ml-auto h-4 w-4 shrink-0 text-slate-400" />
                    </a>
                  )
                ))}
              </div>
            ) : (
              <p className="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500">Tiada fail design dimuat naik untuk order ini.</p>
            )}
          </section>
        )}

        {/* Invoice & Actions */}
        <div className="flex flex-wrap items-center gap-3">
          {order.invoice ? (
            <Link
              href={route('admin.invoices.show', order.invoice.id)}
              className="admin-btn-secondary text-sm"
            >
              <Receipt className="h-4 w-4" />
              Lihat Invoice ({order.invoice.invoice_no})
            </Link>
          ) : order.pricing_status === 'pending_admin' || order.pricing_status === 'awaiting_customer_approval' || order.total <= 0 ? (
            <a
              href="#quote-amount"
              className="admin-btn-secondary text-sm"
            >
              Menunggu Harga / Kelulusan
            </a>
          ) : (
            <Link
              href={route('admin.invoices.store', order.id)}
              method="post"
              as="button"
              type="button"
              className="admin-btn-primary text-sm"
            >
              <Receipt className="h-4 w-4" />
              Cipta Invoice
            </Link>
          )}
          <Link
            href={route('admin.jnt.index', { order_id: order.id })}
            className="admin-btn-secondary text-sm"
          >
            <Truck className="h-4 w-4" />
            J&T Waybill
          </Link>
        </div>

      </div>
    </AdminLayout>
  );
}
