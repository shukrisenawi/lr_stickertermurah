import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { Head, Link, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
    ArrowRight,
    BadgePercent,
    MessageCircle,
    MousePointerClick,
    Sparkles,
    Star,
    Truck,
    X,
} from 'lucide-react';

/* ================= Jenis Data ================= */

interface DesignFromBackend {
    id: number;
    name: string;
    category: string;
    image: string | null;
    tags: string[];
}

interface TagCount {
    name: string;
    count: number;
}

/* ================= Konfigurasi ================= */

const WHATSAPP_NUMBER = '601169409606';
const WHATSAPP_LINK = `https://wa.me/${WHATSAPP_NUMBER}`;
const DESIGNS_API_URL = '/api/designs';

function waLinkFor(design: DesignFromBackend): string {
    const text = `Hi! Saya berminat dengan design "${design.name}" di StickerTermurah. Boleh saya dapatkan maklumat lanjut?`;
    return `${WHATSAPP_LINK}?text=${encodeURIComponent(text)}`;
}

const TAGS_ORDER = ['chatgpt', 'ai', 'baru', 'designbaru', 'cookies', 'kuih', 'viral', 'bakery', 'makanan', 'dessert'] as const;

function heroStickersFor(designs: DesignFromBackend[]) {
    const pick = (index: number) => designs[index]?.image ?? '/images/showcase/sticker-01.webp';
    return {
        main: pick(5),
        top: pick(0),
        right: pick(4),
        bottom: pick(7),
        left: pick(2),
    };
}

function marqueeImagesFor(designs: DesignFromBackend[]) {
    const count = Math.min(designs.length, 12);
    const images: string[] = [];
    for (let i = 0; i < count; i++) {
        if (designs[i]?.image) {
            images.push(designs[i].image as string);
        }
    }
    return images.length > 0 ? images : ['/images/showcase/sticker-01.webp'];
}

/* ================= Komponen Kecil ================= */

function WhatsAppIcon({ className = 'h-4 w-4' }: { className?: string }) {
    return (
        <svg className={className} fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.004 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
    );
}

function Reveal({
    children,
    className = '',
    delay = 0,
}: {
    children: React.ReactNode;
    className?: string;
    delay?: number;
}) {
    const ref = useRef<HTMLDivElement>(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const el = ref.current;
        if (!el) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setVisible(true);
            return;
        }
        const io = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setVisible(true);
                    io.disconnect();
                }
            },
            { threshold: 0.12 },
        );
        io.observe(el);
        return () => io.disconnect();
    }, []);

    return (
        <div
            ref={ref}
            style={{ transitionDelay: `${delay}ms` }}
            className={`reveal ${visible ? 'is-visible' : ''} ${className}`}
        >
            {children}
        </div>
    );
}

/* ================= Props Halaman ================= */

interface HomePageProps extends PageProps {
    testimonials: Array<{
        id: number;
        name: string;
        business: string | null;
        text: string;
        image_url: string | null;
        stars: number;
    }>;
    designs: DesignFromBackend[];
    designs_total: number;
    designs_limit: number;
    categories: Record<string, number>;
    tags: TagCount[];
}

interface DesignsApiResponse {
    data: DesignFromBackend[];
    meta: {
        offset: number;
        limit: number;
        total: number;
        has_more: boolean;
    };
}

/* ================= Halaman Utama ================= */

