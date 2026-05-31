import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Category {
  id: number;
  name: string;
}

interface CreateProps {
  categories: Category[];
}

export default function DesignsCreate({ categories }: CreateProps) {
  const { data, setData, post, processing, errors } = useForm({
    category_id: '',
    is_active: true,
    image: null as File | null,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.designs.store'));
  };

  return (
    <AdminLayout>
      <Head title="Tambah Design" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.designs.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Tambah Design</h2>
          <p className="admin-page-copy">Tambah design sticker baharu.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5" encType="multipart/form-data">
          <div>
            <label htmlFor="category_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Kategori</label>
            <select
              id="category_id"
              value={data.category_id}
              onChange={(e) => setData('category_id', e.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            >
              <option value="">Pilih Kategori</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.name}
                </option>
              ))}
            </select>
            {errors.category_id && <p className="mt-1 text-sm text-rose-600">{errors.category_id}</p>}
          </div>

          <div>
            <label htmlFor="image" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Gambar</label>
            <input
              id="image"
              type="file"
              accept="image/*"
              onChange={(e) => setData('image', e.target.files?.[0] || null)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
            />
            {errors.image && <p className="mt-1 text-sm text-rose-600">{errors.image}</p>}
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
            <Link href={route('admin.designs.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
