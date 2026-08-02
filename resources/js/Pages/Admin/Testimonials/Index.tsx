import { useState } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Star, Pencil, Trash2, CheckCircle, XCircle, Image as ImageIcon } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Testimonial {
  id: number;
  name: string;
  business: string | null;
  text: string;
  image_path: string | null;
  image_url: string | null;
  stars: number;
  is_approved: boolean;
  approved_at: string | null;
  created_at: string;
  approved_by: { name: string } | null;
  user: { name: string; email: string } | null;
}

interface TestimonialsIndexProps {
  testimonials: {
    data: Testimonial[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function TestimonialsIndex({ testimonials }: TestimonialsIndexProps) {
  const { post, delete: destroy } = useForm();
  const [deleting, setDeleting] = useState<number | null>(null);

  const handleApprove = (id: number) => {
    post(route('admin.testimonials.approve', id));
  };

  const handleReject = (id: number) => {
    post(route('admin.testimonials.reject', id));
  };

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam testimoni ini?')) {
      setDeleting(id);
      destroy(route('admin.testimonials.destroy', id));
    }
  };

  return (
    <AdminLayout>
      <Head title="Urus Testimoni" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Testimoni Pelanggan</h2>
            <p className="admin-page-copy">Lulus, sunting dan padam testimoni pelanggan.</p>
          </div>
        </div>

        {testimonials.data.length === 0 ? (
          <div className="rounded-2xl border border-slate-200 bg-white py-20 text-center">
            <Star className="mx-auto h-16 w-16 text-slate-300" />
            <p className="mt-4 text-lg font-semibold text-slate-600">Tiada Testimoni</p>
            <p className="mt-1 text-sm text-slate-400">Belum ada testimoni yang dihantar lagi.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
            {testimonials.data.map((t) => (
              <div
                key={t.id}
                className="admin-flat-card p-5 transition hover:shadow-md"
              >
              <div className="flex flex-col gap-4 lg:flex-row lg:items-start">
                  {/* Image */}
                  <div className="shrink-0">
                    {t.image_url ? (
                      <img
                        src={t.image_url}
                        alt={t.name}
                        className="h-20 w-20 rounded-xl object-cover"
                      />
                    ) : (
                      <div className="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100">
                        <ImageIcon className="h-8 w-8 text-slate-300" />
                      </div>
                    )}
                  </div>

                  {/* Content */}
                  <div className="flex-1 min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                      <h3 className="text-base font-bold text-slate-900">{t.name}</h3>
                      {t.business && (
                        <span className="text-xs text-slate-500">({t.business})</span>
                      )}
                      <span
                        className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${
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
                            <XCircle className="h-3 w-3" />
                            Belum Diluluskan
                          </>
                        )}
                      </span>
                    </div>

                    <div className="mt-1 flex gap-0.5">
                      {Array.from({ length: t.stars }).map((_, i) => (
                        <Star key={`star-${t.id}-${i}`} className="h-3.5 w-3.5 fill-amber-400 text-amber-400" />
                      ))}
                    </div>

                    <p className="mt-2 text-sm leading-relaxed text-slate-600">{t.text}</p>

                    <div className="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                      <span>Dihantar: {formatDate(t.created_at)}</span>
                      {t.approved_at && (
                        <span>Diluluskan: {formatDate(t.approved_at)}</span>
                      )}
                      {t.approved_by && (
                        <span>Oleh: {t.approved_by.name}</span>
                      )}
                      {t.user && (
                        <span>Pengguna: {t.user.name}</span>
                      )}
                    </div>
                  </div>

                  {/* Actions */}
                  <div className="flex shrink-0 items-center gap-2">
                    {!t.is_approved ? (
                      <button
                        type="button"
                        onClick={() => handleApprove(t.id)}
                        className="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                      >
                        <CheckCircle className="h-3.5 w-3.5" />
                        Luluskan
                      </button>
                    ) : (
                      <button
                        type="button"
                        onClick={() => handleReject(t.id)}
                        className="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100"
                      >
                        <XCircle className="h-3.5 w-3.5" />
                        Tolak
                      </button>
                    )}
                    <Link
                      href={route('admin.testimonials.edit', t.id)}
                      className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                      <Pencil className="h-3.5 w-3.5" />
                      Sunting
                    </Link>
                    <button
                      type="button"
                      onClick={() => handleDelete(t.id)}
                      disabled={deleting === t.id}
                      className="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 disabled:opacity-50"
                    >
                      <Trash2 className="h-3.5 w-3.5" />
                      Padam
                    </button>
                  </div>
                </div>
              </div>
            ))}

            {/* Pagination */}
            {testimonials.links.length > 3 && (
              <div className="flex items-center justify-center gap-2 pt-4 md:col-span-2">
                {testimonials.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100'}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span
                      key={i}
                      className="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  )
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
