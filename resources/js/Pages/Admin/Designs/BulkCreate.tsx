import { useState } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Upload, X } from 'lucide-react';

interface Category {
  id: number;
  name: string;
  prefix: string | null;
}

interface BulkCreateProps {
  categories: Category[];
}

export default function BulkCreate({ categories }: BulkCreateProps) {
  const { data, setData, post, processing, errors } = useForm({
    category_id: '',
    images: [] as File[],
  });

  const [previews, setPreviews] = useState<string[]>([]);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    setData('images', files);
    setPreviews(files.map((f) => URL.createObjectURL(f)));
  };

  const removeImage = (index: number) => {
    const newFiles = data.images.filter((_, i) => i !== index);
    setData('images', newFiles);
    URL.revokeObjectURL(previews[index]);
    setPreviews(previews.filter((_, i) => i !== index));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.designs.bulk.store'));
  };

  return (
    <AdminLayout>
      <Head title="Muat Naik Pukal" />
      <div className="space-y-6">
        <Link href={route('admin.designs.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Muat Naik Pukal</h2>
          <p className="admin-page-copy">Upload beberapa design sekaligus. Nama auto-dijana guna prefix kategori.</p>
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
                  {cat.name} {cat.prefix ? `(Prefix: ${cat.prefix})` : ''}
                </option>
              ))}
            </select>
            {errors.category_id && <p className="mt-1 text-sm text-rose-600">{errors.category_id}</p>}
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
              Gambar (Pilih Banyak)
            </label>
            <label className="flex min-h-[140px] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-8 transition hover:border-brand-300 hover:bg-brand-50/30">
              <Upload className="h-8 w-8 text-slate-400" />
              <p className="mt-2 text-sm font-medium text-slate-600">Klik untuk pilih gambar</p>
              <p className="mt-1 text-xs text-slate-400">JPG, PNG, WEBP • Maks 10MB setiap fail</p>
              <input
                type="file"
                accept="image/*"
                multiple
                onChange={handleFileChange}
                className="hidden"
              />
            </label>
            {errors.images && <p className="mt-1 text-sm text-rose-600">{errors.images}</p>}
          </div>

          {previews.length > 0 && (
            <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-5">
              {previews.map((preview, i) => (
                <div key={i} className="group relative aspect-square overflow-hidden rounded-xl border border-slate-200">
                  <img src={preview} alt={`Preview ${i + 1}`} className="h-full w-full object-cover" />
                  <button
                    type="button"
                    onClick={() => removeImage(i)}
                    className="absolute right-1 top-1 rounded-lg bg-black/60 p-1 text-white opacity-0 transition group-hover:opacity-100"
                  >
                    <X className="h-3.5 w-3.5" />
                  </button>
                  <div className="absolute bottom-0 left-0 right-0 bg-black/50 px-2 py-1 text-[10px] text-white">
                    {data.images[i]?.name.substring(0, 18)}
                  </div>
                </div>
              ))}
            </div>
          )}

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.designs.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              <Upload className="h-4 w-4" />
              {processing ? 'Memuat Naik...' : `Muat Naik (${data.images.length})`}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
