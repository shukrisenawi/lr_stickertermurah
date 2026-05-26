import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useState } from 'react';
import { Star, Quote, Send, Image as ImageIcon, CheckCircle } from 'lucide-react';

interface TestimonialsPageProps extends PageProps {
  testimonials: Array<{
    id: number;
    name: string;
    business: string | null;
    text: string;
    image_url: string | null;
    stars: number;
  }>;
}

export default function TestimonialsPage() {
  const { testimonials, flash } = usePage<TestimonialsPageProps>().props;
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    business: '',
    text: '',
    stars: 5,
    image: null as File | null,
  });

  const [showForm, setShowForm] = useState(false);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setData('image', file);
    if (file) {
      setPreviewUrl(URL.createObjectURL(file));
    } else {
      setPreviewUrl(null);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('testimonials.store'), {
      onSuccess: () => {
        reset();
        setPreviewUrl(null);
        setShowForm(false);
      },
    });
  };

  const defaultAvatar = (name: string) =>
    `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(name)}`;

  return (
    <FrontendLayout>
      <Head title="Testimoni Pelanggan" />
      <div className="frontend-shell">
        {/* Header */}
        <section className="py-16 text-center">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-4xl">
              Testimoni Pelanggan
            </h1>
            <p className="mt-3 text-slate-500">
              Lihat apa yang pelanggan kami katakan tentang perkhidmatan kami.
            </p>
            <button
              type="button"
              onClick={() => setShowForm(!showForm)}
              className="mt-6 inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
            >
              <Send className="h-4 w-4" />
              {showForm ? 'Tutup Borang' : 'Hantar Testimoni Anda'}
            </button>
          </div>
        </section>

        {/* Submit Form */}
        {showForm && (
          <section className="pb-12">
            <div className="mx-auto max-w-xl px-4 lg:px-8">
              <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-bold text-slate-900">Hantar Testimoni</h2>
                <p className="text-xs text-slate-500">Testimoni anda akan dipaparkan selepas diluluskan oleh admin.</p>

                <form onSubmit={handleSubmit} className="mt-5 space-y-4">
                  <div>
                    <label htmlFor="t-name" className="frontend-shell-label">Nama</label>
                    <input
                      id="t-name"
                      type="text"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      className="mt-1"
                      placeholder="Nama anda"
                    />
                    {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
                  </div>

                  <div>
                    <label htmlFor="t-business" className="frontend-shell-label">Perniagaan / Tajuk (Pilihan)</label>
                    <input
                      id="t-business"
                      type="text"
                      value={data.business}
                      onChange={(e) => setData('business', e.target.value)}
                      className="mt-1"
                      placeholder="cth: (Perniagaan Kek)"
                    />
                  </div>

                  <div>
                    <label htmlFor="t-text" className="frontend-shell-label">Ulasan</label>
                    <textarea
                      id="t-text"
                      rows={3}
                      value={data.text}
                      onChange={(e) => setData('text', e.target.value)}
                      className="mt-1"
                      placeholder="Ceritakan pengalaman anda..."
                    />
                    {errors.text && <p className="mt-1 text-xs text-rose-600">{errors.text}</p>}
                  </div>

                  <div>
                    <label htmlFor="t-stars" className="frontend-shell-label">Penilaian</label>
                    <select
                      id="t-stars"
                      value={data.stars}
                      onChange={(e) => setData('stars', parseInt(e.target.value))}
                      className="mt-1 w-auto"
                    >
                      {[5, 4, 3, 2, 1].map((n) => (
                        <option key={n} value={n}>{n} Bintang</option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label htmlFor="t-image" className="frontend-shell-label">Gambar (Pilihan)</label>
                    <div className="mt-1 flex items-center gap-4">
                      {previewUrl ? (
                        <img src={previewUrl} alt="Preview" className="h-16 w-16 rounded-lg object-cover" />
                      ) : (
                        <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100">
                          <ImageIcon className="h-6 w-6 text-slate-300" />
                        </div>
                      )}
                      <input
                        id="t-image"
                        type="file"
                        accept="image/*"
                        onChange={handleImageChange}
                        className="text-sm"
                      />
                    </div>
                    {errors.image && <p className="mt-1 text-xs text-rose-600">{errors.image}</p>}
                  </div>

                  <button
                    type="submit"
                    disabled={processing}
                    className="frontend-btn-primary w-full"
                  >
                    <Send className="h-4 w-4" />
                    {processing ? 'Menghantar...' : 'Hantar Testimoni'}
                  </button>
                </form>
              </div>
            </div>
          </section>
        )}

        {/* Flash message */}
        {flash.success && (
          <div className="mx-auto max-w-[1280px] px-4 pb-8 lg:px-8">
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 flex items-center gap-3">
              <CheckCircle className="h-5 w-5 text-emerald-600" />
              <p className="text-sm font-medium text-emerald-800">{flash.success}</p>
            </div>
          </div>
        )}

        {/* Testimonials Grid */}
        <section className="pb-20">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            {testimonials.length === 0 ? (
              <div className="rounded-2xl border border-slate-200 bg-white py-20 text-center">
                <Quote className="mx-auto h-16 w-16 text-slate-300" />
                <p className="mt-4 text-lg font-semibold text-slate-600">Tiada Testimoni Lagi</p>
                <p className="mt-1 text-sm text-slate-400">Jadilah yang pertama memberikan testimoni!</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {testimonials.map((t) => (
                  <div
                    key={`t-${t.id}`}
                    className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition hover:shadow-md"
                  >
                    <Quote className="h-6 w-6 text-brand-300" />
                    <div className="mt-3 flex gap-0.5">
                      {Array.from({ length: t.stars }).map((_, i) => (
                        <Star key={`star-${t.id}-${i}`} className="h-4 w-4 fill-amber-400 text-amber-400" />
                      ))}
                    </div>
                    <p className="mt-3 text-sm leading-relaxed text-slate-600">{t.text}</p>

                    {t.image_url && (
                      <div className="mt-4">
                        <img
                          src={t.image_url}
                          alt={`Gambar oleh ${t.name}`}
                          className="h-40 w-full rounded-xl object-cover"
                        />
                      </div>
                    )}

                    <div className="mt-5 flex items-center gap-3">
                      <img
                        src={t.image_url ? t.image_url : defaultAvatar(t.name)}
                        alt={t.name}
                        className="h-10 w-10 rounded-full bg-slate-100 object-cover"
                      />
                      <div>
                        <p className="text-sm font-bold text-slate-900">– {t.name}</p>
                        {t.business && <p className="text-xs text-slate-500">{t.business}</p>}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </section>
      </div>
    </FrontendLayout>
  );
}
