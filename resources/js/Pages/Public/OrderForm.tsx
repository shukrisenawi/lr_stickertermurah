import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';
import {
  Check,
  ChevronRight,
  Image as ImageIcon,
  Info,
  LoaderCircle,
  RotateCcw,
  Search,
  ShoppingCart,
  X,
} from 'lucide-react';

interface DesignOption {
  id: number;
  name: string;
  image_url: string | null;
  category: string | null;
  tags: string[];
}

interface DesignsApiResponse {
  data: Array<{
    id: number;
    name: string;
    image: string | null;
    category: string | null;
    tags: string[];
  }>;
  meta: {
    offset: number;
    limit: number;
    total: number;
    has_more: boolean;
  };
}

interface RepeatOrderItem {
  id: number;
  sticker_design_id: number | null;
  custom_design_description: string | null;
  sticker_size_id: number | null;
  requested_size: string | null;
  quantity: number;
  cut_type: string | null;
}

interface RepeatOrder {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  items: RepeatOrderItem[];
}

interface OrderFormProps extends PageProps {
  initialDesign: DesignOption | null;
  sizes: Array<{
    id: number;
    name: string;
    width_cm: number;
    height_cm: number;
    qty_per_a3: number | null;
    is_active: boolean;
  }>;
  previousDesigns: DesignOption[];
  catalogTags: string[];
  priceSettings: Array<{
    id: number;
    sticker_type: string;
    qty_from: number;
    qty_to: number | null;
    price_per_a3: number;
  }>;
  paymentSettings: {
    bank_name: string;
    bank_account_no: string;
    bank_account_name: string;
    deposit_amount: number;
  } | null;
  repeatOrder: RepeatOrder | null;
}

