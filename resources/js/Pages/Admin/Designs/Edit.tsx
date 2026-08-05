import AdminLayout from '@/Components/Layouts/AdminLayout';
import HashtagInput from '@/Components/HashtagInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface Category {
  id: number;
  name: string;
}

interface Design {
  id: number;
  name: string;
  category_id: number;
  is_active: boolean;
  tags: string[] | null;
}

interface DesignEditProps {
  design: Design;
  categories: Category[];
}

export default function DesignsEdit({ design, categories }: DesignEditProps) {
  const { data, setData, post, processing, errors } = useForm({
    name: design.name,
    category_id: String(design.category_id),
    is_active: design.is_active,
    image: null as File | null,
    tags: (design.tags || []) as string[],
    _method: 'put',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.designs.update', design.id));
  };

  return (
    <AdminLayout>
      <Head title="Kemaskini Design" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.designs.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Kemaskini Design</h2>
          <p className="admin-page-copy">Sunting maklumat design.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5" encType="multipart/form-data">
          <div>
            <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Design</label>
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
            <label htmlFor="image" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Gambar Baharu (pilihan)</label>
            <input
              id="image"
              type="file"
              accept="image/*"
              onChange={(e) => setData('image', e.target.files?.[0] || null)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition"
            />
            <p className="mt-1 text-xs text-slate-400">Upload baharu akan menggantikan kedua-dua versi paparan dan thumbnail mobile ber-watermark.</p>
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

          <HashtagInput
            label="Hashtag"
            value={data.tags}
            onChange={(tags) => setData('tags', tags)}
            searchUrl={route('admin.designs.tags.search')}
            error={errors.tags}
          />

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.designs.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Kemaskini'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
