import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { Star, Quote, Send, LogIn } from 'lucide-react';

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
  const { auth, testimonials, flash } = usePage<TestimonialsPageProps>().props;
  const isLoggedIn = !!auth.user;

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
            <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
              {isLoggedIn ? (
                <Link
                  href={route('member.testimonials.index')}
                  className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
                >
                  <Send className="h-4 w-4" />
                  Hantar Testimoni
                </Link>
              ) : (
                <>
                  <Link
                    href={route('member.testimonials.index')}
                    className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
                  >
                    <Send className="h-4 w-4" />
                    Hantar Testimoni Anda
                  </Link>
                  <Link
                    href={route('member.login')}
                    className="inline-flex items-center gap-2 rounded-full border-2 border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-brand-200 hover:bg-brand-50"
                  >
                    <LogIn className="h-4 w-4" />
                    Log Masuk Ahli
                  </Link>
                </>
              )}
            </div>
          </div>
        </section>

        {/* Flash message */}
        {flash?.success && (
          <div className="mx-auto max-w-[1280px] px-4 pb-8 lg:px-8">
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 flex items-center gap-3">
              <svg className="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
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
