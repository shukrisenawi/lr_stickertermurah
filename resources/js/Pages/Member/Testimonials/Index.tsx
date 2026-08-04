import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useState } from 'react';
import { Star, Send, Image as ImageIcon, CheckCircle, Clock, Quote } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface MemberTestimonialsPageProps extends PageProps {
  myTestimonials: Array<{
    id: number;
    name: string;
    business: string | null;
    text: string;
    image_url: string | null;
    stars: number;
    is_approved: boolean;
    created_at: string;
  }>;
  allTestimonials: Array<{
    id: number;
    name: string;
    business: string | null;
    text: string;
    image_url: string | null;
    stars: number;
  }>;
}

export default function MemberTestimonialsIndex() {
  const { myTestimonials, flash } = usePage<MemberTestimonialsPageProps>().props;
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
    post(route('member.testimonials.store'), {
      onSuccess: () => {
        reset();
        setPreviewUrl(null);
        setShowForm(false);
      },
    });
  };

  return (
    <MemberLayout>
      <Head title="Testimoni Saya" />
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-[1280px] px-4 py-10 lg:px-8">
          <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">Testimoni Saya</h1>

          {/* Flash message */}
          {flash?.success && (
            <div className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 flex items-center gap-3">
              <CheckCircle className="h-5 w-5 text-emerald-600" />
              <p className="text-sm font-medium text-emerald-800">{flash.success}</p>
            </div>
          )}

          {/* My Testimonials */}
          <section className="mt-8">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-bold text-slate-900">Testimoni Anda</h2>
              <button
                type="button"
                onClick={() => setShowForm(!showForm)}
                className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
              >
                <Send className="h-4 w-4" />
                {showForm ? 'Tutup' : 'Hantar Testimoni'}
              </button>
            </div>

            {showForm && (
              <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 className="text-base font-bold text-slate-900">Borang Testimoni</h3>
                <p className="text-xs text-slate-500">Testimoni anda akan dipaparkan selepas diluluskan oleh admin.</p>

                <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                  <div>
                    <label htmlFor="mt-name" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama</label>
                    <input
                      id="mt-name"
                      type="text"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Nama anda"
                    />
                    {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
                  </div>

                  <div>
                    <label htmlFor="mt-business" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Perniagaan / Tajuk (Pilihan)</label>
                    <input
                      id="mt-business"
                      type="text"
                      value={data.business}
                      onChange={(e) => setData('business', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="cth: (Perniagaan Kek)"
                    />
                  </div>

                  <div>
                    <label htmlFor="mt-text" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ulasan</label>
                    <textarea
                      id="mt-text"
                      rows={3}
                      value={data.text}
                      onChange={(e) => setData('text', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Ceritakan pengalaman anda..."
                    />
                    {errors.text && <p className="mt-1 text-xs text-rose-600">{errors.text}</p>}
                  </div>

                  <div>
                    <label htmlFor="mt-stars" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Penilaian</label>
                    <select
                      id="mt-stars"
                      value={data.stars}
                      onChange={(e) => setData('stars', parseInt(e.target.value))}
                      className="mt-1 w-auto rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      {[5, 4, 3, 2, 1].map((n) => (
                        <option key={n} value={n}>{n} Bintang</option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label htmlFor="mt-image" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Gambar (Pilihan)</label>
                    <div className="mt-1 flex items-center gap-4">
                      {previewUrl ? (
                        <img src={previewUrl} alt="Preview" className="h-16 w-16 rounded-lg object-cover" />
                      ) : (
                        <div className="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100">
                          <ImageIcon className="h-6 w-6 text-slate-300" />
                        </div>
                      )}
                      <input
                        id="mt-image"
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
                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-500 shadow-lg shadow-brand-600/20 disabled:opacity-50"
                  >
                    <Send className="h-4 w-4" />
                    {processing ? 'Menghantar...' : 'Hantar Testimoni'}
                  </button>
                </form>
              </div>
            )}

            {myTestimonials.length === 0 ? (
              <div className="mt-4 rounded-2xl border border-slate-200 bg-white py-12 text-center">
                <Quote className="mx-auto h-12 w-12 text-slate-300" />
                <p className="mt-3 text-sm text-slate-500">Anda belum menghantar sebarang testimoni.</p>
              </div>
            ) : (
              <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                {myTestimonials.map((t) => (
                  <div key={`my-t-${t.id}`} className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div className="flex items-center justify-between">
                      <div className="flex gap-0.5">
                        {Array.from({ length: t.stars }).map((_, i) => (
                          <Star key={`star-${t.id}-${i}`} className="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                        ))}
                      </div>
                      <span
                        className={`inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase ${
                          t.is_approved
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-amber-100 text-amber-700'
                        }`}
                      >
                        {t.is_approved ? (
                          <>
                            <CheckCircle className="h-3 w-3" />
                            Diluluskan
                          </>
                        ) : (
                          <>
                            <Clock className="h-3 w-3" />
                            Belum Diluluskan
                          </>
                        )}
                      </span>
                    </div>
                    <p className="mt-2 text-sm leading-relaxed text-slate-600">{t.text}</p>
                    {t.image_url && (
                      <div className="mt-3">
                        <img
                          src={t.image_url}
                          alt="Gambar testimoni"
                          loading="lazy"
                          decoding="async"
                          width="640"
                          height="256"
                          className="h-32 w-full rounded-xl object-cover"
                        />
                      </div>
                    )}
                    <p className="mt-2 text-xs text-slate-400">
                      Dihantar: {formatDate(t.created_at)}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </section>


        </div>
      </div>
    </MemberLayout>
  );
}
