import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, useForm } from '@inertiajs/react';

export default function OrderForm() {
  const { data, setData, post, processing, errors } = useForm({
    customer_name: '',
    customer_phone: '',
    customer_address: '',
    material: 'mirrorcote',
    items: [] as Array<{ design_id: number; size_id: number; quantity: number }>,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('orders.store'));
  };

  return (
    <FrontendLayout>
      <Head title="Tempah Sticker" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="mx-auto max-w-2xl">
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold text-slate-900">Tempah Sticker</h1>
            <p className="mt-2 text-slate-500">Isi borang di bawah untuk membuat tempahan.</p>
          </div>

          <form onSubmit={handleSubmit} className="frontend-flat-card p-8 space-y-6">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama Penuh</label>
              <input
                type="text"
                value={data.customer_name}
                onChange={(e) => setData('customer_name', e.target.value)}
                className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="Nama anda"
              />
              {errors.customer_name && <p className="mt-1 text-sm text-rose-600">{errors.customer_name}</p>}
            </div>

            <div>
              <label className="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. Telefon</label>
              <input
                type="text"
                value={data.customer_phone}
                onChange={(e) => setData('customer_phone', e.target.value)}
                className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="012-3456789"
              />
              {errors.customer_phone && <p className="mt-1 text-sm text-rose-600">{errors.customer_phone}</p>}
            </div>

            <div>
              <label className="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alamat Penghantaran</label>
              <textarea
                value={data.customer_address}
                onChange={(e) => setData('customer_address', e.target.value)}
                rows={3}
                className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="Alamat lengkap"
              />
              {errors.customer_address && <p className="mt-1 text-sm text-rose-600">{errors.customer_address}</p>}
            </div>

            <div className="flex items-center justify-between pt-4 border-t border-slate-100">
              <button
                type="button"
                onClick={() => window.history.back()}
                className="frontend-btn-secondary"
              >
                Kembali
              </button>
              <button
                type="submit"
                disabled={processing}
                className="frontend-btn-primary disabled:opacity-50"
              >
                {processing ? 'Menghantar...' : 'Hantar Tempahan'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </FrontendLayout>
  );
}
