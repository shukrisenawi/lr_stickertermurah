import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Size {
  id: number;
  name: string;
  width_cm: number;
  height_cm: number;
  shape: string | null;
  qty_per_a3: number | null;
  is_active: boolean;
  is_default: boolean;
  show: boolean;
}

interface SizeEditProps {
  size: Size;
}

export default function SizesEdit({ size }: SizeEditProps) {
  const { data, setData, put, processing, errors } = useForm({
    name: size.name,
    width_cm: String(size.width_cm),
    height_cm: String(size.height_cm),
    shape: size.shape ?? '',
    qty_per_a3: size.qty_per_a3 ? String(size.qty_per_a3) : '',
    is_active: size.is_active,
    is_default: size.is_default,
    show: size.show,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.sizes.update', size.id));
  };

  return (
    <AdminLayout>
      <Head title="Kemaskini Saiz" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.sizes.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Kemaskini Saiz</h2>
          <p className="admin-page-copy">Sunting maklumat saiz.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5">
          <div>
            <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Saiz</label>
            <input id="name" type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
            {errors.name && <p className="mt-1 text-sm text-rose-600">{errors.name}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label htmlFor="width_cm" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Lebar (cm)</label>
              <input id="width_cm" type="number" min="0.01" step="0.01" value={data.width_cm} onChange={(e) => setData('width_cm', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.width_cm && <p className="mt-1 text-sm text-rose-600">{errors.width_cm}</p>}
            </div>
            <div>
              <label htmlFor="height_cm" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Tinggi (cm)</label>
              <input id="height_cm" type="number" min="0.01" step="0.01" value={data.height_cm} onChange={(e) => setData('height_cm', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.height_cm && <p className="mt-1 text-sm text-rose-600">{errors.height_cm}</p>}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label htmlFor="shape" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Bentuk</label>
              <select id="shape" value={data.shape} onChange={(e) => setData('shape', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                <option value="">Pilih bentuk</option>
                <option value="Petak">Petak</option>
                <option value="Segi Empat">Segi Empat</option>
                <option value="Bulat">Bulat</option>
                <option value="Oval">Oval</option>
                <option value="Bebas">Bebas / Custom</option>
              </select>
              {errors.shape && <p className="mt-1 text-sm text-rose-600">{errors.shape}</p>}
            </div>
            <div>
              <label htmlFor="qty_per_a3" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Kuantiti per A3</label>
              <input id="qty_per_a3" type="number" min="1" value={data.qty_per_a3} onChange={(e) => setData('qty_per_a3', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.qty_per_a3 && <p className="mt-1 text-sm text-rose-600">{errors.qty_per_a3}</p>}
            </div>
          </div>

          <div className="flex items-center gap-6">
            <div className="flex items-center gap-3">
              <input id="is_active" type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
              <label htmlFor="is_active" className="text-sm text-slate-700">Aktif</label>
            </div>
            <div className="flex items-center gap-3">
              <input id="is_default" type="checkbox" checked={data.is_default} onChange={(e) => setData('is_default', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
              <label htmlFor="is_default" className="text-sm text-slate-700">Default</label>
            </div>
            <div className="flex items-center gap-3">
              <input id="show" type="checkbox" checked={data.show} onChange={(e) => setData('show', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
              <label htmlFor="show" className="text-sm text-slate-700">Papar dalam dropdown perbandingan harga</label>
            </div>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.sizes.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Kemaskini'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
