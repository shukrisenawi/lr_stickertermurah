import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, CreditCard, Search, Truck } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';
import { type PageProps } from '@/types';

interface LookupOrder {
  order_no: string;
  status: string;
  pricing_status: string;
  payment_status: string;
  total: number;
  created_at: string;
  tracking_no: string | null;
}

interface LookupOrderProps extends PageProps {
  order?: LookupOrder | null;
}

const statusLabels: Record<string, string> = {
  pending: 'Menunggu semakan',
  paid: 'Bayaran diterima',
  processing: 'Sedang diproses',
  shipped: 'Sedang dihantar',
  completed: 'Selesai',
  cancelled: 'Dibatalkan',
};

const paymentLabels: Record<string, string> = {
  pending: 'Menunggu bayaran',
  unpaid: 'Belum bayar',
  submitted: 'Bayaran sedang disemak',
  rejected: 'Bayaran perlu dihantar semula',
  partial: 'Bayaran separa',
  paid: 'Telah bayar',
};

const getStatusLabel = (order: LookupOrder) => {
  if (order.status === 'pending' && ['auto_priced', 'approved'].includes(order.pricing_status) && order.payment_status !== 'paid') {
    return 'Menunggu pembayaran';
  }

  return statusLabels[order.status] ?? order.status;
};

export default function LookupOrder() {
  const { order } = usePage<LookupOrderProps>().props;
  const { data, setData, post, processing, errors } = useForm({
    order_no: '',
    customer_phone: '',
  });
  const lookupError = (errors as typeof errors & { lookup?: string }).lookup;

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    post(route('orders.lookup'), { preserveScroll: true });
  };

  return (
    <FrontendLayout>
      <Head title="Semak Status Tempahan Sticker" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8 lg:py-16">
        <div className="mx-auto max-w-xl">
          <div className="text-center">
            <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
              <Search className="h-5 w-5" />
            </div>
            <h1 className="mt-5 text-3xl font-bold tracking-tight text-slate-900">Semak Status Order</h1>
            <p className="mt-2 text-sm leading-relaxed text-slate-500">
              Masukkan nombor order dan nombor telefon yang digunakan semasa tempahan.
            </p>
          </div>

          <form onSubmit={handleSubmit} className="mt-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            {lookupError && (
              <div className="mb-5 flex items-start gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
                <p>{lookupError}</p>
              </div>
            )}

            <div className="space-y-4">
              <div>
                <label htmlFor="lookup-order-no" className="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">
                  No. Order
                </label>
                <input
                  id="lookup-order-no"
                  type="text"
                  value={data.order_no}
                  onChange={(event) => setData('order_no', event.target.value.toUpperCase())}
                  placeholder="Contoh: ORD-20260826-ABC12"
                  autoComplete="off"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                  required
                />
                {errors.order_no && <p className="mt-1 text-xs text-rose-600">{errors.order_no}</p>}
              </div>

              <div>
                <label htmlFor="lookup-customer-phone" className="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">
                  No. Telefon
                </label>
                <input
                  id="lookup-customer-phone"
                  type="tel"
                  inputMode="tel"
                  value={data.customer_phone}
                  onChange={(event) => setData('customer_phone', event.target.value)}
                  placeholder="Contoh: 0112222333"
                  autoComplete="tel"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                  required
                />
                {errors.customer_phone && <p className="mt-1 text-xs text-rose-600">{errors.customer_phone}</p>}
              </div>
            </div>

            <button
              type="submit"
              disabled={processing}
              className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <Search className="h-4 w-4" />
              {processing ? 'Sedang menyemak...' : 'Semak Sekarang'}
            </button>
            <p className="mt-4 text-center text-xs leading-relaxed text-slate-400">
              Untuk privasi, hanya status order dan maklumat ringkas akan dipaparkan. Alamat penuh tidak dipaparkan.
            </p>
          </form>

          {order && (
            <section className="mt-6 overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-sm" aria-live="polite">
              <div className="border-b border-emerald-100 bg-emerald-50 px-5 py-4 sm:px-7">
                <div className="flex items-center gap-2 text-emerald-700">
                  <CheckCircle2 className="h-5 w-5" />
                  <p className="text-sm font-bold">Order dijumpai</p>
                </div>
                <p className="mt-1 text-xs text-emerald-700/80">{order.order_no} | {formatDateTime(order.created_at)}</p>
              </div>
              <div className="grid gap-3 p-5 sm:grid-cols-2 sm:p-7">
                <div className="rounded-2xl bg-slate-50 p-4">
                  <p className="text-xs font-semibold text-slate-500">Status order</p>
                  <p className="mt-1 text-sm font-bold text-slate-900">{getStatusLabel(order)}</p>
                </div>
                <div className="rounded-2xl bg-slate-50 p-4">
                  <div className="flex items-center gap-2">
                    <CreditCard className="h-4 w-4 text-slate-400" />
                    <p className="text-xs font-semibold text-slate-500">Status bayaran</p>
                  </div>
                  <p className="mt-1 text-sm font-bold text-slate-900">{paymentLabels[order.payment_status] ?? order.payment_status}</p>
                </div>
                {order.total > 0 && (
                  <div className="rounded-2xl bg-slate-50 p-4">
                    <p className="text-xs font-semibold text-slate-500">Jumlah order</p>
                    <p className="mt-1 text-sm font-bold text-brand-700">RM {order.total.toFixed(2)}</p>
                  </div>
                )}
                {order.tracking_no && (
                  <div className="rounded-2xl bg-sky-50 p-4">
                    <div className="flex items-center gap-2">
                      <Truck className="h-4 w-4 text-sky-600" />
                      <p className="text-xs font-semibold text-sky-700">No. tracking</p>
                    </div>
                    <p className="mt-1 text-sm font-bold text-sky-900">{order.tracking_no}</p>
                  </div>
                )}
              </div>
            </section>
          )}

          <div className="mt-6 text-center">
            <Link href={route('home')} className="text-sm font-semibold text-brand-600 hover:text-brand-700">
              Kembali ke halaman utama
            </Link>
          </div>
        </div>
      </div>
    </FrontendLayout>
  );
}
