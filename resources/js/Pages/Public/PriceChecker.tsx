import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import { DollarSign, Ruler, Info } from 'lucide-react';

interface Size {
  id: number;
  name: string;
  width_cm: number;
  height_cm: number;
  shape: string | null;
  qty_per_a3: number | null;
}

interface PriceSetting {
  id: number;
  sticker_type: string;
  qty_from: number;
  qty_to: number | null;
  price_per_a3: number;
}

interface PriceCheckerProps {
  sizes: Size[];
  priceSettings: PriceSetting[];
}

export default function PriceChecker({ sizes, priceSettings }: PriceCheckerProps) {
  const [selectedSizeId, setSelectedSizeId] = useState<number | ''>('');
  const [quantity, setQuantity] = useState(100);

  const selectedSize = useMemo(
    () => sizes.find((s) => s.id === selectedSizeId) ?? null,
    [sizes, selectedSizeId]
  );

  const calculation = useMemo(() => {
    if (!selectedSize || !selectedSize.qty_per_a3) return null;

    const a3Sheets = Math.ceil(quantity / selectedSize.qty_per_a3);
    const match = priceSettings.find(
      (ps) => a3Sheets >= ps.qty_from && (ps.qty_to === null || a3Sheets <= ps.qty_to)
    );
    if (!match) return null;

    return {
      a3Sheets,
      pricePerA3: match.price_per_a3,
      total: a3Sheets * match.price_per_a3,
    };
  }, [selectedSize, quantity, priceSettings]);

  return (
    <FrontendLayout>
      <Head title="Semak Harga Sticker" />
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-[800px] px-4 py-12 lg:px-8">
          <div className="text-center mb-10">
            <div className="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-brand-100 mb-4">
              <DollarSign className="h-7 w-7 text-brand-600" />
            </div>
            <h1 className="text-3xl font-extrabold tracking-tight text-slate-900">Semak Harga Sticker</h1>
            <p className="mt-2 text-slate-500">Pilih saiz dan masukkan kuantiti untuk lihat anggaran harga.</p>
          </div>

          <div className="frontend-flat-card p-6 space-y-6">
            {/* Size Select */}
            <div>
              <p className="flex items-center gap-2 text-sm font-semibold text-slate-700 mb-2">
                <Ruler className="h-4 w-4 text-brand-600" />
                Pilih Saiz
              </p>
              <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                {sizes.map((size) => (
                  <button
                    key={size.id}
                    type="button"
                    onClick={() => setSelectedSizeId(size.id)}
                    className={`rounded-xl border-2 px-4 py-3 text-left transition ${
                      selectedSizeId === size.id
                        ? 'border-brand-600 bg-brand-50'
                        : 'border-slate-200 bg-white hover:border-brand-200'
                    }`}
                  >
                    <p className="text-sm font-bold text-slate-900">{size.name}</p>
                    <p className="text-xs text-slate-500">
                      {size.width_cm}cm × {size.height_cm}cm
                    </p>
                    {size.shape && (
                      <p className="text-xs text-slate-400 mt-0.5">{size.shape}</p>
                    )}
                  </button>
                ))}
              </div>
            </div>

            {/* Quantity */}
            <div>
              <p className="flex items-center gap-2 text-sm font-semibold text-slate-700 mb-2">
                <Ruler className="h-4 w-4 text-brand-600" />
                Kuantiti
              </p>
              <input
                type="number"
                min={1}
                value={quantity}
                onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-lg text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>

            {/* Result */}
            {calculation && (
              <div className="rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100/50 border border-brand-200 p-6 space-y-3">
                <h3 className="text-sm font-bold text-brand-800 uppercase tracking-wider">Anggaran Harga</h3>

                <div className="space-y-2 text-sm">
                  <div className="flex justify-between">
                    <span className="text-slate-600">Kuantiti</span>
                    <span className="font-semibold text-slate-900">{quantity} sticker</span>
                  </div>
                  {selectedSize?.qty_per_a3 && (
                    <div className="flex justify-between">
                      <span className="text-slate-600">Sticker per A3</span>
                      <span className="font-semibold text-slate-900">{selectedSize.qty_per_a3} sticker</span>
                    </div>
                  )}
                  <div className="flex justify-between">
                    <span className="text-slate-600">Helai A3 diperlukan</span>
                    <span className="font-semibold text-slate-900">{calculation.a3Sheets} helai</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-600">Harga per A3</span>
                    <span className="font-semibold text-slate-900">RM {calculation.pricePerA3.toFixed(2)}</span>
                  </div>
                  <div className="border-t border-brand-200 pt-2 flex justify-between">
                    <span className="font-bold text-slate-900">Jumlah</span>
                    <span className="text-xl font-extrabold text-brand-600">RM {calculation.total.toFixed(2)}</span>
                  </div>
                </div>

                <p className="text-xs text-slate-500 flex items-start gap-1.5 pt-2 border-t border-brand-200">
                  <Info className="h-3.5 w-3.5 shrink-0 mt-0.5" />
                  Harga ini adalah anggaran berdasarkan tetapan harga semasa. Harga sebenar mungkin berbeza selepas pengesahan admin.
                </p>
              </div>
            )}

            {selectedSize && !selectedSize.qty_per_a3 && (
              <div className="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-start gap-2">
                <Info className="h-4 w-4 shrink-0 mt-0.5" />
                Saiz ini belum mempunyai tetapan kuantiti per A3. Sila hubungi admin untuk semakan harga.
              </div>
            )}
          </div>
        </div>
      </div>
    </FrontendLayout>
  );
}