export default function Home() {
    const { app, testimonials, designs: initialDesigns, designs_total, designs_limit, categories, tags } = usePage<HomePageProps>().props;

    const [activeCategory, setActiveCategory] = useState<string>('Semua');
    const [activeTag, setActiveTag] = useState<string | null>(null);
    const [selected, setSelected] = useState<DesignFromBackend | null>(null);

    // Pagination state
    const [loadedDesigns, setLoadedDesigns] = useState<DesignFromBackend[]>(() => initialDesigns.filter((d) => d.image));
    const [offset, setOffset] = useState<number>(initialDesigns.length);
    const [total, setTotal] = useState<number>(designs_total);
    const [loadingMore, setLoadingMore] = useState<boolean>(false);
    const hasMore = offset < total;

    // Load initial batch when filter changes
    useEffect(() => {
        let cancelled = false;
        setLoadingMore(true);

        const url = new URL(DESIGNS_API_URL, window.location.origin);
        url.searchParams.set('offset', '0');
        url.searchParams.set('limit', String(designs_limit));
        if (activeCategory && activeCategory !== 'Semua') {
            url.searchParams.set('category', activeCategory);
        }
        if (activeTag) {
            url.searchParams.set('tag', activeTag);
        }

        fetch(url.toString())
            .then((res) => res.json())
            .then((payload: DesignsApiResponse) => {
                if (cancelled) return;
                setLoadedDesigns(payload.data.filter((d) => d.image));
                setOffset(payload.data.length);
                setTotal(payload.meta.total);
            })
            .catch(() => {
                if (cancelled) return;
            })
            .finally(() => {
                if (!cancelled) setLoadingMore(false);
            });

        return () => {
            cancelled = true;
        };
    }, [activeCategory, activeTag, designs_limit]);

    const loadMore = useCallback(async () => {
        if (loadingMore || !hasMore) return;
        setLoadingMore(true);

        try {
            const url = new URL(DESIGNS_API_URL, window.location.origin);
            url.searchParams.set('offset', String(offset));
            url.searchParams.set('limit', String(designs_limit));
            if (activeCategory && activeCategory !== 'Semua') {
                url.searchParams.set('category', activeCategory);
            }
            if (activeTag) {
                url.searchParams.set('tag', activeTag);
            }

            const res = await fetch(url.toString());
            const payload: DesignsApiResponse = await res.json();

            setLoadedDesigns((prev) => [...prev, ...payload.data.filter((d) => d.image)]);
            setOffset((prev) => prev + payload.data.length);
            setTotal(payload.meta.total);
        } finally {
            setLoadingMore(false);
        }
    }, [activeCategory, activeTag, offset, hasMore, loadingMore, designs_limit]);

    const categoryTabs = useMemo(
        () => [
            { name: 'Semua', count: designs_total },
            ...Object.entries(categories)
                .sort((a, b) => a[0].localeCompare(b[0]))
                .map(([name, count]) => ({ name, count })),
        ],
        [categories, designs_total],
    );

    const allTags = useMemo(() => {
        const ordered: TagCount[] = [];
        const seen = new Set<string>();
        TAGS_ORDER.forEach((name) => {
            const found = tags.find((t) => t.name === name);
            if (found) {
                ordered.push(found);
                seen.add(name);
            }
        });
        const rest = tags.filter((t) => !seen.has(t.name));
        return [...ordered, ...rest];
    }, [tags]);

    const heroStickers = useMemo(() => heroStickersFor(loadedDesigns), [loadedDesigns]);
    const marqueeImages = useMemo(() => marqueeImagesFor(loadedDesigns), [loadedDesigns]);

    const closeModal = useCallback(() => setSelected(null), []);

    // Kunci scroll badan + tutup modal dengan ESC
    useEffect(() => {
        if (!selected) return;
        document.body.style.overflow = 'hidden';
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') closeModal();
        };
        window.addEventListener('keydown', onKey);
        return () => {
            document.body.style.overflow = '';
            window.removeEventListener('keydown', onKey);
        };
    }, [selected, closeModal]);

    const scrollToGallery = (e: React.MouseEvent) => {
        e.preventDefault();
        document.getElementById('pilih-design')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const hasTestimonials = testimonials.length > 0;

    return (
        <FrontendLayout hideNavbar>
            <Head title="Pilih Design Sticker Mirrorcote Premium" />

            {/* ========== HEADER ========== */}
            <PublicHeader showTestimoni={hasTestimonials} />

            {/* ========== HERO ========== */}
            <section className="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white">
                <div className="mx-auto grid max-w-[1400px] items-center gap-10 px-4 pb-16 pt-10 lg:grid-cols-2 lg:gap-6 lg:px-8 lg:pb-20 lg:pt-16">
                    {/* Kiri — Teks */}
                    <div className="relative z-10 text-center lg:text-left">
                        <div className="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-white px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700 shadow-sm">
                            <Sparkles className="h-3 w-3" />
                            Sticker Mirrorcote Premium
                        </div>
                        <h1 className="mt-6 font-display text-5xl font-bold leading-[1.05] tracking-tight text-slate-900 sm:text-6xl lg:text-[4.4rem]">
                            Pilih Design.
                            <br />
                            <span className="text-brand-600">WhatsApp.</span> Siap!
                        </h1>
                        <p className="mx-auto mt-5 max-w-md text-base leading-relaxed text-slate-500 lg:mx-0 lg:text-lg">
                            {total}+ design eksklusif sedia diubahsuai dengan nama jenama &amp;
                            nombor telefon anda. Cetakan mirrorcote berkilat, pos seluruh Malaysia.
                        </p>
                        <div className="mt-8 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                            <a
                                href="#pilih-design"
                                onClick={scrollToGallery}
                                className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-7 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98]"
                            >
                                Pilih Design
                                <ArrowRight className="h-4 w-4" />
                            </a>
                            <a
                                href={WHATSAPP_LINK}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-2 rounded-full border-2 border-emerald-500 bg-white px-7 py-3.5 text-sm font-bold text-emerald-600 transition hover:bg-emerald-50 active:scale-[0.98]"
                            >
                                <WhatsAppIcon className="h-4 w-4" />
                                WhatsApp Kami
                            </a>
                        </div>
                    </div>

                    {/* Kanan — Kolaj Sticker */}
                    <div className="relative mx-auto aspect-square w-full max-w-[420px] lg:max-w-[540px]">
                        {/* Latar lembut */}
                        <div className="absolute inset-[8%] rounded-full bg-gradient-to-br from-brand-100 via-brand-50 to-amber-50" />
                        <div className="absolute inset-[18%] rounded-full border-2 border-dashed border-brand-200" />

                        {/* Sticker utama */}
                        <img
                            src={heroStickers.main}
                            alt="Contoh sticker utama"
                            fetchPriority="high"
                            className="absolute left-1/2 top-1/2 w-[52%] -translate-x-1/2 -translate-y-1/2 rounded-full shadow-2xl shadow-brand-900/20 ring-8 ring-white"
                        />
                        {/* Sticker kecil terapung */}
                        <div className="animate-float absolute left-[2%] top-[6%] w-[30%]">
                            <img
                                src={heroStickers.top}
                                alt="Contoh sticker terapung"
                                className="w-full -rotate-[8deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                            />
                        </div>
                        <div className="animate-float-slow absolute right-[0%] top-[14%] w-[27%]">
                            <img
                                src={heroStickers.right}
                                alt="Contoh sticker terapung"
                                className="w-full rotate-[7deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                            />
                        </div>
                        <div className="animate-float-slow absolute bottom-[4%] left-[10%] w-[26%]">
                            <img
                                src={heroStickers.left}
                                alt="Contoh sticker terapung"
                                className="w-full rotate-[6deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                            />
                        </div>
                        <div className="animate-float absolute bottom-[10%] right-[6%] w-[29%]">
                            <img
                                src={heroStickers.bottom}
                                alt="Contoh sticker terapung"
                                className="w-full -rotate-[6deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                            />
                        </div>

                        {/* Lencana kuning */}
                        <div className="animate-wiggle absolute -right-1 top-[42%] flex h-24 w-24 items-center justify-center rounded-full bg-accent text-center shadow-xl shadow-amber-500/30 ring-4 ring-white sm:-right-3 sm:h-28 sm:w-28">
                            <span className="font-display text-xs font-bold leading-tight text-slate-900">
                                HARGA
                                <br />
                                TERMURAH
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {/* ========== MARQUEE STICKER ========== */}
            <section className="overflow-hidden py-6" aria-hidden="true">
                <div className="marquee-pause -mx-4 -rotate-[1.2deg] border-y-4 border-white bg-brand-600 py-5 shadow-lg shadow-brand-600/20">
                    <div className="animate-marquee flex w-max items-center gap-8 pr-8">
                        {[...marqueeImages, ...marqueeImages].map((src, i) => (
                            <img
                                key={i}
                                src={src}
                                alt=""
                                loading="lazy"
                                className="h-20 w-20 shrink-0 rounded-full bg-white object-cover shadow-md ring-4 ring-white/90 sm:h-24 sm:w-24"
                            />
                        ))}
                    </div>
                </div>
            </section>

            {/* ========== GALERI DESIGN ========== */}
            <section id="pilih-design" className="scroll-mt-20 py-14 lg:py-20">
                <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
                    <Reveal>
                        <h2 className="font-display text-4xl font-bold tracking-tight text-slate-900 lg:text-5xl">
                            Pilih Design Anda
                        </h2>
                        <p className="mt-3 max-w-xl text-base leading-relaxed text-slate-500">
                            Setiap design boleh diubahsuai dengan nama jenama, nombor telefon &amp; media sosial
                            anda — tanpa caj tambahan. Klik mana-mana design untuk mula.
                        </p>
                    </Reveal>

                    {/* Filter kategori */}
                    <div className="sticky top-16 z-30 -mx-4 mt-8 bg-white/95 px-4 py-3 backdrop-blur-md lg:-mx-8 lg:px-8">
                        <div className="flex items-center gap-2 overflow-x-auto pb-1">
                            {categoryTabs.map((tab) => (
                                <button
                                    key={tab.name}
                                    type="button"
                                    onClick={() => setActiveCategory(tab.name)}
                                    className={`inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-4 py-2 text-xs font-bold transition active:scale-[0.97] ${
                                        activeCategory === tab.name
                                            ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25'
                                            : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                                    }`}
                                >
                                    {tab.name}
                                    <span
                                        className={`rounded-full px-1.5 py-0.5 text-[10px] font-bold ${
                                            activeCategory === tab.name
                                                ? 'bg-white/20 text-white'
                                                : 'bg-slate-100 text-slate-500'
                                        }`}
                                    >
                                        {tab.count}
                                    </span>
                                </button>
                            ))}
                        </div>

                        {/* Filter hashtag */}
                        {allTags.length > 0 && (
                            <div className="mt-3 flex flex-wrap items-center gap-2">
                                <span className="text-xs font-semibold text-slate-400">#</span>
                                {allTags.slice(0, 12).map((tag) => (
                                    <button
                                        key={tag.name}
                                        type="button"
                                        onClick={() => setActiveTag(activeTag === tag.name ? null : tag.name)}
                                        className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-bold transition active:scale-[0.97] ${
                                            activeTag === tag.name
                                                ? 'bg-brand-100 text-brand-700 ring-1 ring-brand-300'
                                                : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                                        }`}
                                    >
                                        #{tag.name}
                                    </button>
                                ))}
                                {activeTag && (
                                    <button
                                        type="button"
                                        onClick={() => setActiveTag(null)}
                                        className="text-[11px] font-bold text-rose-500 hover:text-rose-600"
                                    >
                                        Kosongkan
                                    </button>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Grid design */}
                    <div className="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4 xl:grid-cols-5">
                        {loadedDesigns.map((design, index) => (
                            <Reveal key={design.id} delay={Math.min(index % 5, 4) * 60}>
                                <button
                                    type="button"
                                    onClick={() => setSelected(design)}
                                    className="group flex w-full flex-col rounded-3xl border border-slate-100 bg-white p-3 text-left shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-600/10 md:odd:rotate-[0.6deg] md:even:-rotate-[0.6deg] md:hover:rotate-0"
                                >
                                    <div className="relative overflow-hidden rounded-2xl bg-slate-50">
                                        <img
                                            src={design.image ?? undefined}
                                            alt={`Design sticker ${design.name}`}
                                            loading="lazy"
                                            className="aspect-square w-full object-cover transition duration-500 ease-out group-hover:scale-[1.06]"
                                        />
                                        <div className="absolute inset-0 flex items-end justify-center bg-gradient-to-t from-black/55 via-black/0 to-transparent opacity-0 transition duration-300 group-hover:opacity-100">
                                            <span className="mb-3 inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-[11px] font-bold text-brand-600 shadow-lg">
                                                Pilih Design
                                                <ArrowRight className="h-3 w-3" />
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex items-center justify-between gap-2 px-1 pb-1 pt-3">
                                        <p className="truncate font-display text-sm font-bold text-slate-800">
                                            {design.name}
                                        </p>
                                        <span className="shrink-0 rounded-full bg-brand-50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-brand-700">
                                            {design.category}
                                        </span>
                                    </div>
                                    <div className="flex flex-wrap gap-1 px-1 pt-0.5">
                                        {design.tags.slice(0, 3).map((tag) => (
                                            <span
                                                key={tag}
                                                className="rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-500"
                                            >
                                                #{tag}
                                            </span>
                                        ))}
                                    </div>
                                </button>
                            </Reveal>
                        ))}
                    </div>

                    {/* Load more */}
                    {hasMore && (
                        <div className="mt-10 flex justify-center">
                            <button
                                type="button"
                                onClick={loadMore}
                                disabled={loadingMore}
                                className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-7 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-brand-200 hover:text-brand-600 active:scale-[0.97] disabled:opacity-60"
                            >
                                {loadingMore ? (
                                    <>
                                        <span className="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-brand-600" />
                                        Memuatkan...
                                    </>
                                ) : (
                                    <>
                                        Lihat Lebih Banyak Design
                                        <ArrowRight className="h-4 w-4" />
                                    </>
                                )}
                            </button>
                        </div>
                    )}
                </div>
            </section>

            {/* ========== CARA TEMPAH ========== */}
            <section id="cara-tempah" className="scroll-mt-20 bg-gradient-to-b from-brand-50/60 via-white to-white py-14 lg:py-20">
                <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
                    <Reveal className="text-center">
                        <h2 className="font-display text-4xl font-bold tracking-tight text-slate-900 lg:text-5xl">
                            Cara Tempah
                        </h2>
                        <p className="mx-auto mt-3 max-w-md text-base leading-relaxed text-slate-500">
                            Tiga langkah mudah — dari pilih design hingga sticker sampai ke pintu anda.
                        </p>
                    </Reveal>

                    <div className="relative mt-12 grid gap-5 md:grid-cols-3">
                        {/* Garis penghubung (desktop) */}
                        <div className="absolute left-[16%] right-[16%] top-14 hidden border-t-2 border-dashed border-brand-200 md:block" aria-hidden="true" />

                        {[
                            {
                                icon: MousePointerClick,
                                title: 'Pilih Design',
                                copy: 'Semak galeri kami & pilih design yang paling sesuai dengan jenama anda.',
                            },
                            {
                                icon: MessageCircle,
                                title: 'WhatsApp Kami',
                                copy: 'Bagitahu design pilihan, saiz & kuantiti — kami balas segera dengan harga.',
                            },
                            {
                                icon: Truck,
                                title: 'Kami Cetak & Pos',
                                copy: 'Sticker dicetak & dihantar terus ke alamat anda di seluruh Malaysia.',
                            },
                        ].map((step, i) => (
                            <Reveal key={step.title} delay={i * 120}>
                                <div className="relative flex h-full flex-col items-center rounded-3xl border border-slate-100 bg-white px-6 py-8 text-center shadow-sm">
                                    <span className="absolute -top-3 right-5 rounded-full bg-accent px-3 py-1 font-display text-xs font-bold text-slate-900 shadow-sm">
                                        0{i + 1}
                                    </span>
                                    <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                                        <step.icon className="h-6 w-6" />
                                    </div>
                                    <h3 className="mt-4 font-display text-xl font-bold text-slate-900">
                                        {step.title}
                                    </h3>
                                    <p className="mt-2 text-sm leading-relaxed text-slate-500">{step.copy}</p>
                                </div>
                            </Reveal>
                        ))}
                    </div>

                    {/* Nilai tambah */}
                    <Reveal delay={150}>
                        <div className="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 rounded-3xl border border-brand-100 bg-white px-6 py-5">
                            {[
                                { icon: Sparkles, label: 'Mirrorcote Berkilat' },
                                { icon: BadgePercent, label: 'Harga Berbaloi' },
                                { icon: Truck, label: 'Pos Seluruh Malaysia' },
                                { icon: MessageCircle, label: 'Respon Pantas' },
                            ].map((item) => (
                                <span
                                    key={item.label}
                                    className="inline-flex items-center gap-2 text-sm font-semibold text-slate-700"
                                >
                                    <item.icon className="h-4 w-4 text-brand-600" />
                                    {item.label}
                                </span>
                            ))}
                        </div>
                    </Reveal>
                </div>
            </section>

            {/* ========== TESTIMONI ========== */}
            {hasTestimonials && (
                <section id="testimoni" className="scroll-mt-20 py-14 lg:py-20">
                    <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
                        <Reveal className="text-center">
                            <h2 className="font-display text-4xl font-bold tracking-tight text-slate-900 lg:text-5xl">
                                Kata Pelanggan Kami
                            </h2>
                        </Reveal>
                        <div className="mt-10 grid gap-5 md:grid-cols-3">
                            {testimonials.map((t, i) => (
                                <Reveal key={t.id} delay={i * 120}>
                                    <figure className="flex h-full flex-col rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                                        <div className="flex items-center gap-1">
                                            {Array.from({ length: t.stars }).map((_, s) => (
                                                <Star key={s} className="h-4 w-4 fill-amber-400 text-amber-400" />
                                            ))}
                                        </div>
                                        <blockquote className="mt-4 flex-1 text-sm leading-relaxed text-slate-600">
                                            “{t.text}”
                                        </blockquote>
                                        <figcaption className="mt-5 border-t border-slate-100 pt-4">
                                            <p className="font-display text-sm font-bold text-slate-900">{t.name}</p>
                                            {t.business && (
                                                <p className="text-xs text-slate-500">{t.business}</p>
                                            )}
                                        </figcaption>
                                    </figure>
                                </Reveal>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* ========== CTA AKHIR ========== */}
            <section className="pb-16 pt-4 lg:pb-24">
                <div className="mx-auto max-w-[1400px] px-4 lg:px-8">
                    <Reveal>
                        <div className="relative overflow-hidden rounded-[2.5rem] bg-brand-900 px-6 py-12 lg:px-16 lg:py-16">
                            {/* Hiasan sticker */}
                            <img
                                src="/images/showcase/sticker-22.webp"
                                alt=""
                                aria-hidden="true"
                                className="absolute -left-10 -top-10 w-40 rotate-[-14deg] rounded-full opacity-20"
                            />
                            <img
                                src="/images/showcase/sticker-29.webp"
                                alt=""
                                aria-hidden="true"
                                className="absolute -bottom-12 right-[38%] w-44 rotate-[10deg] rounded-full opacity-15"
                            />
                            <div className="relative z-10 grid items-center gap-10 lg:grid-cols-[1.3fr_1fr]">
                                <div className="text-center lg:text-left">
                                    <h2 className="font-display text-4xl font-bold leading-tight tracking-tight text-white lg:text-5xl">
                                        Ada Design Sendiri? Boleh!
                                    </h2>
                                    <p className="mx-auto mt-4 max-w-md text-base leading-relaxed text-brand-100 lg:mx-0">
                                        WhatsAppkan design anda — kami cetak ikut saiz &amp; kuantiti pilihan anda.
                                    </p>
                                    <div className="mt-8 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                                        <a
                                            href={WHATSAPP_LINK}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-bold text-brand-800 shadow-xl transition hover:bg-brand-50 active:scale-[0.98]"
                                        >
                                            <WhatsAppIcon className="h-4 w-4 text-emerald-500" />
                                            WhatsApp Kami
                                        </a>
                                        <Link
                                            href="/harga"
                                            className="inline-flex items-center gap-2 rounded-full border-2 border-white/25 px-7 py-3.5 text-sm font-bold text-white transition hover:bg-white/10 active:scale-[0.98]"
                                        >
                                            Lihat Senarai Harga
                                        </Link>
                                    </div>
                                </div>
                                <div className="relative mx-auto hidden w-full max-w-[280px] lg:block">
                                    <img
                                        src={app.logo_url}
                                        alt="Logo StickerTermurah"
                                        className="animate-float w-full rounded-full shadow-2xl shadow-black/30 ring-8 ring-white/15"
                                    />
                                </div>
                            </div>
                        </div>
                    </Reveal>
                </div>
            </section>

            {/* ========== MODAL QUICK VIEW ========== */}
            {selected && (
                <div
                    className="fixed inset-0 z-[60] flex items-end justify-center bg-slate-900/60 p-0 backdrop-blur-sm sm:items-center sm:p-6"
                    onClick={closeModal}
                    role="dialog"
                    aria-modal="true"
                    aria-label={`Design ${selected.name}`}
                >
                    <div
                        className="w-full max-w-lg overflow-hidden rounded-t-[2rem] bg-white shadow-2xl sm:rounded-[2rem]"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <div className="relative bg-slate-50">
                            <img
                                src={selected.image ?? undefined}
                                alt={`Design sticker ${selected.name}`}
                                className="aspect-square w-full object-cover"
                            />
                            <button
                                type="button"
                                onClick={closeModal}
                                aria-label="Tutup"
                                className="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-md transition hover:bg-white hover:text-slate-900"
                            >
                                <X className="h-4 w-4" />
                            </button>
                            <span className="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-brand-700 shadow-sm">
                                {selected.category}
                            </span>
                        </div>
                        <div className="p-6">
                            <h3 className="font-display text-2xl font-bold tracking-tight text-slate-900">
                                {selected.name}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-slate-500">
                                Design ini akan diubahsuai dengan nama jenama, nombor telefon &amp; media sosial
                                anda — percuma.
                            </p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {selected.tags.map((tag) => (
                                    <button
                                        key={tag}
                                        type="button"
                                        onClick={() => {
                                            setActiveTag(tag);
                                            setSelected(null);
                                            document.getElementById('pilih-design')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                        }}
                                        className="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 transition hover:bg-brand-100"
                                    >
                                        #{tag}
                                    </button>
                                ))}
                            </div>
                            <div className="mt-6 flex flex-col gap-2.5">
                                <a
                                    href={waLinkFor(selected)}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-emerald-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600 active:scale-[0.98]"
                                >
                                    <WhatsAppIcon className="h-4 w-4" />
                                    WhatsApp Kami
                                </a>
                                <Link
                                    href="/harga"
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-600 transition hover:border-brand-200 hover:text-brand-600"
                                >
                                    Lihat Senarai Harga
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* ========== BUTANG WHATSAPP TERAPUNG ========== */}
            <a
                href={WHATSAPP_LINK}
                target="_blank"
                rel="noreferrer"
                className="group fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-2xl shadow-emerald-500/40 transition hover:scale-110 hover:bg-emerald-600"
                aria-label="Hubungi kami melalui WhatsApp"
            >
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-40" />
                <WhatsAppIcon className="relative h-7 w-7" />
            </a>
        </FrontendLayout>
    );
}