export default function OrderForm() {
  const { initialDesign, previousDesigns, catalogTags, sizes, priceSettings, paymentSettings, repeatOrder, auth } = usePage<OrderFormProps>().props;

  const repeatItem = repeatOrder?.items?.[0] ?? null;
  const initialDesignId = initialDesign?.id ?? null;

  const [selectedDesign, setSelectedDesign] = useState<number | 'custom'>(
    initialDesignId ? initialDesignId : 'custom'
  );
  const [selectedDesignInfo, setSelectedDesignInfo] = useState<DesignOption | null>(initialDesign);
  const [customDesc, setCustomDesc] = useState(repeatItem?.custom_design_description ?? '');
  const [selectedSize, setSelectedSize] = useState<number | null>(repeatItem?.sticker_size_id ?? null);
  const [quantity, setQuantity] = useState(repeatItem?.quantity ?? 100);
  const [requestCustomSize, setRequestCustomSize] = useState(!!repeatItem?.requested_size && !repeatItem?.sticker_size_id);
  const [customSizeDesc, setCustomSizeDesc] = useState(repeatItem?.requested_size ?? '');
  const [cutType, setCutType] = useState<'standard' | 'die-cut'>(
    repeatItem?.cut_type === 'die-cut' ? 'die-cut' : 'standard'
  );
  const [designPreview, setDesignPreview] = useState<string | null>(null);
  const [isDesignPickerOpen, setIsDesignPickerOpen] = useState(false);
  const [catalogDesigns, setCatalogDesigns] = useState<DesignOption[]>([]);
  const [catalogSearch, setCatalogSearch] = useState('');
  const [catalogTag, setCatalogTag] = useState<string | null>(null);
  const [catalogOffset, setCatalogOffset] = useState(0);
  const [catalogTotal, setCatalogTotal] = useState(0);
  const [catalogLoading, setCatalogLoading] = useState(false);
  const [catalogLoaded, setCatalogLoaded] = useState(false);
  const [catalogError, setCatalogError] = useState(false);
  const catalogAbortRef = useRef<AbortController | null>(null);

  const { data, setData, post, processing, errors } = useForm({
    design_id: initialDesignId,
    custom_description: repeatItem?.custom_design_description ?? '',
    size_id: repeatItem?.sticker_size_id ?? null,
    requested_size: repeatItem?.requested_size ?? '',
    quantity: repeatItem?.quantity ?? 100,
    cut_type: (repeatItem?.cut_type === 'die-cut' ? 'die-cut' : 'standard') as 'standard' | 'die-cut',
    customer_design_image: null as File | null,
    customer_name: repeatOrder?.customer_name ?? auth.user?.name ?? '',
    customer_phone: repeatOrder?.customer_phone ?? '',
    customer_address: repeatOrder?.customer_address ?? '',
    repeat_from_order_id: repeatOrder?.id ?? null,
  });

  const loadCatalog = async (nextOffset: number, reset: boolean, search: string, tag = catalogTag) => {
    catalogAbortRef.current?.abort();
    const controller = new AbortController();
    catalogAbortRef.current = controller;
    setCatalogLoading(true);
    setCatalogError(false);

    try {
      const url = new URL('/api/designs', window.location.origin);
      url.searchParams.set('offset', String(nextOffset));
      url.searchParams.set('limit', '12');
      if (search) url.searchParams.set('search', search);
      if (tag) url.searchParams.set('tag', tag);

      const response = await fetch(url.toString(), {
        signal: controller.signal,
        headers: { Accept: 'application/json' },
      });

      if (!response.ok) throw new Error('Gagal memuatkan katalog design.');

      const payload = (await response.json()) as DesignsApiResponse;
      const nextDesigns = payload.data.map((design) => ({
        id: design.id,
        name: design.name,
        image_url: design.image,
        category: design.category,
        tags: design.tags ?? [],
      }));

      setCatalogDesigns((current) => (reset ? nextDesigns : [...current, ...nextDesigns]));
      setCatalogOffset(payload.meta.offset + payload.data.length);
      setCatalogTotal(payload.meta.total);
      setCatalogLoaded(true);
    } catch (error) {
      if ((error as Error).name !== 'AbortError') setCatalogError(true);
    } finally {
      if (catalogAbortRef.current === controller) setCatalogLoading(false);
    }
  };

  const openDesignPicker = () => {
    setIsDesignPickerOpen(true);
    if (!catalogLoaded && !catalogLoading) void loadCatalog(0, true, '');
  };

  const closeDesignPicker = () => {
    catalogAbortRef.current?.abort();
    setIsDesignPickerOpen(false);
  };

  const chooseDesign = (design: DesignOption) => {
    setSelectedDesign(design.id);
    setSelectedDesignInfo(design);
    setData('design_id', design.id);
    setData('customer_design_image', null);
    setDesignPreview(null);
    closeDesignPicker();
  };

  const chooseCustomDesign = () => {
    setSelectedDesign('custom');
    setSelectedDesignInfo(null);
    setData('design_id', null);
  };

  const handleCatalogSearch = () => {
    void loadCatalog(0, true, catalogSearch.trim(), catalogTag);
  };

  const handleCatalogTag = (tag: string | null) => {
    setCatalogTag(tag);
    void loadCatalog(0, true, catalogSearch.trim(), tag);
  };

  const hasMoreCatalogDesigns = catalogOffset < catalogTotal;

  useEffect(() => {
    if (!isDesignPickerOpen) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        catalogAbortRef.current?.abort();
        setIsDesignPickerOpen(false);
      }
    };
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [isDesignPickerOpen]);

  useEffect(() => () => catalogAbortRef.current?.abort(), []);

  const selectedSizeObj = useMemo(() => sizes.find((s) => s.id === selectedSize) ?? null, [sizes, selectedSize]);
  const isDieCutTooSmall = cutType === 'die-cut' && selectedSizeObj && Math.max(selectedSizeObj.width_cm, selectedSizeObj.height_cm) < 5;

  const priceCalculation = useMemo(() => {
    if (requestCustomSize || !selectedSize || !selectedSizeObj) return null;

    const qtyPerA3 = selectedSizeObj.qty_per_a3;
    if (!qtyPerA3) return null;

    const a3Sheets = Math.ceil(quantity / qtyPerA3);

    const match = priceSettings.find(
      (ps) => a3Sheets >= ps.qty_from && (ps.qty_to === null || a3Sheets <= ps.qty_to)
    );

    if (!match) return null;

    return {
      a3Sheets,
      pricePerA3: match.price_per_a3,
      total: a3Sheets * match.price_per_a3,
    };
  }, [selectedSize, selectedSizeObj, quantity, priceSettings, requestCustomSize]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('orders.store'));
  };

  return (
    <FrontendLayout>
      <Head title="Tempah Sticker" />
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-[1280px] px-4 py-10 lg:px-8">
          <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">Tempah Sticker</h1>
          <p className="mt-2 text-sm text-slate-500">Pilih design, saiz & kuantiti sticker anda.</p>

          {repeatOrder && (
            <div className="mt-4 flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-3.5">
              <RotateCcw className="h-5 w-5 shrink-0 text-brand-600" />
              <div>
                <p className="text-sm font-bold text-brand-900">Ulang Tempahan {repeatOrder.order_no}</p>
                <p className="text-xs text-brand-700">Butangan dari tempahan lama telah diisi. Sila semak dan ubah jika perlu.</p>
              </div>
            </div>
          )}

          <form onSubmit={handleSubmit} className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            {/* Left: Design Selection */}
            <div className="lg:col-span-2 space-y-8">
              {/* Step 1: Design */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">1</div>
                  <h2 className="text-lg font-bold text-slate-900">Pilih Design</h2>
                </div>

                <div className="mt-4 space-y-3">
                  <button
                    type="button"
                    onClick={openDesignPicker}
                    className="flex w-full items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-3 text-left transition hover:border-brand-300 hover:bg-brand-50/40"
                  >
                    <div className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                      {selectedDesignInfo?.image_url ? (
                        <img
                          src={selectedDesignInfo.image_url}
                          alt=""
                          className="h-full w-full object-cover"
                        />
                      ) : (
                        <ImageIcon className="h-7 w-7 text-slate-300" />
                      )}
                    </div>
                    <div className="min-w-0 flex-1">
                      <p className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Design dipilih</p>
                      <p className="mt-1 truncate text-sm font-bold text-slate-900">
                        {selectedDesign === 'custom' ? 'Custom / Design sendiri' : selectedDesignInfo?.name ?? 'Pilih daripada katalog'}
                      </p>
                      <p className="mt-0.5 text-xs text-slate-500">
                        {selectedDesignInfo?.category ?? 'Katalog design sticker'}
                      </p>
                    </div>
                    <span className="shrink-0 rounded-xl bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700">Tukar</span>
                  </button>

                  <button
                    type="button"
                    onClick={chooseCustomDesign}
                    className={`flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left transition ${
                      selectedDesign === 'custom'
                        ? 'border-brand-300 bg-brand-50'
                        : 'border-slate-200 bg-slate-50 hover:border-brand-200'
                    }`}
                  >
                    <span className="flex items-center gap-2 text-sm font-semibold text-slate-700">
                      <ImageIcon className="h-4 w-4 text-slate-400" />
                      Saya ada design sendiri
                    </span>
                    {selectedDesign === 'custom' && <Check className="h-4 w-4 text-brand-600" />}
                  </button>
                </div>
                <p className="mt-3 text-xs text-slate-400">Katalog dibuka apabila diperlukan. Imej dimuatkan secara berperingkat.</p>

                {selectedDesign === 'custom' && (
                  <div className="mt-4">
                    <label htmlFor="custom-desc" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Keterangan Design</label>
                    <textarea
                      id="custom-desc"
                      value={customDesc}
                      onChange={(e) => { setCustomDesc(e.target.value); setData('custom_description', e.target.value); }}
                      rows={3}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Terangkan design yang anda mahukan..."
                    />
                  </div>
                )}

                <div className="mt-5">
                  <label htmlFor="design-upload" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Hantar Design Sendiri (Pilihan)</label>
                  <div className="mt-1 flex items-center gap-4">
                    {designPreview ? (
                      <img src={designPreview} alt="Design preview" className="h-20 w-20 rounded-xl object-cover border border-slate-200" />
                    ) : (
                      <div className="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100">
                        <ImageIcon className="h-8 w-8 text-slate-300" />
                      </div>
                    )}
                    <div>
                      <input
                        id="design-upload"
                        type="file"
                        accept="image/*,application/pdf"
                        onChange={(e) => {
                          const file = e.target.files?.[0] ?? null;
                          setData('customer_design_image', file);
                          if (file) {
                            setSelectedDesign('custom');
                            setSelectedDesignInfo(null);
                            setData('design_id', null);
                          }
                          if (file) {
                            setDesignPreview(URL.createObjectURL(file));
                          } else {
                            setDesignPreview(null);
                          }
                        }}
                        className="text-sm"
                      />
                      <p className="mt-1 text-xs text-slate-400">JPG, PNG, PDF. Maks 10MB.</p>
                    </div>
                  </div>
                </div>
              </section>

              {/* Step 2: Size & Quantity */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">2</div>
                  <h2 className="text-lg font-bold text-slate-900">Saiz & Kuantiti</h2>
                </div>

                <div className="mt-4 space-y-4">
                  <div className="flex items-center gap-3">
                    <input
                      id="custom-size"
                      type="checkbox"
                      checked={requestCustomSize}
                      onChange={(e) => { setRequestCustomSize(e.target.checked); setSelectedSize(null); setData('size_id', null); }}
                      className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                    <label htmlFor="custom-size" className="text-sm font-medium text-slate-700">
                      Tiada saiz yang sesuai? Request saiz sendiri
                    </label>
                  </div>

                  {!requestCustomSize ? (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                      {sizes.map((size) => (
                        <button
                          key={size.id}
                          type="button"
                          onClick={() => { setSelectedSize(size.id); setData('size_id', size.id); }}
                          className={`rounded-xl border-2 px-4 py-3 text-left transition ${
                            selectedSize === size.id
                              ? 'border-brand-600 bg-brand-50'
                              : 'border-slate-200 bg-white hover:border-brand-200'
                          }`}
                        >
                          <p className="text-sm font-bold text-slate-900">{size.name}</p>
                          <p className="text-xs text-slate-500">{size.width_cm}cm x {size.height_cm}cm</p>
                          {size.qty_per_a3 && (
                            <p className="text-xs text-slate-400 mt-0.5">{size.qty_per_a3} sticker/A3</p>
                          )}
                        </button>
                      ))}
                    </div>
                  ) : (
                    <div>
                      <label htmlFor="req-size" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Saiz & Kuantiti yang Diinginkan</label>
                      <textarea
                        id="req-size"
                        value={customSizeDesc}
                        onChange={(e) => { setCustomSizeDesc(e.target.value); setData('requested_size', e.target.value); }}
                        rows={2}
                        className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        placeholder="cth: 5cm x 5cm, 500 pcs"
                      />
                    </div>
                  )}

                  <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Jenis Potong</p>
                    <div className="mt-2 grid grid-cols-2 gap-3">
                      <button
                        type="button"
                        onClick={() => { setCutType('standard'); setData('cut_type', 'standard'); }}
                        className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                          cutType === 'standard'
                            ? 'border-brand-600 bg-brand-50'
                            : 'border-slate-200 bg-white hover:border-brand-200'
                        }`}
                      >
                        <p className="text-sm font-bold text-slate-900">Standard</p>
                        <p className="text-xs text-slate-500">Potong segi empat</p>
                      </button>
                      <button
                        type="button"
                        onClick={() => { setCutType('die-cut'); setData('cut_type', 'die-cut'); }}
                        className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                          cutType === 'die-cut'
                            ? 'border-brand-600 bg-brand-50'
                            : 'border-slate-200 bg-white hover:border-brand-200'
                        }`}
                      >
                        <p className="text-sm font-bold text-slate-900">Potong Ikon / Bentuk</p>
                        <p className="text-xs text-slate-500">Mengikut bentuk sticker</p>
                      </button>
                    </div>
                    {isDieCutTooSmall && (
                      <p className="mt-2 flex items-start gap-1.5 text-xs text-rose-600">
                        <Info className="h-3.5 w-3.5 shrink-0" />
                        Potong ikut bentuk hanya boleh untuk saiz 5cm ke atas.
                      </p>
                    )}
                  </div>

                  <div>
                    <label htmlFor="qty" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Kuantiti</label>
                    <input
                      id="qty"
                      type="number"
                      min={1}
                      value={quantity}
                      onChange={(e) => { const q = parseInt(e.target.value) || 1; setQuantity(q); setData('quantity', q); }}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {selectedSizeObj && selectedSizeObj.qty_per_a3 && (
                      <p className="mt-1 text-xs text-slate-500">
                        ~{Math.ceil(quantity / selectedSizeObj.qty_per_a3)} helai A3
                      </p>
                    )}
                  </div>
                </div>
              </section>

              {/* Step 3: Customer Details */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">3</div>
                  <h2 className="text-lg font-bold text-slate-900">Maklumat Penghantaran</h2>
                </div>
                <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label htmlFor="c-name" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama</label>
                    <input
                      id="c-name"
                      type="text"
                      value={data.customer_name}
                      onChange={(e) => setData('customer_name', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {errors.customer_name && <p className="mt-1 text-xs text-rose-600">{errors.customer_name}</p>}
                  </div>
                  <div>
                    <label htmlFor="c-phone" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. Telefon</label>
                    <input
                      id="c-phone"
                      type="text"
                      value={data.customer_phone}
                      onChange={(e) => setData('customer_phone', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {errors.customer_phone && <p className="mt-1 text-xs text-rose-600">{errors.customer_phone}</p>}
                  </div>
                  <div className="sm:col-span-2">
                    <label htmlFor="c-address" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alamat Penghantaran</label>
                    <textarea
                      id="c-address"
                      value={data.customer_address}
                      onChange={(e) => setData('customer_address', e.target.value)}
                      rows={3}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {errors.customer_address && <p className="mt-1 text-xs text-rose-600">{errors.customer_address}</p>}
                  </div>
                </div>
              </section>
            </div>

            {/* Right: Summary */}
            <div className="lg:col-span-1">
              <div className="sticky top-24 frontend-flat-card p-6">
                <h3 className="text-lg font-bold text-slate-900">Ringkasan Tempahan</h3>

                <div className="mt-4 space-y-3">
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Design</span>
                    <span className="font-medium text-slate-900">
                      {selectedDesign === 'custom' ? 'Custom' : selectedDesignInfo?.name ?? '-'}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Saiz</span>
                    <span className="font-medium text-slate-900">
                      {requestCustomSize ? 'Custom' : sizes.find((s) => s.id === selectedSize)?.name ?? '-'}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Kuantiti</span>
                    <span className="font-medium text-slate-900">{quantity} pcs</span>
                  </div>
                  {priceCalculation && (
                    <div className="flex justify-between text-sm">
                      <span className="text-slate-500">Helai A3</span>
                      <span className="font-medium text-slate-900">{priceCalculation.a3Sheets}</span>
                    </div>
                  )}
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Potong</span>
                    <span className="font-medium text-slate-900">{cutType === 'die-cut' ? 'Ikut Bentuk' : 'Standard'}</span>
                  </div>
                  {data.customer_design_image && (
                    <div className="flex justify-between text-sm">
                      <span className="text-slate-500">Design Hantar</span>
                      <span className="font-medium text-emerald-600">Ya</span>
                    </div>
                  )}
                </div>

                {priceCalculation && (
                  <div className="mt-3 border-t border-slate-100 pt-3 space-y-1 text-xs text-slate-500">
                    <p>RM {priceCalculation.pricePerA3.toFixed(2)} × {priceCalculation.a3Sheets} A3</p>
                  </div>
                )}

                <div className="mt-1 border-t border-slate-100 pt-4">
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-bold text-slate-900">Jumlah</span>
                    <span className="text-xl font-extrabold text-brand-600">
                      {priceCalculation !== null ? `RM ${priceCalculation.total.toFixed(2)}` : 'Pending'}
                    </span>
                  </div>
                  {(requestCustomSize || (!selectedSizeObj?.qty_per_a3)) && (
                    <p className="mt-2 flex items-start gap-1.5 text-xs text-amber-600">
                      <Info className="h-3.5 w-3.5 shrink-0" />
                      Harga akan dimaklumkan selepas admin semak tempahan anda.
                    </p>
                  )}
                </div>

                {!auth.user ? (
                  <div className="mt-6 space-y-3">
                    <p className="text-xs text-slate-500">Anda perlu log masuk untuk checkout.</p>
                    <Link
                      href={route('member.login')}
                      className="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
                    >
                      Log Masuk untuk Mula Tempahan
                      <ChevronRight className="h-4 w-4" />
                    </Link>
                  </div>
                ) : (
                  <button
                    type="submit"
                    disabled={processing || (!selectedDesign && !customDesc) || (!requestCustomSize && !selectedSize) || !!isDieCutTooSmall}
                    className="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20 disabled:opacity-50"
                  >
                    <ShoppingCart className="h-4 w-4" />
                    {processing ? 'Menghantar...' : 'Hantar Tempahan'}
                  </button>
                )}

                {paymentSettings && (
                  <div className="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
                    <p className="font-semibold text-slate-700">Deposit: RM {paymentSettings.deposit_amount}</p>
                    <p>atau bayar penuh selepas confirm design.</p>
                  </div>
                )}
              </div>
            </div>
          </form>

          {isDesignPickerOpen && (
            <div className="fixed inset-0 z-50 flex items-center justify-center p-4" role="presentation">
              <button
                type="button"
                aria-label="Tutup katalog design"
                className="absolute inset-0 bg-slate-950/50"
                onClick={closeDesignPicker}
              />
              <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="design-picker-title"
                className="relative flex max-h-[calc(100dvh-2rem)] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
              >
                <div className="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">Katalog design</p>
                    <h2 id="design-picker-title" className="mt-1 text-xl font-bold text-slate-900">Pilih design sticker</h2>
                    <p className="mt-1 text-sm text-slate-500">Cari design yang sesuai. Imej dimuatkan mengikut halaman.</p>
                  </div>
                  <button
                    type="button"
                    onClick={closeDesignPicker}
                    aria-label="Tutup katalog design"
                    className="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>

                <div className="overflow-y-auto p-5 sm:p-6">
                  <div className="flex gap-2">
                    <div className="relative min-w-0 flex-1">
                      <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                      <input
                        type="search"
                        value={catalogSearch}
                        onChange={(event) => setCatalogSearch(event.target.value)}
                        onKeyDown={(event) => {
                          if (event.key === 'Enter') {
                            event.preventDefault();
                            handleCatalogSearch();
                          }
                        }}
                        aria-label="Cari design"
                        placeholder="Cari nama design..."
                        className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100"
                      />
                    </div>
                    <button
                      type="button"
                      onClick={handleCatalogSearch}
                      disabled={catalogLoading}
                      className="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700 disabled:opacity-50"
                    >
                      Cari
                    </button>
                  </div>

                  {catalogTags.length > 0 && (
                    <div className="mt-4 flex flex-wrap items-center gap-2">
                      <span className="text-xs font-semibold text-slate-400">#</span>
                      <button
                        type="button"
                        onClick={() => handleCatalogTag(null)}
                        className={`rounded-full px-3 py-1 text-[11px] font-bold transition ${
                          catalogTag === null
                            ? 'bg-brand-100 text-brand-700 ring-1 ring-brand-300'
                            : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                        }`}
                      >
                        Semua
                      </button>
                      {catalogTags.map((tag) => (
                        <button
                          key={tag}
                          type="button"
                          onClick={() => handleCatalogTag(catalogTag === tag ? null : tag)}
                          className={`rounded-full px-3 py-1 text-[11px] font-bold transition ${
                            catalogTag === tag
                              ? 'bg-brand-100 text-brand-700 ring-1 ring-brand-300'
                              : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                          }`}
                        >
                          #{tag}
                        </button>
                      ))}
                    </div>
                  )}

                  {previousDesigns.length > 0 && (
                    <div className="mt-5 rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                      <div className="flex items-center gap-2">
                        <RotateCcw className="h-4 w-4 text-brand-600" />
                        <p className="text-sm font-bold text-brand-900">Design yang pernah ditempah</p>
                      </div>
                      <div className="mt-3 flex gap-2 overflow-x-auto pb-1">
                        {previousDesigns.map((design) => (
                          <button
                            key={design.id}
                            type="button"
                            onClick={() => chooseDesign(design)}
                            className={`inline-flex shrink-0 items-center gap-2 rounded-xl border-2 bg-white px-3 py-2 text-left transition ${
                              selectedDesign === design.id ? 'border-brand-600' : 'border-brand-100 hover:border-brand-300'
                            }`}
                          >
                            <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                              <RotateCcw className="h-3.5 w-3.5" />
                            </span>
                            <span className="max-w-40 truncate text-xs font-semibold text-slate-700">
                              <span className="text-brand-500">#</span>{design.name}
                            </span>
                            {selectedDesign === design.id && <Check className="h-3.5 w-3.5 text-brand-600" />}
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  <div className="mt-6">
                    <div className="mb-3 flex items-center justify-between gap-3">
                      <p className="text-sm font-bold text-slate-900">
                        Semua design{catalogTotal > 0 ? ` (${catalogTotal})` : ''}
                      </p>
                      {catalogLoading && catalogDesigns.length > 0 && (
                        <span className="inline-flex items-center gap-1.5 text-xs text-slate-500">
                          <LoaderCircle className="h-3.5 w-3.5 animate-spin" />
                          Memuatkan
                        </span>
                      )}
                    </div>

                    {catalogLoading && catalogDesigns.length === 0 && (
                      <div className="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500">
                        <span className="inline-flex items-center gap-2">
                          <LoaderCircle className="h-4 w-4 animate-spin" />
                          Memuatkan katalog...
                        </span>
                      </div>
                    )}

                    {!catalogLoading && catalogError && (
                      <div className="rounded-2xl border border-rose-100 bg-rose-50 p-5 text-center">
                        <p className="text-sm font-semibold text-rose-700">Katalog tidak dapat dimuatkan.</p>
                        <button
                          type="button"
                          onClick={handleCatalogSearch}
                          className="mt-3 rounded-lg bg-white px-3 py-2 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-200 transition hover:bg-rose-100"
                        >
                          Cuba lagi
                        </button>
                      </div>
                    )}

                    {!catalogLoading && !catalogError && catalogLoaded && catalogDesigns.length === 0 && (
                      <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <p className="text-sm font-semibold text-slate-700">Design tidak dijumpai.</p>
                        <p className="mt-1 text-xs text-slate-500">Cuba kata carian yang lain.</p>
                      </div>
                    )}

                    {catalogDesigns.length > 0 && (
                      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        {catalogDesigns.map((design) => (
                          <button
                            key={design.id}
                            type="button"
                            onClick={() => chooseDesign(design)}
                            className={`relative overflow-hidden rounded-2xl border-2 text-left transition ${
                              selectedDesign === design.id
                                ? 'border-brand-600 shadow-sm shadow-brand-600/10'
                                : 'border-slate-200 hover:border-brand-300'
                            }`}
                          >
                            <div className="aspect-square bg-slate-100">
                              {design.image_url ? (
                                <img
                                  src={design.image_url}
                                  alt={design.name}
                                  loading="lazy"
                                  decoding="async"
                                  className="h-full w-full object-cover"
                                />
                              ) : (
                                <div className="flex h-full items-center justify-center">
                                  <ImageIcon className="h-8 w-8 text-slate-300" />
                                </div>
                              )}
                            </div>
                            <p className="truncate px-2.5 py-2 text-xs font-semibold text-slate-700">
                              <span className="text-brand-500">#</span>{design.name}
                            </p>
                            {selectedDesign === design.id && (
                              <span className="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-brand-600">
                                <Check className="h-3 w-3 text-white" />
                              </span>
                            )}
                          </button>
                        ))}
                      </div>
                    )}

                    {hasMoreCatalogDesigns && (
                      <button
                        type="button"
                        onClick={() => void loadCatalog(catalogOffset, false, catalogSearch.trim())}
                        disabled={catalogLoading}
                        className="mt-5 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-brand-300 hover:text-brand-700 disabled:opacity-50"
                      >
                        {catalogLoading && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Muatkan lagi design
                      </button>
                    )}
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </FrontendLayout>
  );
}
