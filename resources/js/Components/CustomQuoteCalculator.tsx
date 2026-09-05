import { Calculator } from 'lucide-react';
import { useEffect, useState } from 'react';
import { calculateBillableA3Sheets } from '@/lib/stickerPricing';

export interface CustomQuoteCalculatorItem {
  id: number;
  name: string;
  size: string;
  quantity: number;
  has_design: boolean;
  sticker_type?: string | null;
  quoted_qty_per_a3: number;
  quoted_price_per_a3: number | string;
}

interface CustomQuoteCalculatorProps {
  items: CustomQuoteCalculatorItem[];
  minimumA3SheetsWithoutDesign: number;
  className?: string;
}

export default function CustomQuoteCalculator({ items, minimumA3SheetsWithoutDesign, className = '' }: CustomQuoteCalculatorProps) {
  const [quantities, setQuantities] = useState<Record<number, string>>(() => Object.fromEntries(
    items.map((item) => [item.id, String(item.quantity)]),
  ));

  useEffect(() => {
    setQuantities((current) => {
      let changed = false;
      const next = { ...current };

      items.forEach((item) => {
        if (next[item.id] === undefined) {
          next[item.id] = String(item.quantity);
          changed = true;
        }
      });

      return changed ? next : current;
    });
  }, [items]);

  const calculate = (item: CustomQuoteCalculatorItem) => {
    const quantity = Number(quantities[item.id] ?? item.quantity);
    const qtyPerA3 = Number(item.quoted_qty_per_a3);
    const pricePerA3 = Number(item.quoted_price_per_a3);

    if (!Number.isInteger(quantity) || quantity < 1 || !Number.isInteger(qtyPerA3) || qtyPerA3 < 1 || !Number.isFinite(pricePerA3) || pricePerA3 <= 0) {
      return null;
    }

    const a3Sheets = calculateBillableA3Sheets(quantity, qtyPerA3, item.has_design, minimumA3SheetsWithoutDesign);

    return {
      total: a3Sheets * pricePerA3,
    };
  };

  if (items.length === 0) return null;

  return (
    <section className={['rounded-2xl border border-brand-100 bg-brand-50/70 p-5 sm:p-6', className].filter(Boolean).join(' ')}>
      <div className="flex items-start gap-3">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600">
          <Calculator className="h-5 w-5" />
        </div>
        <div>
          <h2 className="text-lg font-bold text-brand-900">Kiraan harga saiz custom</h2>
          <p className="mt-1 text-sm leading-relaxed text-brand-800">Masukkan kuantiti lain untuk dapatkan anggaran harga sendiri.</p>
        </div>
      </div>
      <div className="mt-5 grid gap-3 lg:grid-cols-2">
        {items.map((item) => {
          const calculation = calculate(item);

          return (
            <div key={item.id} className="rounded-2xl border border-brand-100 bg-white p-4">
              <div className="flex flex-wrap items-baseline justify-between gap-2">
                <p className="text-sm font-bold text-slate-900">{item.name}</p>
                <p className="text-xs text-slate-500">{item.size}</p>
              </div>
              <p className="mt-1 text-xs text-slate-500">Harga khas admin{item.sticker_type ? ` • ${item.sticker_type}` : ''}</p>
              <label htmlFor={`custom-quote-quantity-${item.id}`} className="mt-4 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kuantiti untuk kiraan (pcs)</label>
              <input
                id={`custom-quote-quantity-${item.id}`}
                type="number"
                min="1"
                step="1"
                value={quantities[item.id] ?? String(item.quantity)}
                onChange={(event) => setQuantities((current) => ({ ...current, [item.id]: event.target.value }))}
                className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
              {calculation ? (
                <div className="mt-3 rounded-xl bg-emerald-50 px-3 py-2.5 text-sm text-emerald-800" aria-live="polite">
                  <p className="font-semibold">Anggaran harga berdasarkan kuantiti yang dimasukkan.</p>
                  <p className="mt-1 font-bold">Anggaran: RM {calculation.total.toFixed(2)}</p>
                </div>
              ) : (
                <p className="mt-3 text-xs text-rose-600">Masukkan kuantiti bulat yang lebih besar daripada 0.</p>
              )}
            </div>
          );
        })}
      </div>
      <p className="mt-4 text-xs leading-relaxed text-brand-700">Anggaran ini menggunakan kadar yang admin tetapkan untuk order anda. Hubungi admin jika kuantiti atau spesifikasi berubah.</p>
    </section>
  );
}
