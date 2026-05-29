import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { Link } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import {
  ShieldCheck,
  Droplets,
  Palette,
  Truck,
  Heart,
  Star,
  Quote,
  Image as ImageIcon,
  ShoppingCart,
  ClipboardCheck,
  Printer,
  ArrowRight,
  Tag,
  MessageCircle,
  Upload,
} from 'lucide-react';

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

const features = [
  {
    icon: ShieldCheck,
    title: 'Kualiti Premium',
    desc: 'Material mirrorcote berkualiti tinggi',
    color: 'text-blue-600',
    bg: 'bg-blue-50',
  },
  {
    icon: Droplets,
    title: 'Tahan Lama',
    desc: 'Tahan air, calar & tidak mudah pudar',
    color: 'text-cyan-600',
    bg: 'bg-cyan-50',
  },
  {
    icon: Palette,
    title: 'Warna Menarik',
    desc: 'Cetakan warna yang lebih hidup & jelas',
    color: 'text-emerald-600',
    bg: 'bg-emerald-50',
  },
  {
    icon: Truck,
    title: 'Penghantaran Pantas',
    desc: 'Proses cepat & pos laju ke seluruh Malaysia',
    color: 'text-orange-600',
    bg: 'bg-orange-50',
  },
  {
    icon: Heart,
    title: '100% Kepuasan',
    desc: 'Kami utamakan kualiti & servis terbaik',
    color: 'text-rose-600',
    bg: 'bg-rose-50',
  },
];

const defaultAvatar = (name: string) =>
  `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(name)}`;

const steps = [
  {
    num: '01',
    icon: ImageIcon,
    title: 'Pilih Design',
    desc: 'Pilih design yang anda suka',
  },
  {
    num: '02',
    icon: ShoppingCart,
    title: 'Pilih Kuantiti',
    desc: 'Pilih saiz & kuantiti yang diperlukan',
  },
  {
    num: '03',
    icon: ClipboardCheck,
    title: 'Sahkan Order',
    desc: 'Semak maklumat & buat bayaran',
  },
  {
    num: '04',
    icon: Printer,
    title: 'Proses Cetakan',
    desc: 'Kami proses & cetak dengan kualiti terbaik',
  },
  {
    num: '05',
    icon: Truck,
    title: 'Penghantaran',
    desc: 'Pos laju ke seluruh Malaysia',
  },
];

