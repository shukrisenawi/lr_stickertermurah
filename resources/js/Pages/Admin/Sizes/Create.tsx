import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

export default function SizesCreate() {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    width_mm: '',
    height_mm: '',
    price: '',
    is_active: true,
    is_default: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.sizes.store'));
  };

  return (
    <AdminLayout>
      <Head title="Tambah Saiz" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.sizes.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Tambah Saiz</h2>
          <p className="admin-page-copy">Tambah saiz sticker baharu.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5">
          <div>
            <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Saiz</label>
            <input id="name" type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
            {errors.name && <p className="mt-1 text-sm text-rose-600">{errors.name}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label htmlFor="width_mm" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Lebar (mm)</label>
              <input id="width_mm" type="number" step="0.01" value={data.width_mm} onChange={(e) => setData('width_mm', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.width_mm && <p className="mt-1 text-sm text-rose-600">{errors.width_mm}</p>}
            </div>
            <div>
              <label htmlFor="height_mm" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Tinggi (mm)</label>
              <input id="height_mm" type="number" step="0.01" value={data.height_mm} onChange={(e) => setData('height_mm', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              {errors.height_mm && <p className="mt-1 text-sm text-rose-600">{errors.height_mm}</p>}
            </div>
          </div>

          <div>
            <label htmlFor="price" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Harga (RM)</label>
            <input id="price" type="number" step="0.01" value={data.price} onChange={(e) => setData('price', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
            {errors.price && <p className="mt-1 text-sm text-rose-600">{errors.price}</p>}
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
          </div>

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.sizes.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
