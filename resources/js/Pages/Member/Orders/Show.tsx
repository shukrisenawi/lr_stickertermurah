import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, Clock3, Image as ImageIcon, MapPin, Package, Phone, Receipt, User, X } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';
import { useEffect, useState } from 'react';

interface OrderItem {
  id: number;
  design: { name: string } | null;
  project: { title: string } | null;
  size: { name: string } | null;
  quantity: number;
  unit_price: number;
  line_total: number;
  preview_url: string | null;
  preview_urls: string[];
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
  shipping_fee: number;
  shipping_region: string | null;
  pricing_status: string;
  price_note: string | null;
  custom_request: string | null;
  created_at: string;
  invoice: { id: number; invoice_no: string } | null;
  items: OrderItem[];
}

interface OrderShowProps {
  order: Order;
}

interface PreviewImage {
  url: string;
  alt: string;
}

export default function MemberOrderShow({ order }: OrderShowProps) {
  const [previewImage, setPreviewImage] = useState<PreviewImage | null>(null);

  useEffect(() => {
    if (!previewImage) return;

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setPreviewImage(null);
      }
    };
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [previewImage]);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
      case 'paid': return 'bg-blue-100 text-blue-700 border-blue-200';
      case 'partial': return 'bg-violet-100 text-violet-700 border-violet-200';
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
    auto_priced: 'Harga automatik tersedia',
    pending_admin: 'Menunggu harga admin',
    awaiting_customer_approval: 'Menunggu kelulusan anda',
    approved: 'Harga telah diluluskan',
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

  return (
    <MemberLayout>
      <Head title={`Order ${order.order_no}`} />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8 space-y-6">
        <Link
          href={route('member.orders.index')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          Kembali ke Order Saya
        </Link>

        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-brand-600">
              <Package className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-xl font-bold text-slate-900">{order.order_no}</h1>
              <p className="text-sm text-slate-500">{formatDateTime(order.created_at)}</p>
            </div>
          </div>
          <span className={`inline-flex rounded-full border px-4 py-1.5 text-xs font-semibold uppercase tracking-wider ${getStatusColor(order.status)}`}>
            {statusLabels[order.status] ?? order.status}
          </span>
        </div>

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
          {/* Customer Info */}
          <div className="frontend-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Maklumat Penghantaran</h3>
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
              {order.tracking_no && (
                <div className="pt-2 border-t border-slate-100">
                  <span className="text-xs text-slate-500">No. Tracking: </span>
                  <span className="text-sm font-medium text-slate-900">{order.tracking_no}</span>
                </div>
              )}
            </div>
          </div>

          {/* Order Summary */}
          <div className="frontend-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Ringkasan</h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Material</span>
                <span className="text-sm font-medium text-slate-900">{order.material}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Subtotal</span>
                <span className="text-sm text-slate-900">{formatCurrency(order.subtotal)}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Pos</span>
                <span className={`text-sm font-medium ${Number(order.shipping_fee ?? 0) === 0 ? 'text-emerald-600' : 'text-slate-900'}`}>
                  {Number(order.shipping_fee ?? 0) === 0 ? 'Percuma' : formatCurrency(Number(order.shipping_fee))}
                </span>
              </div>
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm font-semibold text-slate-900">Jumlah</span>
                <span className="text-lg font-bold text-brand-600">{formatCurrency(order.total)}</span>
              </div>
            </div>
            {order.invoice && (
              <div className="mt-4 pt-4 border-t border-slate-100">
                <Link
                  href={route('member.invoices.show', order.invoice.id)}
                  className="frontend-btn-secondary w-full text-sm"
                >
                  <Receipt className="h-4 w-4" />
                  Lihat Invoice
                </Link>
              </div>
            )}
          </div>
        </div>

        <div className={`rounded-2xl border p-5 ${order.pricing_status === 'awaiting_customer_approval' ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white'}`}>
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-start gap-3">
              {order.pricing_status === 'awaiting_customer_approval' ? <Clock3 className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" /> : <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />}
              <div>
                <p className="font-bold text-slate-900">{pricingLabels[order.pricing_status] ?? 'Status harga sedang disemak'}</p>
                {order.price_note && <p className="mt-1 text-sm text-slate-600">Nota admin: {order.price_note}</p>}
                {order.pricing_status === 'pending_admin' && <p className="mt-1 text-sm text-slate-600">Admin akan semak dan masukkan harga untuk saiz atau kuantiti ini.</p>}
                {order.pricing_status === 'auto_priced' && <p className="mt-1 text-sm text-slate-600">Harga dikira berdasarkan saiz dan kuantiti dalam sistem.</p>}
              </div>
            </div>
            {order.pricing_status === 'awaiting_customer_approval' && (
              <Link
                href={route('member.orders.approve-price', order.id)}
                method="post"
                as="button"
                type="button"
                className="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700"
              >
                <CheckCircle2 className="h-4 w-4" />
                Luluskan Harga
              </Link>
            )}
          </div>
        </div>

        {/* Items */}
        <div className="frontend-flat-card">
          <div className="px-5 py-4 border-b border-slate-200">
            <h3 className="text-lg font-bold text-slate-900">Item Order</h3>
          </div>
          <div className="frontend-table-wrap">
            <table className="frontend-table">
              <thead>
                <tr>
                  <th>Gambar</th>
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
                    <td>
                      {item.preview_urls.length > 0 ? (
                        <div className="flex max-w-[220px] flex-wrap gap-2">
                          {item.preview_urls.map((previewUrl, previewIndex) => (
                            <button
                              key={previewUrl}
                              type="button"
                              onClick={() => setPreviewImage({
                                url: previewUrl,
                                alt: `Preview ${item.design?.name || item.project?.title || 'design'} ${previewIndex + 1}`,
                              })}
                              className="inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition hover:border-brand-300"
                              aria-label={`Lihat gambar ${previewIndex + 1}`}
                            >
                              <img src={previewUrl} alt={`Preview ${item.design?.name || item.project?.title || 'design'} ${previewIndex + 1}`} loading="lazy" className="h-full w-full object-contain" />
                            </button>
                          ))}
                        </div>
                      ) : (
                        <span className="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-slate-50 text-slate-300">
                          <ImageIcon className="h-5 w-5" />
                        </span>
                      )}
                    </td>
                    <td>{item.design?.name || item.project?.title || 'Design sendiri'}</td>
                    <td>{item.size?.name || 'Saiz custom'}</td>
                    <td>{item.quantity}</td>
                    <td>{formatCurrency(item.unit_price)}</td>
                    <td className="font-medium">{formatCurrency(item.line_total)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
        {previewImage && (
          <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-label={previewImage.alt}
            onMouseDown={(event) => {
              if (event.target === event.currentTarget) setPreviewImage(null);
            }}
          >
            <div className="relative flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col items-center rounded-3xl border border-white/10 bg-slate-900/95 p-3 shadow-2xl sm:p-5">
              <button
                type="button"
                onClick={() => setPreviewImage(null)}
                className="absolute right-3 top-3 z-10 rounded-xl bg-white/10 p-2 text-white transition hover:bg-white/20"
                aria-label="Tutup preview gambar"
              >
                <X className="h-5 w-5" />
              </button>
              <img src={previewImage.url} alt={previewImage.alt} className="max-h-[calc(100vh-7rem)] max-w-full rounded-2xl object-contain" />
              <p className="mt-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-300">Preview order {order.order_no}</p>
            </div>
          </div>
        )}
      </div>
    </MemberLayout>
  );
}
