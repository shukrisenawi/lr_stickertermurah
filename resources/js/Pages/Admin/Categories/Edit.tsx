import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Category {
  id: number;
  name: string;
  prefix: string | null;
  is_active: boolean;
}

interface CategoryEditProps {
  category: Category;
}

export default function CategoriesEdit({ category }: CategoryEditProps) {
  const { data, setData, put, processing, errors } = useForm({
    name: category.name,
    prefix: category.prefix || '',
    is_active: category.is_active,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.categories.update', category.id));
  };

  return (
    <AdminLayout>
      <Head title="Kemaskini Kategori" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.categories.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Kemaskini Kategori</h2>
          <p className="admin-page-copy">Sunting maklumat kategori.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5">
          <div>
            <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Kategori</label>
            <input
              id="name"
              type="text"
              value={data.name}
              onChange={(e) => setData('name', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            />
            {errors.name && <p className="mt-1 text-sm text-rose-600">{errors.name}</p>}
          </div>

          <div>
            <label htmlFor="prefix" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Prefix (untuk nama design auto)</label>
            <input
              id="prefix"
              type="text"
              maxLength={10}
              value={data.prefix}
              onChange={(e) => setData('prefix', e.target.value.toUpperCase())}
              className="w-full max-w-[120px] rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-mono text-slate-900 uppercase outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              placeholder="Cth: LP"
            />
          </div>

          <div className="flex items-center gap-3">
            <input
              id="is_active"
              type="checkbox"
              checked={data.is_active}
              onChange={(e) => setData('is_active', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
            />
            <label htmlFor="is_active" className="text-sm text-slate-700">Aktif</label>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.categories.index')} className="admin-btn-secondary flex-1 text-sm">
              Batal
            </Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Kemaskini'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
