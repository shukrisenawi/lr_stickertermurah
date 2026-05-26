import { useState, useEffect } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Image as ImageIcon } from 'lucide-react';

interface TestimonialEditProps {
  testimonial: {
    id: number;
    name: string;
    business: string | null;
    text: string;
    image_path: string | null;
    image_url: string | null;
    stars: number;
    is_approved: boolean;
  };
}

export default function TestimonialsEdit({ testimonial }: TestimonialEditProps) {
  const { data, setData, post, processing, errors } = useForm({
    _method: 'PUT',
    name: testimonial.name,
    business: testimonial.business ?? '',
    text: testimonial.text,
    stars: testimonial.stars,
    is_approved: testimonial.is_approved,
    image: null as File | null,
  });

  const [previewUrl, setPreviewUrl] = useState<string | null>(testimonial.image_url);

  useEffect(() => {
    if (data.image) {
      const url = URL.createObjectURL(data.image);
      setPreviewUrl(url);
      return () => URL.revokeObjectURL(url);
    }
  }, [data.image]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.testimonials.update', testimonial.id));
  };

  return (
    <AdminLayout>
      <Head title="Sunting Testimoni" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Sunting Testimoni</h2>
            <p className="admin-page-copy">Kemaskini maklumat testimoni pelanggan.</p>
          </div>
          <a
            href={route('admin.testimonials.index')}
            className="admin-btn-secondary"
          >
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </a>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 max-w-2xl space-y-6">
          {/* Name */}
          <div>
            <label htmlFor="name">Nama</label>
            <input
              id="name"
              type="text"
              value={data.name}
              onChange={(e) => setData('name', e.target.value)}
              className="mt-1.5"
            />
            {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
          </div>

          {/* Business */}
          <div>
            <label htmlFor="business">Perniagaan / Tajuk</label>
            <input
              id="business"
              type="text"
              value={data.business}
              onChange={(e) => setData('business', e.target.value)}
              placeholder="cth: (Perniagaan Kek)"
              className="mt-1.5"
            />
            {errors.business && <p className="mt-1 text-xs text-rose-600">{errors.business}</p>}
          </div>

          {/* Text */}
          <div>
            <label htmlFor="text">Ulasan</label>
            <textarea
              id="text"
              rows={4}
              value={data.text}
              onChange={(e) => setData('text', e.target.value)}
              className="mt-1.5"
            />
            {errors.text && <p className="mt-1 text-xs text-rose-600">{errors.text}</p>}
          </div>

          {/* Stars */}
          <div>
            <label htmlFor="stars">Bintang</label>
            <select
              id="stars"
              value={data.stars}
              onChange={(e) => setData('stars', parseInt(e.target.value))}
              className="mt-1.5 w-auto"
            >
              {[1, 2, 3, 4, 5].map((n) => (
                <option key={n} value={n}>{n} Bintang</option>
              ))}
            </select>
            {errors.stars && <p className="mt-1 text-xs text-rose-600">{errors.stars}</p>}
          </div>

          {/* Approved */}
          <div className="flex items-center gap-3">
            <input
              id="is_approved"
              type="checkbox"
              checked={data.is_approved}
              onChange={(e) => setData('is_approved', e.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
            />
            <label htmlFor="is_approved" className="!text-sm !normal-case !tracking-normal !font-medium text-slate-700">
              Diluluskan
            </label>
          </div>

          {/* Image */}
          <div>
            <label htmlFor="image">Gambar</label>
            <div className="mt-1.5 flex items-center gap-4">
              {previewUrl ? (
                <img src={previewUrl} alt="Preview" className="h-24 w-24 rounded-xl object-cover" />
              ) : (
                <div className="flex h-24 w-24 items-center justify-center rounded-xl bg-slate-100">
                  <ImageIcon className="h-8 w-8 text-slate-300" />
                </div>
              )}
              <div>
                <input
                  id="image"
                  type="file"
                  accept="image/*"
                  onChange={(e) => setData('image', e.target.files?.[0] ?? null)}
                  className="text-sm"
                />
                <p className="mt-1 text-xs text-slate-400">JPG, PNG, WEBP. Maks 10MB.</p>
              </div>
            </div>
            {errors.image && <p className="mt-1 text-xs text-rose-600">{errors.image}</p>}
          </div>

          {/* Submit */}
          <div className="pt-2">
            <button
              type="submit"
              disabled={processing}
              className="admin-btn-primary"
            >
              <Save className="h-4 w-4" />
              {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