export default function Home() {
  const { app, categories, allDesigns, testimonials } = usePage<HomePageProps>().props;
  const [activeCategory, setActiveCategory] = useState<number | 'all'>('all');

  const categoryTabs = useMemo(() => {
    const tabs: Array<{ id: number | 'all'; name: string }> = [{ id: 'all', name: 'Semua' }];
    for (const c of categories) {
      tabs.push({ id: c.id, name: c.name });
    }
    return tabs;
  }, [categories]);

  const filteredDesigns = useMemo(() => {
    if (activeCategory === 'all') return allDesigns;
    return allDesigns.filter((d) => d.category_id === activeCategory);
  }, [activeCategory, allDesigns]);

  const heroDesigns = useMemo(() => {
    const designs: Array<{ id: number; image_url: string | null; name: string }> = [];
    for (const cat of categories) {
      for (const d of cat.designs.slice(0, 2)) {
        if (d.image_url) designs.push(d);
      }
    }
    return designs.slice(0, 6);
  }, [categories]);

  const heroFeatureList = [
    'Kualiti cetakan premium',
    'Warna tajam & tidak mudah pudar',
    'Tahan air, calar & cuaca',
    'Sesuai untuk semua jenis penggunaan',
  ];

  return (
    <FrontendLayout>
      <Head title="Print Sticker Mirrorcote Premium Malaysia" />
      <div className="frontend-shell">
        {/* ========== HERO SECTION ========== */}
        <section className="relative overflow-hidden pt-10 pb-16 lg:pt-16 lg:pb-24">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="grid grid-cols-1 gap-10 lg:grid-cols-2 lg:items-center">
              {/* Left: Text */}
              <div className="max-w-xl">
                <div className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-white">
                  <Tag className="h-3.5 w-3.5" />
                  100% MIRRORCOTE PREMIUM
                </div>
                <h1 className="mt-5 text-4xl font-extrabold tracking-tight text-slate-900 lg:text-5xl">
                  PRINT STICKER{' '}
                  <span className="text-brand-600">MIRRORCOTE</span>
                </h1>
                <p className="mt-4 text-lg font-semibold text-slate-700">
                  Berkilat, Tahan Lama, Warna Lebih Menarik
                </p>
                <ul className="mt-6 space-y-3">
                  {heroFeatureList.map((item) => (
                    <li key={item} className="flex items-center gap-3 text-sm text-slate-600">
                      <span className="flex h-5 w-5 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                        <svg className="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                          <path
                            fillRule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clipRule="evenodd"
                          />
                        </svg>
                      </span>
                      {item}
                    </li>
                  ))}
                </ul>
                <div className="mt-8 flex flex-wrap items-center gap-4">
                  <Link
                    href={route('orders.create')}
                    className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/25"
                  >
                    Pilih Design Sekarang
                    <ArrowRight className="h-4 w-4" />
                  </Link>
                  <a
                    href="/harga"
                    className="inline-flex items-center gap-2 rounded-full border-2 border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-200 hover:bg-brand-50"
                  >
                    Lihat Harga
                    <Tag className="h-4 w-4" />
                  </a>
                </div>
              </div>

              {/* Right: Floating Stickers */}
              <div className="relative flex items-center justify-center">
                <div className="relative h-[380px] w-[380px] lg:h-[450px] lg:w-[450px]">
                  {heroDesigns.map((design, i) => {
                    const positions = [
                      'top-0 left-1/2 -translate-x-1/2',
                      'top-[15%] right-0',
                      'top-[40%] right-[5%]',
                      'bottom-[10%] right-[15%]',
                      'bottom-0 left-1/2 -translate-x-1/2',
                      'top-[20%] left-[5%]',
                    ];
                    const sizes = ['w-32 h-32', 'w-28 h-28', 'w-24 h-24', 'w-32 h-32', 'w-28 h-28', 'w-24 h-24'];
                    const rotations = ['-rotate-6', 'rotate-12', '-rotate-12', 'rotate-6', '-rotate-3', 'rotate-3'];
                    return (
                      <div
                        key={`hero-${design.id}`}
                        className={`absolute ${positions[i]} rounded-full border-4 border-white shadow-2xl shadow-slate-900/10 overflow-hidden ${sizes[i]} ${rotations[i]} transition-transform hover:scale-110 hover:z-10`}
                      >
                        <img
                          src={design.image_url || app.logo_url}
                          alt={design.name}
                          className="h-full w-full object-cover"
                        />
                      </div>
                    );
                  })}
                  {/* Floating badge */}
                  <div className="absolute top-[5%] right-[0%] inline-flex items-center gap-1.5 rounded-full bg-blue-500 px-3 py-1.5 text-xs font-bold text-white shadow-lg">
                    <svg className="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Warna Lebih Tajam
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ========== FEATURES BAR ========== */}
        <section className="mx-auto max-w-[1280px] px-4 lg:px-8">
          <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
            <div className="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-5">
              {features.map((f) => (
                <div key={f.title} className="flex items-start gap-3">
                  <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${f.bg}`}>
                    <f.icon className={`h-5 w-5 ${f.color}`} />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-slate-900">{f.title}</p>
                    <p className="text-xs leading-relaxed text-slate-500">{f.desc}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* ========== PILIH DESIGN ========== */}
        <section id="pilih-design" className="py-20">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="text-center">
              <h2 className="text-2xl font-extrabold tracking-tight text-slate-900">PILIH DESIGN</h2>
              <p className="mt-2 text-sm text-slate-500">
                Pilih design yang anda suka atau hantar design sendiri!
              </p>
            </div>

            {/* Category Tabs */}
            <div className="mt-8 flex flex-wrap items-center justify-center gap-2">
              {categoryTabs.map((tab) => (
                <button
                  key={tab.id}
                  type="button"
                  onClick={() => {
                    setActiveCategory(tab.id);
                  }}
                  className={`rounded-full px-4 py-2 text-xs font-semibold transition ${
                    activeCategory === tab.id
                      ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20'
                      : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:bg-brand-50'
                  }`}
                >
                  {tab.name}
                </button>
              ))}
            </div>

            {/* Design Grid */}
            <div className="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
              {filteredDesigns.map((design) => (
                <div
                  key={`design-${design.id}`}
                  className="group relative cursor-pointer overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition hover:shadow-md"
                  onClick={() => {
                    window.location.href = route('orders.create', { design_id: design.id });
                  }}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      window.location.href = route('orders.create', { design_id: design.id });
                    }
                  }}
                  role="button"
                  tabIndex={0}
                >
                  <div className="aspect-square overflow-hidden">
                    <img
                      src={design.image_url || app.logo_url}
                      alt={design.name}
                      className="h-full w-full object-cover transition group-hover:scale-105"
                    />
                  </div>
                  <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition group-hover:bg-black/20">
                    <span className="rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand-600 opacity-0 shadow-sm transition group-hover:opacity-100">
                      Pilih
                    </span>
                  </div>
                  <p className="truncate px-3 py-2 text-center text-xs font-medium text-slate-700">
                    {design.name}
                  </p>
                </div>
              ))}
            </div>

            <div className="mt-8 text-center">
              <Link
                href={route('orders.create')}
                className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-brand-200 hover:bg-brand-50"
              >
                Lihat Lebih Banyak Design
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </div>
        </section>

        {/* ========== TESTIMONI ========== */}
        <section id="testimoni" className="bg-white py-20">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="text-center">
              <h2 className="text-2xl font-extrabold tracking-tight text-slate-900">TESTIMONI PELANGGAN</h2>
            </div>

            <div className="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
              {testimonials.map((t) => (
                <div
                  key={`home-t-${t.id}`}
                  className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition hover:shadow-md"
                >
                  <Quote className="h-6 w-6 text-brand-300" />
                  <div className="mt-3 flex gap-0.5">
                    {Array.from({ length: t.stars }).map((_, i) => (
                      <Star key={`star-${t.id}-${i}`} className="h-4 w-4 fill-amber-400 text-amber-400" />
                    ))}
                  </div>
                  <p className="mt-3 text-sm leading-relaxed text-slate-600">{t.text}</p>
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

            <div className="mt-8 text-center">
              <Link
                href={route('testimonials.index')}
                className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
              >
                Lihat Lebih Banyak Testimoni
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </div>
        </section>

        {/* ========== CARA TEMPAH ========== */}
        <section id="cara-tempah" className="py-20">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="text-center">
              <h2 className="text-2xl font-extrabold tracking-tight text-slate-900">CARA TEMPAH</h2>
            </div>

            <div className="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-center">
              {/* Steps */}
              <div className="lg:col-span-9">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 md:grid-cols-5">
                  {steps.map((step, i) => (
                    <div key={step.num} className="relative text-center">
                      <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border-2 border-brand-100 bg-white text-brand-600 shadow-sm">
                        <step.icon className="h-6 w-6" />
                      </div>
                      <div className="mt-3">
                        <span className="text-xs font-bold text-brand-600">{step.num}</span>
                        <h3 className="mt-1 text-sm font-bold text-slate-900">{step.title}</h3>
                        <p className="mt-1 text-xs leading-relaxed text-slate-500">{step.desc}</p>
                      </div>
                      {i < steps.length - 1 && (
                        <div className="hidden md:block absolute top-8 -right-3 text-slate-300">
                          <ArrowRight className="h-4 w-4" />
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              </div>

              {/* Design Sendiri Card */}
              <div className="lg:col-span-3">
                <div className="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                  <div className="flex items-center gap-2">
                    <Upload className="h-5 w-5 text-brand-600" />
                    <h3 className="text-sm font-bold text-slate-900">Design Sendiri?</h3>
                  </div>
                  <p className="mt-2 text-xs leading-relaxed text-slate-500">
                    Hantar design anda sendiri, kami boleh bantu cetak!
                  </p>
                  <a
                    href="https://wa.me/601169409606"
                    target="_blank"
                    rel="noreferrer"
                    className="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:border-brand-200 hover:bg-brand-50"
                  >
                    Hantar Design
                    <Upload className="h-3.5 w-3.5" />
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* ========== CTA BANNER ========== */}
        <section id="hubungi-kami" className="pb-16">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-600 to-brand-500 px-8 py-10 text-center lg:px-16">
              <div className="relative z-10">
                <h2 className="text-xl font-extrabold text-white lg:text-2xl">
                  Ada soalan? Kami sedia membantu!
                </h2>
                <p className="mt-2 text-sm text-brand-100">
                  Hubungi kami sekarang untuk maklumat lanjut.
                </p>
                <div className="mt-6 flex flex-wrap items-center justify-center gap-3">
                  <a
                    href="https://wa.me/601169409606"
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-brand-600 transition hover:bg-brand-50"
                  >
                    <MessageCircle className="h-4 w-4" />
                    011-694090606
                  </a>
                  <a
                    href="mailto:stickertermurah@gmail.com"
                    className="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20"
                  >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email Kami
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </FrontendLayout>
  );
}
