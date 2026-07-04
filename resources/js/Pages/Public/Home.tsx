import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useState, useMemo, useCallback } from 'react';
import { ArrowRight, X, Zap, Tag, LogIn, Image as ImageIcon } from 'lucide-react';

interface HomePageProps extends PageProps {
  categories: Array<{
    id: number;
    name: string;
    prefix: string | null;
    designs: Array<{
      id: number;
      name: string;
      image_url: string | null;
      category_id: number;
    }>;
  }>;
  allDesigns: Array<{
    id: number;
    name: string;
    image_url: string | null;
    category: { id: number; name: string } | null;
    category_id: number;
  }>;
  testimonials: Array<{
    id: number;
    name: string;
    business: string | null;
    text: string;
    image_url: string | null;
    stars: number;
  }>;
}

export default function Home() {
  const { app, categories, allDesigns } = usePage<HomePageProps>().props;
  const [activeCategory, setActiveCategory] = useState<number | 'all'>('all');

  const categoryTabs = useMemo(() => {
    const tabs: Array<{ id: number | 'all'; name: string; count: number }> = [
      { id: 'all', name: 'Semua', count: allDesigns.length },
    ];
    for (const c of categories) {
      tabs.push({ id: c.id, name: c.name, count: c.designs.length });
    }
    return tabs;
  }, [categories, allDesigns]);

  const filteredDesigns = useMemo(() => {
    if (activeCategory === 'all') {
      return allDesigns;
    }
    return allDesigns.filter((d) => d.category_id === activeCategory);
  }, [activeCategory, allDesigns]);

  const resetFilters = useCallback(() => {
    setActiveCategory('all');
  }, []);

  return (
    <FrontendLayout hideNavbar>
      <Head title="Pilih Design Sticker Mirrorcote Premium Malaysia" />

      {/* ========== TOP NAV MINI ========== */}
      <header className="sticky top-0 z-50 border-b border-slate-100 bg-white/90 backdrop-blur-md">
        <div className="mx-auto flex h-16 max-w-[1400px] items-center justify-between px-4 lg:px-8">
          <Link href="/" className="flex items-center gap-2 shrink-0">
            <img src={app.logo_url} alt="StickerTermurah" className="h-10 w-auto" />
          </Link>
          <div className="flex items-center gap-2">
            <Link
              href={route('admin.login')}
              className="inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
            >
              <LogIn className="h-3.5 w-3.5" />
              <span className="hidden sm:inline">Admin</span>
            </Link>
            <a
              href="https://wa.me/601169409606"
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-700 shadow-md shadow-brand-600/20"
            >
              <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <span className="hidden sm:inline">011-69409606</span>
              <span className="sm:hidden">WhatsApp</span>
            </a>
          </div>
        </div>
      </header>

      {/* ========== HERO MINIMAL ========== */}
      <section className="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-brand-50/40 via-white to-white">
        <div className="mx-auto max-w-[1400px] px-4 py-10 lg:px-8 lg:py-16">
          <div className="mx-auto max-w-2xl text-center">
            <div className="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700">
              <Zap className="h-3 w-3" />
              Sticker Mirrorcote Premium
            </div>
            <h1 className="mt-5 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 lg:text-5xl">
              Pilih Design Sticker
              <br />
              <span className="text-brand-600">Untuk Jenama Anda</span>
            </h1>
            <p className="mt-4 text-sm text-slate-500 lg:text-base">
              {allDesigns.length}+ design premium sedia untuk dipilih. Klik pada design untuk mula tempahan.
            </p>
          </div>
        </div>
      </section>

      {/* ========== FILTERS STICKY ========== */}
      <section className="sticky top-16 z-40 border-b border-slate-100 bg-white/95 backdrop-blur-md">
        <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
          <div className="flex items-center gap-3 py-3.5">
            <div className="hidden shrink-0 items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 sm:flex">
              <Tag className="h-3.5 w-3.5" />
              <span>Filter</span>
            </div>
            <div className="flex flex-1 items-center gap-2 overflow-x-auto pb-1">
              {categoryTabs.map((tab) => (
                <button
                  key={tab.id}
                  type="button"
                  onClick={() => setActiveCategory(tab.id)}
                  className={`group inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold transition ${
                    activeCategory === tab.id
                      ? 'bg-slate-900 text-white shadow-md'
                      : 'border border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'
                  }`}
                >
                  {tab.name}
                  <span
                    className={`rounded-full px-1.5 py-0.5 text-[10px] font-bold ${
                      activeCategory === tab.id
                        ? 'bg-white/20 text-white'
                        : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'
                    }`}
                  >
                    {tab.count}
                  </span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ========== DESIGN GALLERY ========== */}
      <section className="py-8 lg:py-12" id="pilih-design">
        <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
          {/* Result count */}
          <div className="mb-6 flex items-center justify-between">
            <p className="text-sm text-slate-500">
              {filteredDesigns.length > 0 ? (
                <>
                  Menunjukkan{' '}
                  <span className="font-semibold text-slate-900">{filteredDesigns.length}</span>{' '}
                  design
                </>
              ) : (
                'Tiada design dijumpai'
              )}
            </p>
            {activeCategory !== 'all' && (
              <button
                type="button"
                onClick={resetFilters}
                className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:text-brand-600"
              >
                <X className="h-3 w-3" />
                Set Semula
              </button>
            )}
          </div>

          {filteredDesigns.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-center">
              <div className="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-50">
                <ImageIcon className="h-8 w-8 text-slate-300" />
              </div>
              <h3 className="mt-5 text-lg font-bold text-slate-900">Tiada Design Dijumpai</h3>
              <p className="mt-2 max-w-xs text-sm text-slate-500">
                Cuba tukar carian atau pilih kategori lain.
              </p>
              <button
                type="button"
                onClick={resetFilters}
                className="mt-5 inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-brand-700"
              >
                Set Semula Filter
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
              {filteredDesigns.map((design) => (
                <Link
                  key={`design-${design.id}`}
                  href={route('orders.create', { design_id: design.id })}
                  className="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-600/5"
                >
                  {/* Image */}
                  <div className="relative aspect-square overflow-hidden bg-slate-50">
                    <img
                      src={design.image_url || app.logo_url}
                      alt={design.name}
                      className="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-105"
                      loading="lazy"
                    />
                    {/* Hover overlay */}
                    <div className="absolute inset-0 flex items-end justify-center bg-gradient-to-t from-black/60 via-black/0 to-transparent opacity-0 transition duration-300 group-hover:opacity-100">
                      <span className="mb-3 inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-[11px] font-bold text-brand-600 shadow-lg">
                        Pilih Design
                        <ArrowRight className="h-3 w-3" />
                      </span>
                    </div>
                    {/* Category badge */}
                    {design.category && (
                      <div className="absolute left-2 top-2 rounded-full bg-white/95 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-slate-700 shadow-sm backdrop-blur-sm">
                        {design.category.name}
                      </div>
                    )}
                  </div>
                  {/* Name */}
                  <div className="border-t border-slate-100 px-3 py-2.5">
                    <p className="truncate text-center text-xs font-semibold text-slate-800">
                      {design.name}
                    </p>
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </section>

      {/* ========== CTA BANNER ========== */}
      <section className="pb-16">
        <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
          <div className="relative overflow-hidden rounded-3xl bg-slate-900 px-6 py-10 text-center lg:px-16 lg:py-14">
            {/* Decorative gradient */}
            <div className="absolute inset-0 bg-gradient-to-br from-brand-600/20 via-transparent to-transparent" />
            <div className="relative z-10 mx-auto max-w-xl">
              <div className="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white/70">
                <Zap className="h-3 w-3" />
                Custom Design
              </div>
              <h2 className="mt-4 text-2xl font-extrabold tracking-tight text-white lg:text-3xl">
                Tak jumpa design yang sesuai?
              </h2>
              <p className="mt-2 text-sm text-slate-300">
                Hantar design anda sendiri, kami sedia membantu mencetak untuk anda!
              </p>
              <div className="mt-7 flex flex-wrap items-center justify-center gap-3">
                <a
                  href="https://wa.me/601169409606"
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-brand-50"
                >
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  Hantar Design Sendiri
                </a>
                <Link
                  href="/harga"
                  className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                >
                  Lihat Senarai Harga
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ========== FLOATING WHATSAPP BUTTON ========== */}
      <a
        href="https://wa.me/601169409606"
        target="_blank"
        rel="noreferrer"
        className="fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-2xl shadow-emerald-500/40 transition hover:scale-110 hover:bg-emerald-600"
        aria-label="Hubungi WhatsApp"
      >
        <svg className="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.004 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
      </a>
    </FrontendLayout>
  );
}