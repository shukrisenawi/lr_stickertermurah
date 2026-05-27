import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useState, useMemo } from 'react';
import { Link } from '@inertiajs/react';
import { ShoppingCart, Image as ImageIcon, Check, ChevronRight, Info } from 'lucide-react';

interface OrderFormProps extends PageProps {
  designs: Array<{
    id: number;
    name: string;
    image_url: string | null;
    category: { name: string } | null;
  }>;
  sizes: Array<{
    id: number;
    name: string;
    width_cm: number;
    height_cm: number;
    qty_per_a3: number | null;
    is_active: boolean;
  }>;
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
  selectedDesignId: number | null;
}

export default function OrderForm() {
  const { app, designs, sizes, priceSettings, paymentSettings, selectedDesignId, auth } = usePage<OrderFormProps>().props;

  const [selectedDesign, setSelectedDesign] = useState<number | 'custom'>(
    selectedDesignId ? selectedDesignId : 'custom'
  );
  const [customDesc, setCustomDesc] = useState('');
  const [selectedSize, setSelectedSize] = useState<number | null>(null);
  const [quantity, setQuantity] = useState(100);
  const [requestCustomSize, setRequestCustomSize] = useState(false);
  const [customSizeDesc, setCustomSizeDesc] = useState('');
  const [cutType, setCutType] = useState<'standard' | 'die-cut'>('standard');
  const [designPreview, setDesignPreview] = useState<string | null>(null);

  const { data, setData, post, processing, errors } = useForm({
    design_id: selectedDesignId,
    custom_description: '',
    size_id: null as number | null,
    requested_size: '',
    quantity: 100,
    cut_type: 'standard' as 'standard' | 'die-cut',
    customer_design_image: null as File | null,
    customer_name: auth.user?.name ?? '',
    customer_phone: '',
    customer_address: '',
  });

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

          <form onSubmit={handleSubmit} className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            {/* Left: Design Selection */}
            <div className="lg:col-span-2 space-y-8">
              {/* Step 1: Design */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">1</div>
                  <h2 className="text-lg font-bold text-slate-900">Pilih Design</h2>
                </div>

                <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                  <button
                    type="button"
                    onClick={() => { setSelectedDesign('custom'); setData('design_id', null); }}
                    className={`relative flex flex-col items-center justify-center gap-2 rounded-2xl border-2 p-4 transition ${
                      selectedDesign === 'custom'
                        ? 'border-brand-600 bg-brand-50'
                        : 'border-slate-200 bg-white hover:border-brand-200'
                    }`}
                  >
                    <ImageIcon className="h-8 w-8 text-slate-400" />
                    <span className="text-xs font-semibold text-slate-700">Custom / Sendiri</span>
                    {selectedDesign === 'custom' && (
                      <span className="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-brand-600">
                        <Check className="h-3 w-3 text-white" />
                      </span>
                    )}
                  </button>

                  {designs.map((design) => (
                    <button
                      key={design.id}
                      type="button"
                      onClick={() => { setSelectedDesign(design.id); setData('design_id', design.id); }}
                      className={`relative overflow-hidden rounded-2xl border-2 transition ${
                        selectedDesign === design.id
                          ? 'border-brand-600'
                          : 'border-slate-200 hover:border-brand-200'
                      }`}
                    >
                      <div className="aspect-square">
                        <img
                          src={design.image_url || app.logo_url}
                          alt={design.name}
                          className="h-full w-full object-cover"
                        />
                      </div>
                      <p className="truncate px-2 py-1.5 text-center text-[11px] font-medium text-slate-700">{design.name}</p>
                      {selectedDesign === design.id && (
                        <span className="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-brand-600">
                          <Check className="h-3 w-3 text-white" />
                        </span>
                      )}
                    </button>
                  ))}
                </div>

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
                      {selectedDesign === 'custom' ? 'Custom' : designs.find((d) => d.id === selectedDesign)?.name ?? '-'}
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
                      Log Masuk untuk Checkout
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
                    {processing ? 'Menghantar...' : 'Checkout'}
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
        </div>
      </div>
    </FrontendLayout>
  );
}
