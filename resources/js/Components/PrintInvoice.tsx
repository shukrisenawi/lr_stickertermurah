import { type ReactNode } from 'react';

export interface PrintInvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit_price: number;
  line_total: number;
}

export interface PrintInvoiceProps {
  invoiceNo: string;
  issueDate: string;
  amount: number;
  customerName: string;
  customerPhone: string;
  customerAddress: string;
  items: PrintInvoiceItem[];
  notes?: string | null;
  paymentStatus?: string;
  paymentType?: string | null;
  paidAt?: string | null;
  trackingNo?: string | null;
  brandName?: string;
  brandTagline?: string;
  brandPhone?: string;
  brandEmail?: string;
  logoUrl?: string | null;
  children?: ReactNode;
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('ms-MY', { day: '2-digit', month: 'long', year: 'numeric' });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('ms-MY', {
    style: 'currency',
    currency: 'MYR',
    minimumFractionDigits: 2,
  }).format(Number(amount));
};

const paymentStatusLabels: Record<string, { label: string; class: string }> = {
  unpaid: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-800 border-amber-200' },
  submitted: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-800 border-amber-200' },
  rejected: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-800 border-amber-200' },
  partial: { label: 'Bayaran Separa', class: 'bg-violet-100 text-violet-800 border-violet-200' },
  paid: { label: 'Telah Bayar', class: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
};

export default function PrintInvoice({
  invoiceNo,
  issueDate,
  amount,
  customerName,
  customerPhone,
  customerAddress,
  items,
  notes,
  paymentStatus = 'unpaid',
  paymentType,
  paidAt,
  trackingNo,
  brandName = 'StickerTermurah',
  brandTagline = 'SH Best Creative Design',
  brandPhone = '011-69409606',
  brandEmail = 'stickertermurah@gmail.com',
  logoUrl,
  children,
}: PrintInvoiceProps) {
  const statusInfo = paymentStatusLabels[paymentStatus] ?? paymentStatusLabels.unpaid;
  const totalQty = items.reduce((sum, i) => sum + Number(i.quantity), 0);

  return (
    <div className="invoice-preview mx-auto w-full max-w-[850px]">
      <article className="invoice-print-area">
        <header className="invoice-hero relative overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white px-5 py-6 sm:px-7 sm:py-7">
          <div className="relative flex flex-col gap-7 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex min-w-0 items-center gap-4">
              {logoUrl ? (
                <img src={logoUrl} alt={brandName} className="h-16 w-16 shrink-0 object-contain" />
              ) : (
                <span className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-2xl font-extrabold text-brand-600 shadow-sm">
                  ST
                </span>
              )}
              <div className="min-w-0">
                <h1 className="font-display text-2xl font-extrabold tracking-tight text-slate-900">
                  {brandName}
                </h1>
                <p className="mt-0.5 text-xs font-medium tracking-wide text-slate-500">{brandTagline}</p>
              </div>
            </div>

            <div className="sm:text-right">
              <p className="text-[10px] font-bold uppercase tracking-[0.24em] text-brand-600">Dokumen Invoice</p>
              <h2 className="mt-1 text-4xl font-black uppercase leading-none tracking-[-0.04em] text-slate-900">Invoice</h2>
              <div className="mt-3 inline-flex flex-col rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 sm:items-end">
                <span className="text-sm font-bold tracking-wide text-slate-900">{invoiceNo}</span>
                <span className="mt-0.5 text-[11px] font-medium text-slate-500">Dikeluarkan {formatDate(issueDate)}</span>
              </div>
            </div>
          </div>
        </header>

        <section className="mt-6 grid gap-4 sm:grid-cols-[minmax(0,1.35fr)_minmax(15rem,0.65fr)]">
          <div className="rounded-2xl border border-slate-200 bg-white/90 p-5">
            <div className="flex items-center gap-2">
              <span className="h-4 w-1 rounded-full bg-brand-600" />
              <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Kepada</p>
            </div>
            <div className="mt-3">
              <p className="text-lg font-extrabold tracking-tight text-slate-900">{customerName || '-'}</p>
              <p className="mt-1 text-sm font-medium text-slate-600">{customerPhone || '-'}</p>
              <p className="mt-1 max-w-md whitespace-pre-line text-sm leading-relaxed text-slate-600">{customerAddress || '-'}</p>
              {trackingNo && (
                <div className="mt-3 inline-flex rounded-lg border border-brand-100 bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700">
                  No. Tracking J&amp;T: {trackingNo}
                </div>
              )}
            </div>
          </div>

          <div className="rounded-2xl border border-brand-100 bg-brand-50/80 p-5 sm:text-right">
            <p className="text-[10px] font-bold uppercase tracking-[0.2em] text-brand-700">Status Bayaran</p>
            <span className={`mt-2 inline-flex rounded-full border px-3.5 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] ${statusInfo.class}`}>
              {statusInfo.label}
            </span>
            {paymentType && (
              <div className="mt-4 border-t border-brand-100 pt-3">
                <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Jenis Bayaran</p>
                <p className="mt-1 text-sm font-bold text-slate-700">
                  {paymentType === 'deposit' ? 'Deposit' : paymentType === 'custom' ? 'Jumlah Lain' : 'Bayaran Penuh'}
                </p>
              </div>
            )}
            {paidAt && (
              <p className="mt-2 text-xs font-semibold text-emerald-700">
                Dibayar pada {formatDate(paidAt)}
              </p>
            )}
          </div>
        </section>

        <section className="mt-6">
          <div className="mb-3 flex items-end justify-between gap-4">
            <div>
              <p className="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-900">Butiran Tempahan</p>
              <p className="mt-0.5 text-[11px] text-slate-500">Senarai produk dan jumlah yang dikenakan</p>
            </div>
            <p className="shrink-0 text-[11px] font-bold text-slate-500">{items.length} item</p>
          </div>

          <div className="invoice-table-wrap overflow-x-auto rounded-2xl border border-slate-200 bg-white/95">
            <table className="invoice-items-table w-full min-w-[620px] border-collapse text-left">
              <thead>
                <tr className="bg-slate-900">
                  <th className="w-[8%] px-4 py-3.5 text-[9px] font-bold uppercase tracking-[0.16em] text-white/70">Bil</th>
                  <th className="w-[40%] px-4 py-3.5 text-[9px] font-bold uppercase tracking-[0.16em] text-white/70">Penerangan</th>
                  <th className="w-[13%] px-4 py-3.5 text-right text-[9px] font-bold uppercase tracking-[0.16em] text-white/70">Kuantiti</th>
                  <th className="w-[19%] px-4 py-3.5 text-right text-[9px] font-bold uppercase tracking-[0.16em] text-white/70">Harga Unit</th>
                  <th className="w-[20%] px-4 py-3.5 text-right text-[9px] font-bold uppercase tracking-[0.16em] text-brand-200">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                {items.length === 0 ? (
                  <tr className="invoice-empty-row">
                    <td colSpan={5} className="px-4 py-10 text-center text-sm text-slate-500">
                      Tiada item dalam invoice ini.
                    </td>
                  </tr>
                ) : (
                  items.map((item, idx) => (
                    <tr key={item.id} className="border-b border-slate-100 last:border-0 odd:bg-white even:bg-slate-50/70">
                      <td data-label="Bil" className="px-4 py-3.5 align-top text-sm font-semibold text-slate-500">{idx + 1}</td>
                      <td data-label="Penerangan" className="px-4 py-3.5 align-top text-sm font-semibold leading-relaxed text-slate-900">{item.description}</td>
                      <td data-label="Kuantiti" className="px-4 py-3.5 text-right align-top text-sm tabular-nums text-slate-600">{item.quantity}</td>
                      <td data-label="Harga Unit" className="whitespace-nowrap px-4 py-3.5 text-right align-top text-sm tabular-nums text-slate-600">{formatCurrency(item.unit_price)}</td>
                      <td data-label="Jumlah" className="whitespace-nowrap px-4 py-3.5 text-right align-top text-sm font-extrabold tabular-nums text-slate-900">{formatCurrency(item.line_total)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </section>

        <section className="mt-6 grid items-start gap-4 sm:grid-cols-[minmax(0,1fr)_19rem]">
          {notes && (
            <div className="rounded-2xl border border-slate-200 bg-slate-50/90 p-4">
              <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Nota</p>
              <p className="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-600">{notes}</p>
            </div>
          )}

          <div className={`invoice-total-card overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 text-slate-900 ${notes ? '' : 'sm:col-start-2'}`}>
            <div className="flex items-center justify-between gap-4 text-xs">
              <span className="font-medium text-slate-500">Jumlah Kuantiti</span>
              <span className="font-bold tabular-nums text-slate-900">{totalQty} unit</span>
            </div>
            <div className="mt-4 border-t border-slate-200 pt-4">
              <p className="text-[9px] font-bold uppercase tracking-[0.2em] text-brand-600">Jumlah Bayaran</p>
              <p className="mt-1 text-right text-3xl font-black tracking-tight text-slate-900 tabular-nums">{formatCurrency(amount)}</p>
            </div>
          </div>
        </section>

        <footer className="invoice-footer mt-auto pt-10">
          <div className="flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p className="text-sm font-extrabold text-slate-900">Terima kasih atas tempahan anda.</p>
              <p className="mt-1 max-w-sm text-[10px] leading-relaxed text-slate-500">
                Invoice ini dijana secara elektronik dan sah tanpa tandatangan.
              </p>
            </div>
            <div className="text-[11px] leading-relaxed text-slate-500 sm:text-right">
              <p className="font-bold text-slate-700">{brandName}</p>
              <p>{brandPhone}</p>
              <p>{brandEmail}</p>
            </div>
          </div>
          <div className="invoice-footer-stripe mt-5 h-1.5 w-full rounded-full" />
        </footer>
      </article>

      {children && (
        <div className="invoice-no-print mt-4 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          {children}
        </div>
      )}
    </div>
  );
}
