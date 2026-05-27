import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import { Search, X, Phone, Info } from 'lucide-react';

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
  stickerTypes: string[];
  paymentSettings: { admin_phone: string } | null;
}

export default function PriceChecker({ sizes, priceSettings, stickerTypes, paymentSettings }: PriceCheckerProps) {
  const [stickerType, setStickerType] = useState(stickerTypes[0] ?? 'Mirrorcote');
  const [width, setWidth] = useState('');
  const [height, setHeight] = useState('');
  const [shape, setShape] = useState('');
  const [quantity, setQuantity] = useState('100');
  const [showPopup, setShowPopup] = useState(false);

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

    const a3Sheets = Math.ceil(q / matchedSize.qty_per_a3);
    const match = priceSettings.find(
      (ps) => ps.sticker_type === stickerType && a3Sheets >= ps.qty_from && (ps.qty_to === null || a3Sheets <= ps.qty_to)
    );
    if (!match) return null;

    return { a3Sheets, pricePerA3: match.price_per_a3, total: a3Sheets * match.price_per_a3 };
  }, [matchedSize, quantity, priceSettings, stickerType]);

  const needsAdmin = matchedSize && (!matchedSize.qty_per_a3 || !calculation);

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

  const adminPhone = paymentSettings?.admin_phone ?? '01123456789';
  const waUrl = `https://wa.me/${adminPhone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(
    `Hi, saya nak tanya harga sticker:\n- Jenis: ${stickerType}\n- Lebar: ${width || '?'}cm\n- Tinggi: ${height || '?'}cm\n- Bentuk: ${shape || '-'}\n- Kuantiti: ${quantity || '?'} pcs`
  )}`;

  return (
    <FrontendLayout>
      <Head title="Semak Harga Sticker" />
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-[800px] px-4 py-12 lg:px-8">
          <div className="text-center mb-10">
            <div className="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-brand-100 mb-4">
              <Search className="h-7 w-7 text-brand-600" />
            </div>
            <h1 className="text-3xl font-extrabold tracking-tight text-slate-900">Semak Harga Sticker</h1>
            <p className="mt-2 text-slate-500">Masukkan jenis, saiz dan kuantiti untuk semak anggaran harga.</p>
          </div>

          <div className="frontend-flat-card p-6 space-y-6">
            {/* Sticker Type */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Jenis Sticker</p>
              <select
                value={stickerType}
                onChange={(e) => setStickerType(e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              >
                {stickerTypes.map((t) => (
                  <option key={t} value={t}>{t}</option>
                ))}
              </select>
            </div>

            {/* Width & Height */}
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Lebar (cm)</p>
                <input
                  type="number"
                  step="0.1"
                  min="0"
                  value={width}
                  onChange={(e) => setWidth(e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  placeholder="cth: 5"
                />
              </div>
              <div>
                <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Tinggi (cm)</p>
                <input
                  type="number"
                  step="0.1"
                  min="0"
                  value={height}
                  onChange={(e) => setHeight(e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  placeholder="cth: 5"
                />
              </div>
            </div>

            {/* Shape */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Bentuk</p>
              <select
                value={shape}
                onChange={(e) => setShape(e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              >
                <option value="">Pilih bentuk</option>
                <option value="Petak">Petak</option>
                <option value="Segi Empat">Segi Empat</option>
                <option value="Bulat">Bulat</option>
                <option value="Oval">Oval</option>
                <option value="Bebas">Bebas / Custom</option>
              </select>
            </div>

            {/* Quantity */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Kuantiti</p>
              <input
                type="number"
                min="1"
                value={quantity}
                onChange={(e) => setQuantity(e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="cth: 100"
              />
            </div>

            {/* Cek Harga Button */}
            <button
              type="button"
              onClick={handleCek}
              className="w-full rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
            >
              Cek Harga
            </button>
          </div>

          {/* Result */}
          {calculation && (
            <div className="mt-6 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-200 p-6 space-y-3">
              <h3 className="text-sm font-bold text-emerald-800 uppercase tracking-wider">Anggaran Harga</h3>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-slate-600">Saiz</span>
                  <span className="font-semibold text-slate-900">{width}cm × {height}cm</span>
                </div>
                {shape && (
                  <div className="flex justify-between">
                    <span className="text-slate-600">Bentuk</span>
                    <span className="font-semibold text-slate-900">{shape}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-slate-600">Jenis</span>
                  <span className="font-semibold text-slate-900">{stickerType}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-600">Kuantiti</span>
                  <span className="font-semibold text-slate-900">{parseInt(quantity).toLocaleString()} sticker</span>
                </div>
                {matchedSize?.qty_per_a3 && (
                  <div className="flex justify-between">
                    <span className="text-slate-600">Sticker per A3</span>
                    <span className="font-semibold text-slate-900">{matchedSize.qty_per_a3} sticker</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span className="text-slate-600">Helai A3</span>
                  <span className="font-semibold text-slate-900">{calculation.a3Sheets} helai</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-600">Harga per A3</span>
                  <span className="font-semibold text-slate-900">RM {calculation.pricePerA3.toFixed(2)}</span>
                </div>
                <div className="border-t border-emerald-200 pt-2 flex justify-between">
                  <span className="font-bold text-slate-900">Jumlah</span>
                  <span className="text-xl font-extrabold text-emerald-600">RM {calculation.total.toFixed(2)}</span>
                </div>
              </div>
              <p className="text-xs text-slate-500 flex items-start gap-1.5 pt-2 border-t border-emerald-200">
                <Info className="h-3.5 w-3.5 shrink-0 mt-0.5" />
                Anggaran berdasarkan tetapan harga semasa. Harga sebenar mungkin berbeza.
              </p>
            </div>
          )}

          {matchedSize && !calculation && !needsAdmin && (
            <div className="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-start gap-2">
              <Info className="h-4 w-4 shrink-0 mt-0.5" />
              Sila isi semua ruangan untuk semak harga.
            </div>
          )}
        </div>
      </div>

      {/* WhatsApp Popup */}
      {showPopup && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
          <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-bold text-slate-900">Hubungi Kami</h3>
              <button type="button" onClick={() => setShowPopup(false)} className="rounded-lg p-1 text-slate-400 hover:bg-slate-100">
                <X className="h-5 w-5" />
              </button>
            </div>
            <p className="text-sm text-slate-600 mb-5">
              {!matchedSize
                ? 'Saiz yang anda pilih tiada dalam senarai kami. Sila hubungi admin untuk sebut harga.'
                : 'Saiz ini belum mempunyai tetapan harga. Sila hubungi admin untuk sebut harga.'}
            </p>
            <div className="rounded-xl bg-slate-50 p-4 mb-5 space-y-1 text-sm">
              <p className="font-medium text-slate-700">Maklumat pertanyaan:</p>
              <p className="text-slate-600">Jenis: {stickerType}</p>
              <p className="text-slate-600">Saiz: {width || '?'}cm × {height || '?'}cm</p>
              {shape && <p className="text-slate-600">Bentuk: {shape}</p>}
              <p className="text-slate-600">Kuantiti: {quantity || '?'} pcs</p>
            </div>
            <a
              href={waUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 shadow-lg"
            >
              <Phone className="h-4 w-4" />
              WhatsApp Admin
            </a>
            <button
              type="button"
              onClick={() => setShowPopup(false)}
              className="mt-3 w-full rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
            >
              Tutup
            </button>
          </div>
        </div>
      )}
    </FrontendLayout>
  );
}
