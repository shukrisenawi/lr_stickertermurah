import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Design {
  id: number;
  name: string;
}

interface Size {
  id: number;
  name: string;
  shape: string | null;
}

interface Discount {
  id: number;
  name: string;
  sticker_design_id: number | null;
  sticker_size_id: number | null;
  min_qty: number;
  max_qty: number | null;
  type: string;
  value: number;
  is_active: boolean;
}

interface DiscountsEditProps {
  discount: Discount;
  designs: Design[];
  sizes: Size[];
}

export default function DiscountsEdit({ discount, designs, sizes }: DiscountsEditProps) {
  const { data, setData, put, processing, errors } = useForm({
    name: discount.name,
    sticker_design_id: discount.sticker_design_id ?? '',
    sticker_size_id: discount.sticker_size_id ?? '',
    min_qty: String(discount.min_qty),
    max_qty: discount.max_qty ? String(discount.max_qty) : '',
    type: discount.type,
    value: String(discount.value),
    is_active: discount.is_active,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.discounts.update', discount.id));
  };

  return (
    <AdminLayout>
      <Head title="Kemaskini Diskaun" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.discounts.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Kemaskini Diskaun</h2>
          <p className="admin-page-copy">Sunting maklumat diskaun.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5">
          <div>
            <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Diskaun</label>
            <input id="name" type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
            {errors.name && <p className="mt-1 text-sm text-rose-600">{errors.name}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label htmlFor="sticker_design_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Design Sticker</label>
              <select id="sticker_design_id" value={data.sticker_design_id} onChange={(e) => setData('sticker_design_id', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                <option value="">Semua Design</option>
                {designs.map((design) => (
                  <option key={design.id} value={design.id}>{design.name}</option>
                ))}
              </select>
              {errors.sticker_design_id && <p className="mt-1 text-sm text-rose-600">{errors.sticker_design_id}</p>}
            </div>
            <div>
              <label htmlFor="sticker_size_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Saiz Sticker</label>
              <select id="sticker_size_id" value={data.sticker_size_id} onChange={(e) => setData('sticker_size_id', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                <option value="">Semua Saiz</option>
                {sizes.map((size) => (
                  <option key={size.id} value={size.id}>{size.name}{size.shape ? ` (${size.shape})` : ''}</option>
                ))}
              </select>
              {errors.sticker_size_id && <p className="mt-1 text-sm text-rose-600">{errors.sticker_size_id}</p>}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label htmlFor="min_qty" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Kuantiti Min</label>
              <input id="min_qty" type="number" min="1" value={data.min_qty} onChange={(e) => setData('min_qty', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.min_qty && <p className="mt-1 text-sm text-rose-600">{errors.min_qty}</p>}
            </div>
            <div>
              <label htmlFor="max_qty" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Kuantiti Maks (opsional)</label>
              <input id="max_qty" type="number" min="1" value={data.max_qty} onChange={(e) => setData('max_qty', e.target.value)} placeholder="Biarkan kosong untuk tanpa had" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.max_qty && <p className="mt-1 text-sm text-rose-600">{errors.max_qty}</p>}
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Jenis Diskaun</label>
            <div className="flex items-center gap-4">
              <label className="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="type" value="fixed" checked={data.type === 'fixed'} onChange={(e) => setData('type', e.target.value)} className="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500" />
                <span className="text-sm text-slate-700">Nilai Tetap (RM)</span>
              </label>
              <label className="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="type" value="percentage" checked={data.type === 'percentage'} onChange={(e) => setData('type', e.target.value)} className="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500" />
                <span className="text-sm text-slate-700">Peratus (%)</span>
              </label>
            </div>
            {errors.type && <p className="mt-1 text-sm text-rose-600">{errors.type}</p>}
          </div>

          <div>
            <label htmlFor="value" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
              Nilai Diskaun {data.type === 'percentage' ? '(%)' : '(RM)'}
            </label>
            <div className="relative">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                {data.type === 'percentage' ? '%' : 'RM'}
              </span>
              <input id="value" type="number" step="0.01" min="0" value={data.value} onChange={(e) => setData('value', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
            </div>
            {errors.value && <p className="mt-1 text-sm text-rose-600">{errors.value}</p>}
          </div>

          <div className="flex items-center gap-3">
            <input id="is_active" type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
            <label htmlFor="is_active" className="text-sm text-slate-700">Aktif</label>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.discounts.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Kemaskini'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
