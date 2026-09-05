import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { Head } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import {
    ArrowDown,
    ArrowUp,
    BadgePercent,
    Calculator,
    Info,
    MessageCircle,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-react';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';
import { calculateBillableA3Sheets, minimumA3Sheets } from '@/lib/stickerPricing';

interface Size {
    id: number;
    name: string;
    width_cm: number;
    height_cm: number;
    shape: string | null;
    qty_per_a3: number | null;
    show: boolean;
}

interface PriceSetting {
    id: number;
    sticker_type: string;
    qty_from: number;
    qty_to: number | null;
    price_per_a3: number | string;
}

interface Discount {
    id: number;
    name: string;
    sticker_type: string | null;
    sticker_size_id: number | null;
    min_qty: number;
    max_qty: number | null;
    type: 'fixed' | 'price' | 'percentage';
    value: number | string;
}

interface PriceCell {
    original: number;
    discounted: number;
    discountName: string | null;
}

interface PriceCheckerProps {
    sizes: Size[];
    priceSettings: PriceSetting[];
    discounts: Discount[];
    stickerTypes: string[];
    paymentSettings: { admin_phone: string } | null;
    priceTableQuantities: number[];
    minimumA3SheetsWithoutDesign: number;
}

const QUICK_QUANTITIES = [100, 200, 300, 500, 1000];
const DEFAULT_TABLE_DIMENSIONS = [3, 4, 5, 6, 7, 8, 9, 10];
const DEFAULT_TABLE_SHAPES = new Set(['segiempatsama', 'bulat']);
const getShapeLabel = (size: Size) => size.shape?.trim() || 'Lain-lain';
const normalizeShape = (shape: string | null) => shape?.trim().toLowerCase().replace(/\s+/g, '') ?? '';
const roundPrice = (price: number) => Math.round(price * 100) / 100;

const applyDiscount = (
    originalPrice: number,
    discounts: Discount[],
    sizeId: number,
    stickerType: string,
    quantity: number,
): PriceCell => {
    const bestDiscount = discounts
        .filter((discount) =>
            (discount.sticker_type === null || discount.sticker_type === stickerType)
            && (discount.sticker_size_id === null || discount.sticker_size_id === sizeId)
            && quantity >= discount.min_qty
            && (discount.max_qty === null || quantity <= discount.max_qty),
        )
        .reduce<{ price: number; name: string | null }>((best, discount) => {
            const value = Number(discount.value);
            if (!Number.isFinite(value) || value <= 0) return best;

            const discountedPrice = discount.type === 'percentage'
                ? originalPrice * Math.max(0, 1 - value / 100)
                : discount.type === 'price'
                    ? Math.max(0, value)
                    : Math.max(0, originalPrice - value);

            return discountedPrice < best.price
                ? { price: discountedPrice, name: discount.name }
                : best;
        }, { price: originalPrice, name: null });

    return {
        original: roundPrice(originalPrice),
        discounted: roundPrice(bestDiscount.price),
        discountName: bestDiscount.name,
    };
};

export default function PriceChecker({
    sizes,
    priceSettings,
    discounts,
    stickerTypes,
    paymentSettings,
    priceTableQuantities,
    minimumA3SheetsWithoutDesign,
}: PriceCheckerProps) {
    const [stickerType, setStickerType] = useState(stickerTypes[0] ?? 'Mirrorcote');
    const [width, setWidth] = useState('');
    const [height, setHeight] = useState('');
    const [shape, setShape] = useState('');
    const [quantity, setQuantity] = useState('100');
    const [hasDesign, setHasDesign] = useState(false);
    const [showPopup, setShowPopup] = useState(false);
    const [sizeToAdd, setSizeToAdd] = useState('');
    const [selectedSizeIds, setSelectedSizeIds] = useState<number[]>(() =>
        sizes
            .filter((size) => size.show && DEFAULT_TABLE_SHAPES.has(normalizeShape(size.shape)))
            .filter((size) => {
                const match = size.name.match(/^\s*(\d+(?:[.,]\d+)?)/);

                return match && DEFAULT_TABLE_DIMENSIONS.includes(Number(match[1].replace(',', '.')));
            })
            .map((size) => size.id),
    );
    const calculatorRef = useRef<HTMLDivElement>(null);
    const minimumSheets = minimumA3Sheets(hasDesign, minimumA3SheetsWithoutDesign);

    const matchedSize = useMemo(() => {
        if (!width || !height) return null;
        const w = parseFloat(width);
        const h = parseFloat(height);
        if (isNaN(w) || isNaN(h)) return null;

        return sizes.find((s) => s.width_cm === w && s.height_cm === h) ?? null;
    }, [sizes, width, height]);

    const calculation = useMemo(() => {
        if (!matchedSize || !matchedSize.qty_per_a3 || !quantity) return null;
        const q = parseInt(quantity);
        if (isNaN(q) || q < 1) return null;

        const a3Sheets = calculateBillableA3Sheets(q, matchedSize.qty_per_a3, hasDesign, minimumA3SheetsWithoutDesign);
        const match = priceSettings.find(
            (ps) => ps.sticker_type === stickerType && a3Sheets >= ps.qty_from && (ps.qty_to === null || a3Sheets <= ps.qty_to),
        );
        if (!match) return null;

        const pricePerA3 = Number(match.price_per_a3);

        const price = applyDiscount(a3Sheets * pricePerA3, discounts, matchedSize.id, stickerType, q);

        return { a3Sheets, pricePerA3, total: price.discounted, originalTotal: price.original, discountName: price.discountName };
    }, [discounts, matchedSize, quantity, priceSettings, stickerType, hasDesign, minimumA3SheetsWithoutDesign]);

    const needsAdmin = matchedSize && (!matchedSize.qty_per_a3 || !calculation);

    const priceTables = useMemo(
        () =>
            stickerTypes.map((tableStickerType) => {
                const typePriceSettings = priceSettings.filter((setting) => setting.sticker_type === tableStickerType);
                const rows = selectedSizeIds.flatMap((id) => {
                    const size = sizes.find((item) => item.id === id);
                    if (!size) return [];

                    const prices = priceTableQuantities.reduce<Record<number, PriceCell | null>>((result, tableQuantity) => {
                        if (!size.qty_per_a3) {
                            result[tableQuantity] = null;
                            return result;
                        }

                        const naturalA3Sheets = Math.ceil(tableQuantity / Math.max(1, size.qty_per_a3));
                        if (naturalA3Sheets < minimumSheets) {
                            result[tableQuantity] = null;
                            return result;
                        }

                        const a3Sheets = calculateBillableA3Sheets(tableQuantity, size.qty_per_a3, hasDesign, minimumA3SheetsWithoutDesign);
                        const tier = typePriceSettings.find(
                            (setting) =>
                                a3Sheets >= setting.qty_from &&
                                (setting.qty_to === null || a3Sheets <= setting.qty_to),
                        );

                        result[tableQuantity] = tier
                            ? applyDiscount(
                                a3Sheets * Number(tier.price_per_a3),
                                discounts,
                                size.id,
                                tableStickerType,
                                tableQuantity,
                            )
                            : null;
                        return result;
                    }, {});

                    return [{ size, prices }];
                });

                const shapes = Array.from(new Set(rows.map(({ size }) => getShapeLabel(size))));

                return shapes.map((shape) => ({
                    stickerType: tableStickerType,
                    shape,
                    rows: rows.filter(({ size }) => getShapeLabel(size) === shape),
                }));
            }).flat(),
        [discounts, hasDesign, minimumA3SheetsWithoutDesign, minimumSheets, priceSettings, priceTableQuantities, selectedSizeIds, sizes, stickerTypes],
    );

    const availableSizes = sizes.filter((size) => size.show && !selectedSizeIds.includes(size.id));
    const availableSizeGroups = availableSizes.reduce<Record<string, Size[]>>((groups, size) => {
        const type = getShapeLabel(size);
        groups[type] ??= [];
        groups[type].push(size);

        return groups;
    }, {});
    const availableSizeGroupEntries = Object.entries(availableSizeGroups).sort(([firstType], [secondType]) => {
        const firstIsPetak = firstType.trim().toLowerCase() === 'petak';
        const secondIsPetak = secondType.trim().toLowerCase() === 'petak';

        return Number(firstIsPetak) - Number(secondIsPetak);
    });

    const addSizeToTable = () => {
        const id = Number(sizeToAdd);
        if (!id || selectedSizeIds.includes(id)) return;

        setSelectedSizeIds((currentIds) => [...currentIds, id]);
        setSizeToAdd('');
    };

    const removeSizeFromTable = (id: number) => {
        setSelectedSizeIds((currentIds) => currentIds.filter((currentId) => currentId !== id));
    };

    const moveSize = (id: number, shape: string, direction: -1 | 1) => {
        setSelectedSizeIds((currentIds) => {
            const sameShapeIds = currentIds.filter((currentId) => {
                const size = sizes.find((item) => item.id === currentId);

                return size && getShapeLabel(size) === shape;
            });
            const currentShapeIndex = sameShapeIds.indexOf(id);
            const nextShapeIndex = currentShapeIndex + direction;
            if (currentShapeIndex < 0 || nextShapeIndex < 0 || nextShapeIndex >= sameShapeIds.length) return currentIds;

            const reorderedIds = [...currentIds];
            const currentIndex = currentIds.indexOf(id);
            const nextIndex = currentIds.indexOf(sameShapeIds[nextShapeIndex]);
            [reorderedIds[currentIndex], reorderedIds[nextIndex]] = [reorderedIds[nextIndex], reorderedIds[currentIndex]];
            return reorderedIds;
        });
    };

    const scrollToCalculator = () => {
        calculatorRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const handleCek = () => {
        if (!matchedSize) {
            setShowPopup(true);
            return;
        }
        if (!matchedSize.qty_per_a3) {
            setShowPopup(true);
            return;
        }
        if (!calculation) {
            setShowPopup(true);
            return;
        }
    };

    // Kunci scroll badan + tutup popup dengan ESC
    useEffect(() => {
        if (!showPopup) return;
        document.body.style.overflow = 'hidden';
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') setShowPopup(false);
        };
        window.addEventListener('keydown', onKey);
        return () => {
            document.body.style.overflow = '';
            window.removeEventListener('keydown', onKey);
        };
    }, [showPopup]);

    const adminPhone = paymentSettings?.admin_phone ?? '01169409606';
    const waUrl = whatsappWebUrl(
        adminPhone,
        `Hi, saya nak tanya harga sticker:\n- Jenis: ${stickerType}\n- Lebar: ${width || '?'}cm\n- Tinggi: ${height || '?'}cm\n- Bentuk: ${shape || '-'}\n- Kuantiti: ${quantity || '?'} pcs\n- Status design: ${hasDesign ? 'Design sudah siap' : 'Belum ada design'}`,
    );

    const waOrderUrl = calculation
        ? whatsappWebUrl(
              adminPhone,
              `Hi, saya nak tempah sticker:\n- Jenis: ${stickerType}\n- Saiz: ${width}cm × ${height}cm\n- Bentuk: ${shape || '-'}\n- Kuantiti: ${quantity} pcs\n- Anggaran: RM ${calculation.total.toFixed(2)}`,
          )
        : waUrl;

    const inputClass =
        'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100';
    const labelClass = 'mb-2 block text-sm font-bold text-slate-700';

    return (
        <FrontendLayout hideNavbar>
            <Head title="Harga Sticker Mirrorcote Malaysia" />
            <PublicHeader active="harga" />

            <div className="bg-gradient-to-b from-brand-50/70 via-white to-white pb-20">
                <div className="mx-auto max-w-[1200px] px-4 pt-12 lg:px-8 lg:pt-16">
                    {/* ===== Tajuk ===== */}
                    <div className="max-w-xl">
                        <div className="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-white px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700 shadow-sm">
                            <BadgePercent className="h-3 w-3" />
                            Senarai Harga
                        </div>
                        <h1 className="mt-5 font-display text-4xl font-bold tracking-tight text-slate-900 lg:text-5xl">
                            Semak Harga Sticker
                        </h1>
                        <p className="mt-3 text-base leading-relaxed text-slate-500">
                            Masukkan jenis, saiz dan kuantiti untuk dapatkan anggaran harga.
                        </p>
                    </div>

                    {/* ===== Kalkulator + Keputusan ===== */}
                    <div ref={calculatorRef} id="kalkulator-harga" className="mt-10 scroll-mt-20 grid items-start gap-6 lg:grid-cols-[1fr_400px]">
                        {/* Borang */}
                        <div className="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                            <div className="flex items-center gap-3">
                                <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                                    <Calculator className="h-5 w-5" />
                                </div>
                                <h2 className="font-display text-xl font-bold text-slate-900">Kalkulator Harga</h2>
                            </div>

                            <div className="mt-6 space-y-5">
                                {/* Status design mempengaruhi kiraan harga */}
                                <div>
                                    <p className={labelClass}>Status Design</p>
                                    <div className="grid gap-2 sm:grid-cols-2">
                                        <button
                                            type="button"
                                            onClick={() => setHasDesign(false)}
                                            className={`rounded-2xl border-2 px-4 py-3 text-left transition ${
                                                !hasDesign
                                                    ? 'border-amber-300 bg-amber-50'
                                                    : 'border-slate-200 bg-white hover:border-amber-200'
                                            }`}
                                        >
                                            <span className="block text-sm font-bold text-slate-900">Belum ada design</span>
                                            <span className="mt-1 block text-xs text-slate-500">Harga akan disahkan bersama admin</span>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setHasDesign(true)}
                                            className={`rounded-2xl border-2 px-4 py-3 text-left transition ${
                                                hasDesign
                                                    ? 'border-emerald-300 bg-emerald-50'
                                                    : 'border-slate-200 bg-white hover:border-emerald-200'
                                            }`}
                                        >
                                            <span className="block text-sm font-bold text-slate-900">Design sudah siap</span>
                                            <span className="mt-1 block text-xs text-slate-500">Sedia untuk dikira</span>
                                        </button>
                                    </div>
                                    <p className="mt-2 flex items-start gap-1.5 text-xs leading-relaxed text-slate-500">
                                        <Info className="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-600" />
                                        Pilih status yang betul supaya anggaran harga ikut minimum tempahan anda.
                                    </p>
                                </div>

                                {/* Jenis Sticker */}
                                <div>
                                    <p className={labelClass}>Jenis Sticker</p>
                                    <div className="flex flex-wrap gap-2">
                                        {stickerTypes.map((t) => (
                                            <button
                                                key={t}
                                                type="button"
                                                onClick={() => setStickerType(t)}
                                                className={`rounded-full px-5 py-2.5 text-sm font-bold transition active:scale-[0.97] ${
                                                    stickerType === t
                                                        ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25'
                                                        : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                                                }`}
                                            >
                                                {t}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                {/* Saiz */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label htmlFor="lebar" className={labelClass}>
                                            Lebar (cm)
                                        </label>
                                        <input
                                            id="lebar"
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            value={width}
                                            onChange={(e) => setWidth(e.target.value)}
                                            className={inputClass}
                                            placeholder="cth: 5"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="tinggi" className={labelClass}>
                                            Tinggi (cm)
                                        </label>
                                        <input
                                            id="tinggi"
                                            type="number"
                                            step="0.1"
                                            min="0"
                                            value={height}
                                            onChange={(e) => setHeight(e.target.value)}
                                            className={inputClass}
                                            placeholder="cth: 5"
                                        />
                                    </div>
                                </div>

                                {/* Bentuk */}
                                <div>
                                    <label htmlFor="bentuk" className={labelClass}>
                                        Bentuk
                                    </label>
                                    <select
                                        id="bentuk"
                                        value={shape}
                                        onChange={(e) => setShape(e.target.value)}
                                        className={inputClass}
                                    >
                                        <option value="">Pilih bentuk</option>
                                        <option value="Petak">Petak</option>
                                        <option value="Segi Empat Sama">Segi Empat Sama</option>
                                        <option value="Bulat">Bulat</option>
                                        <option value="Oval">Oval</option>
                                        <option value="Bebas">Bebas / Custom</option>
                                    </select>
                                </div>

                                {/* Kuantiti */}
                                <div>
                                    <label htmlFor="kuantiti" className={labelClass}>
                                        Kuantiti (pcs)
                                    </label>
                                    <div className="mb-2.5 flex flex-wrap gap-2">
                                        {QUICK_QUANTITIES.map((q) => (
                                            <button
                                                key={q}
                                                type="button"
                                                onClick={() => setQuantity(String(q))}
                                                className={`rounded-full px-4 py-2 text-xs font-bold transition active:scale-[0.97] ${
                                                    quantity === String(q)
                                                        ? 'bg-slate-900 text-white shadow-md'
                                                        : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                                                }`}
                                            >
                                                {q.toLocaleString()}
                                            </button>
                                        ))}
                                    </div>
                                    <input
                                        id="kuantiti"
                                        type="number"
                                        min="1"
                                        value={quantity}
                                        onChange={(e) => setQuantity(e.target.value)}
                                        className={inputClass}
                                        placeholder="cth: 100"
                                    />
                                </div>

                                <button
                                    type="button"
                                    onClick={handleCek}
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-5 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98]"
                                >
                                    <Search className="h-4 w-4" />
                                    Cek Harga
                                </button>
                            </div>
                        </div>

                        {/* Panel keputusan */}
                        <div className="lg:sticky lg:top-24">
                            {calculation ? (
                                <div className="overflow-hidden rounded-[2rem] border border-emerald-200 bg-gradient-to-b from-emerald-50 to-white shadow-sm">
                                    <div className="border-b border-emerald-100 bg-emerald-500/10 px-6 py-4">
                                        <h3 className="font-display text-lg font-bold text-emerald-800">
                                            Anggaran Harga Anda
                                        </h3>
                                    </div>
                                    <div className="space-y-2.5 px-6 py-5 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-slate-500">Saiz</span>
                                            <span className="font-semibold text-slate-900">
                                                {width}cm × {height}cm
                                            </span>
                                        </div>
                                        {shape && (
                                            <div className="flex justify-between">
                                                <span className="text-slate-500">Bentuk</span>
                                                <span className="font-semibold text-slate-900">{shape}</span>
                                            </div>
                                        )}
                                        <div className="flex justify-between">
                                            <span className="text-slate-500">Jenis</span>
                                            <span className="font-semibold text-slate-900">{stickerType}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-slate-500">Status design</span>
                                            <span className="font-semibold text-slate-900">{hasDesign ? 'Design sudah siap' : 'Belum ada design'}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-slate-500">Kuantiti</span>
                                            <span className="font-semibold text-slate-900">
                                                {parseInt(quantity).toLocaleString()} sticker
                                            </span>
                                        </div>
                                        <div className="mt-3 flex items-end justify-between border-t border-emerald-100 pt-4">
                                            <span className="text-sm font-bold text-slate-900">Jumlah</span>
                                            {calculation.originalTotal > calculation.total ? (
                                                <span className="flex flex-col items-end gap-0.5">
                                                    <span className="text-sm font-medium text-slate-400 line-through">RM {calculation.originalTotal.toFixed(2)}</span>
                                                    <span className="font-display text-3xl font-bold text-pink-600">RM {calculation.total.toFixed(2)}</span>
                                                </span>
                                            ) : (
                                                <span className="font-display text-3xl font-bold text-emerald-600">
                                                    RM {calculation.total.toFixed(2)}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="px-6 pb-6">
                                        <a
                                            href={waOrderUrl}
                                            target={WHATSAPP_TARGET}
                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-emerald-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600 active:scale-[0.98]"
                                        >
                                            <MessageCircle className="h-4 w-4" />
                                            Tempah Melalui WhatsApp
                                        </a>
                                        <p className="mt-4 flex items-start gap-1.5 text-xs leading-relaxed text-slate-400">
                                            <Info className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                            Anggaran berdasarkan tetapan harga semasa. Harga sebenar akan disahkan
                                            melalui WhatsApp.
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="flex h-full min-h-[280px] flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-200 bg-white/60 px-8 py-10 text-center">
                                    <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-400">
                                        <Calculator className="h-6 w-6" />
                                    </div>
                                    <h3 className="mt-4 font-display text-lg font-bold text-slate-900">
                                        Anggaran Akan Papar Di Sini
                                    </h3>
                                    <p className="mt-2 max-w-[240px] text-sm leading-relaxed text-slate-500">
                                        Lengkapkan maklumat di sebelah untuk melihat anggaran harga sticker anda.
                                    </p>
                                </div>
                            )}

                            {matchedSize && !calculation && !needsAdmin && (
                                <div className="mt-4 flex items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    <Info className="mt-0.5 h-4 w-4 shrink-0" />
                                    Sila isi semua ruangan untuk semak harga.
                                </div>
                            )}
                        </div>
                    </div>

                    {/* ===== Jadual Harga ===== */}
                    <div className="mt-14">
                        <div className="mb-6 flex flex-col gap-5 rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm lg:flex-row lg:items-end lg:justify-between sm:p-8">
                            <div>
                                <h2 className="font-display text-3xl font-bold tracking-tight text-slate-900 lg:text-4xl">
                                    Jadual Harga
                                </h2>
                                <p className="mt-2 max-w-xl text-sm leading-relaxed text-slate-500">
                                    Harga dalam jadual mengikut status design yang dipilih. Tambah beberapa saiz untuk buat perbandingan.
                                </p>
                                <div className="mt-4 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        onClick={scrollToCalculator}
                                        className="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-2.5 text-xs font-bold text-brand-700 transition hover:border-brand-300 hover:bg-brand-100 active:scale-[0.98]"
                                    >
                                        <Calculator className="h-4 w-4" />
                                        Kira saiz &amp; kuantiti lain
                                    </button>
                                    <a
                                        href={waUrl}
                                        target={WHATSAPP_TARGET}
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600 active:scale-[0.98]"
                                    >
                                        <MessageCircle className="h-4 w-4" />
                                        Tanya Harga Admin
                                    </a>
                                </div>
                            </div>

                            <div className="flex w-full flex-col gap-3 sm:flex-row sm:items-end lg:w-auto">
                                <div className="min-w-0 sm:min-w-[250px]">
                                    <label htmlFor="tambah-saiz" className={labelClass}>
                                        Tambah saiz perbandingan
                                    </label>
                                    <select
                                        id="tambah-saiz"
                                        value={sizeToAdd}
                                        onChange={(e) => setSizeToAdd(e.target.value)}
                                        className={inputClass}
                                        disabled={availableSizes.length === 0}
                                    >
                                        <option value="">
                                            {availableSizes.length > 0 ? 'Pilih saiz' : 'Semua saiz telah ditambah'}
                                        </option>
                                        {availableSizeGroupEntries.map(([type, groupSizes]) => (
                                            <optgroup key={type} label={type}>
                                                {groupSizes.map((size) => (
                                                    <option key={size.id} value={size.id}>
                                                        {size.name}
                                                    </option>
                                                ))}
                                            </optgroup>
                                        ))}
                                    </select>
                                </div>
                                <button
                                    type="button"
                                    onClick={addSizeToTable}
                                    disabled={!sizeToAdd}
                                    className="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none"
                                >
                                    <Plus className="h-4 w-4" />
                                    Tambah
                                </button>
                            </div>
                        </div>

                        {priceTables.length === 0 || priceTables.every(({ rows }) => rows.length === 0) ? (
                            <div className="flex min-h-[220px] flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-200 bg-white/70 px-6 py-12 text-center">
                                <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-400">
                                    <BadgePercent className="h-6 w-6" />
                                </div>
                                <h3 className="mt-4 font-display text-lg font-bold text-slate-900">Belum ada saiz dipilih</h3>
                                <p className="mt-2 max-w-sm text-sm leading-relaxed text-slate-500">
                                    Pilih saiz di atas dan tekan Tambah untuk papar harga dalam jadual ini.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-6">
                                {priceTables.map(({ stickerType: tableStickerType, shape, rows }) => (
                                    <div key={`${tableStickerType}-${shape}`} className="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm">
                                        <div className="border-b border-slate-100 bg-brand-50 px-6 py-4 sm:px-8">
                                            <h3 className="font-display text-xl font-bold text-slate-900">
                                                Jadual Harga {tableStickerType} ({shape})
                                            </h3>
                                        </div>
                                        <div className="relative isolate overflow-x-auto">
                                            <table className="w-full min-w-[900px] border-separate border-spacing-0 text-xs">
                                                <thead>
                                                    <tr className="bg-brand-600 text-white">
                                                        <th className="sticky left-0 z-20 w-px whitespace-nowrap bg-brand-600 px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider shadow-[4px_0_8px_-6px_rgba(15,23,42,0.5)]">
                                                            Saiz
                                                        </th>
                                                        {priceTableQuantities.map((q) => (
                                                            <th
                                                                key={q}
                                                                className="px-3 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider"
                                                            >
                                                                {q.toLocaleString()} pcs
                                                            </th>
                                                        ))}
                                                        <th className="px-4 py-3.5 text-right text-[11px] font-bold uppercase tracking-wider">
                                                            Tindakan
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-slate-100">
                                                    {rows.map((row, i) => (
                                                        <tr
                                                            key={row.size.id}
                                                            className={`transition hover:bg-brand-50/60 ${i % 2 === 1 ? 'bg-slate-50/50' : ''}`}
                                                        >
                                                            <td className={`sticky left-0 z-10 w-px whitespace-nowrap px-4 py-2.5 shadow-[4px_0_8px_-6px_rgba(15,23,42,0.18)] ${i % 2 === 1 ? 'bg-slate-50' : 'bg-white'}`}>
                                                                <div className="font-display text-sm font-bold text-slate-900">{row.size.name}</div>
                                                            </td>
                                                            {priceTableQuantities.map((q) => {
                                                                const price = row.prices[q];
                                                                return (
                                                                    <td
                                                                        key={String(q)}
                                                                        className={`px-3 py-2.5 text-right tabular-nums ${
                                                                            price !== null && price !== undefined
                                                                                ? 'font-semibold text-slate-900'
                                                                                : 'text-slate-300'
                                                                        }`}
                                                                    >
                                                                        {price !== null && price !== undefined
                                                                            ? price.discounted < price.original ? (
                                                                                <span className="inline-flex flex-col items-end leading-tight whitespace-nowrap">
                                                                                    <span className="text-[10px] font-medium text-slate-400 line-through">RM{price.original.toFixed(2)}</span>
                                                                                    <span className="font-bold text-pink-600">RM{price.discounted.toFixed(2)}</span>
                                                                                </span>
                                                                            ) : `RM${price.original.toFixed(2)}`
                                                                            : '-'}
                                                                    </td>
                                                                );
                                                            })}
                                                            <td className="px-4 py-2.5">
                                                                <div className="flex items-center justify-end gap-1">
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => moveSize(row.size.id, shape, -1)}
                                                                        disabled={i === 0}
                                                                        aria-label={`Naikkan ${row.size.name}`}
                                                                        title="Naikkan baris"
                                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-30"
                                                                    >
                                                                        <ArrowUp className="h-4 w-4" />
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => moveSize(row.size.id, shape, 1)}
                                                                        disabled={i === rows.length - 1}
                                                                        aria-label={`Turunkan ${row.size.name}`}
                                                                        title="Turunkan baris"
                                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-brand-50 hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-30"
                                                                    >
                                                                        <ArrowDown className="h-4 w-4" />
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => removeSizeFromTable(row.size.id)}
                                                                        aria-label={`Buang ${row.size.name} daripada jadual`}
                                                                        title="Buang saiz"
                                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50 hover:text-rose-600"
                                                                    >
                                                                        <Trash2 className="h-4 w-4" />
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                        <p className="mt-4 flex items-start justify-center gap-1.5 text-center text-xs text-slate-400">
                            <Info className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            Harga tertakluk kepada perubahan semasa. Hubungi kami untuk pengesahan harga terkini.
                        </p>
                    </div>
                </div>
            </div>

            {/* ===== Popup WhatsApp ===== */}
            {showPopup && (
                <div
                    className="fixed inset-0 z-[60] flex items-end justify-center bg-slate-900/60 backdrop-blur-sm sm:items-center sm:p-6"
                    onClick={(e) => {
                        if (e.target === e.currentTarget) setShowPopup(false);
                    }}
                    onKeyDown={(e) => {
                        if (e.key === 'Escape') setShowPopup(false);
                    }}
                    role="dialog"
                    aria-modal="true"
                    aria-label="Hubungi kami"
                    tabIndex={-1}
                >
                    <div className="w-full max-w-md rounded-t-[2rem] bg-white p-6 shadow-2xl sm:rounded-[2rem]">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="font-display text-xl font-bold text-slate-900">Hubungi Kami</h3>
                            <button
                                type="button"
                                onClick={() => setShowPopup(false)}
                                aria-label="Tutup"
                                className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                        <p className="mb-5 text-sm leading-relaxed text-slate-600">
                            {!matchedSize
                                ? 'Saiz yang anda pilih tiada dalam senarai kami. Sila hubungi kami untuk sebut harga.'
                                : 'Saiz ini belum mempunyai tetapan harga. Sila hubungi kami untuk sebut harga.'}
                        </p>
                        <div className="mb-5 space-y-1.5 rounded-2xl bg-slate-50 p-4 text-sm">
                            <p className="font-bold text-slate-700">Maklumat pertanyaan:</p>
                            <p className="text-slate-600">Jenis: {stickerType}</p>
                            <p className="text-slate-600">
                                Saiz: {width || '?'}cm × {height || '?'}cm
                            </p>
                            {shape && <p className="text-slate-600">Bentuk: {shape}</p>}
                            <p className="text-slate-600">Kuantiti: {quantity || '?'} pcs</p>
                        </div>
                        <a
                            href={waUrl}
                            target={WHATSAPP_TARGET}
                            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-emerald-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600 active:scale-[0.98]"
                        >
                            <MessageCircle className="h-4 w-4" />
                            WhatsApp Kami
                        </a>
                    </div>
                </div>
            )}
        </FrontendLayout>
    );
}
