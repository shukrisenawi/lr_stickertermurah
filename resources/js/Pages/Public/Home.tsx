import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useState, useMemo } from 'react';
import { Search, ArrowRight, Tag, Zap } from 'lucide-react';

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
  const [searchQuery, setSearchQuery] = useState('');

  const categoryTabs = useMemo(() => {
    const tabs: Array<{ id: number | 'all'; name: string }> = [{ id: 'all', name: 'Semua' }];
    for (const c of categories) {
      tabs.push({ id: c.id, name: c.name });
    }
    return tabs;
  }, [categories]);

  const filteredDesigns = useMemo(() => {
    let designs = allDesigns;
    if (activeCategory !== 'all') {
      designs = designs.filter((d) => d.category_id === activeCategory);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      designs = designs.filter(
        (d) =>
          d.name.toLowerCase().includes(q) ||
          (d.category?.name ?? '').toLowerCase().includes(q),
      );
    }
    return designs;
  }, [activeCategory, allDesigns, searchQuery]);

  return (
    <FrontendLayout>
      <Head title="Print Sticker Mirrorcote Premium Malaysia" />

      {/* ========== HERO MINIMAL ========== */}
      <section className="bg-gradient-to-b from-brand-50/50 to-white pt-16 pb-8 lg:pt-20 lg:pb-12">
        <div className="mx-auto max-w-[1280px] px-4 text-center lg:px-8">
          <div className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-white mb-4">
            <Zap className="h-3.5 w-3.5" />
            PRINT STICKER MIRRORCOTE PREMIUM
          </div>
          <h1 className="text-3xl font-extrabold tracking-tight text-slate-900 lg:text-5xl">
            Pilih{' '}
            <span className="text-brand-600">Design Sticker</span> Anda
          </h1>
          <p className="mt-3 text-base text-slate-500 lg:text-lg max-w-lg mx-auto">
            Lebih 30 design premium sedia untuk dipilih. Klik pada design untuk mula tempahan!
          </p>
        </div>
      </section>

      {/* ========== FILTERS ========== */}
      <section className="sticky top-[72px] z-40 border-b border-slate-100 bg-white/80 backdrop-blur-sm">
        <div className="mx-auto max-w-[1280px] px-4 py-3 lg:px-8">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="relative flex-1 max-w-xs">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
              <input
                type="text"
                placeholder="Cari design..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <div className="flex flex-wrap items-center gap-2 overflow-x-auto pb-1">
              {categoryTabs.map((tab) => (
                <button
                  key={tab.id}
                  type="button"
                  onClick={() => setActiveCategory(tab.id)}
                  className={`whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold transition ${
                    activeCategory === tab.id
                      ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20'
                      : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:bg-brand-50'
                  }`}
                >
                  {tab.name}
                  {tab.id !== 'all' && (
                    <span className="ml-1.5 opacity-60">
                      ({categories.find((c) => c.id === tab.id)?.designs.length ?? 0})
                    </span>
                  )}
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ========== DESIGN GRID ========== */}
      <section className="py-10 lg:py-14">
        <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
          {filteredDesigns.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-20 text-center">
              <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                <Search className="h-6 w-6 text-slate-400" />
              </div>
              <h3 className="mt-4 text-lg font-bold text-slate-900">Tiada Design Dijumpai</h3>
              <p className="mt-2 text-sm text-slate-500 max-w-xs">
                Cuba tukar kategori atau carian anda.
              </p>
              <button
                type="button"
                onClick={() => { setActiveCategory('all'); setSearchQuery(''); }}
                className="mt-4 rounded-full bg-brand-600 px-5 py-2 text-xs font-semibold text-white transition hover:bg-brand-700"
              >
                Set Semula
              </button>
            </div>
          ) : (
            <>
              <p className="mb-6 text-sm text-slate-500">
                Menunjukkan <span className="font-semibold text-slate-900">{filteredDesigns.length}</span> design
              </p>
              <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                {filteredDesigns.map((design) => (
                  <Link
                    key={`design-${design.id}`}
                    href={route('orders.create', { design_id: design.id })}
                    className="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:shadow-lg hover:-translate-y-0.5"
                  >
                    <div className="aspect-square overflow-hidden bg-slate-50">
                      <img
                        src={design.image_url || app.logo_url}
                        alt={design.name}
                        className="h-full w-full object-cover transition duration-300 group-hover:scale-110"
                        loading="lazy"
                      />
                    </div>
                    <div className="absolute inset-0 flex items-center justify-center bg-gradient-to-t from-black/50 via-black/10 to-transparent opacity-0 transition group-hover:opacity-100">
                      <span className="flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-xs font-bold text-brand-600 shadow-lg">
                        Pilih Design
                        <ArrowRight className="h-3.5 w-3.5" />
                      </span>
                    </div>
                    {design.category && (
                      <div className="absolute left-2 top-2 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-semibold text-slate-600 shadow-sm backdrop-blur-sm">
                        {design.category.name}
                      </div>
                    )}
                    <div className="border-t border-slate-100 px-3 py-2.5">
                      <p className="truncate text-center text-xs font-semibold text-slate-800">
                        {design.name}
                      </p>
                    </div>
                  </Link>
                ))}
              </div>
            </>
          )}
        </div>
      </section>

      {/* ========== CTA BANNER ========== */}
      <section className="pb-16">
        <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
          <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-600 to-brand-500 px-8 py-10 text-center lg:px-16">
            <div className="relative z-10">
              <h2 className="text-xl font-extrabold text-white lg:text-2xl">
                Tak jumpa design yang sesuai?
              </h2>
              <p className="mt-2 text-sm text-brand-100">
                Hantar design anda sendiri, kami sedia membantu!
              </p>
              <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a
                  href="https://wa.me/601169409606"
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-brand-600 transition hover:bg-brand-50"
                >
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  Hantar Design Sendiri
                </a>
                <a
                  href="/harga"
                  className="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
                >
                  <Tag className="h-4 w-4" />
                  Lihat Harga
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </FrontendLayout>
  );
}
