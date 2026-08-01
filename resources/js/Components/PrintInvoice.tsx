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
  submitted: { label: 'Menunggu Pengesahan', class: 'bg-blue-100 text-blue-800 border-blue-200' },
  paid: { label: 'Dibayar', class: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
  rejected: { label: 'Ditolak', class: 'bg-rose-100 text-rose-800 border-rose-200' },
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
    <div className="invoice-print-area mx-auto max-w-[800px] bg-white p-8 sm:p-10 print:p-0">
      {/* Header */}
      <div className="flex flex-col items-start justify-between gap-6 border-b-2 border-slate-800 pb-6 sm:flex-row sm:items-center">
        <div className="flex items-center gap-4">
          {logoUrl ? (
            <img src={logoUrl} alt={brandName} className="h-16 w-16 rounded-xl object-contain" />
          ) : (
            <div className="flex h-16 w-16 items-center justify-center rounded-xl bg-brand-600 text-2xl font-bold text-white">
              ST
            </div>
          )}
          <div>
            <h1 className="font-display text-2xl font-extrabold tracking-tight text-slate-900">
              {brandName}
            </h1>
            <p className="text-xs font-medium text-slate-500">{brandTagline}</p>
          </div>
        </div>
        <div className="text-right">
          <h2 className="text-3xl font-extrabold uppercase tracking-tight text-slate-900">Invoice</h2>
          <p className="mt-1 text-sm font-semibold text-slate-700">{invoiceNo}</p>
          <p className="text-xs text-slate-500">{formatDate(issueDate)}</p>
        </div>
      </div>

      {/* Bill To + Status */}
      <div className="mt-6 flex flex-col gap-6 sm:flex-row sm:justify-between">
        <div className="flex-1">
          <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Ditagihkan Kepada</p>
          <div className="mt-2 space-y-0.5">
            <p className="text-base font-bold text-slate-900">{customerName || '-'}</p>
            <p className="text-sm text-slate-600">{customerPhone || '-'}</p>
            <p className="max-w-sm text-sm text-slate-600">{customerAddress || '-'}</p>
          </div>
        </div>
        <div className="sm:text-right">
          <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Status Bayaran</p>
          <span className={`mt-2 inline-flex rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider ${statusInfo.class}`}>
            {statusInfo.label}
          </span>
          {paymentType && (
            <p className="mt-2 text-xs font-medium text-slate-500">
              Jenis: {paymentType === 'deposit' ? 'Deposit' : 'Bayaran Penuh'}
            </p>
          )}
          {paidAt && (
            <p className="mt-1 text-xs font-medium text-emerald-600">
              Dibayar pada {formatDate(paidAt)}
            </p>
          )}
        </div>
      </div>

      {/* Items Table */}
      <div className="mt-8 overflow-hidden rounded-2xl border border-slate-200">
        <table className="w-full border-collapse text-left">
          <thead>
            <tr className="border-b border-slate-200 bg-slate-800">
              <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-[0.16em] text-white">Bil</th>
              <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-[0.16em] text-white">Penerangan</th>
              <th className="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.16em] text-white">Kuantiti</th>
              <th className="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.16em] text-white">Harga Unit</th>
              <th className="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.16em] text-white">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 ? (
              <tr>
                <td colSpan={5} className="px-4 py-10 text-center text-sm text-slate-400">
                  Tiada item dalam invoice ini.
                </td>
              </tr>
            ) : (
              items.map((item, idx) => (
                <tr key={item.id} className="border-b border-slate-100 last:border-0">
                  <td className="px-4 py-3 text-sm font-medium text-slate-500">{idx + 1}</td>
                  <td className="px-4 py-3 text-sm font-medium text-slate-900">{item.description}</td>
                  <td className="px-4 py-3 text-right text-sm text-slate-700">{item.quantity}</td>
                  <td className="px-4 py-3 text-right text-sm text-slate-700">{formatCurrency(item.unit_price)}</td>
                  <td className="px-4 py-3 text-right text-sm font-bold text-slate-900">{formatCurrency(item.line_total)}</td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {/* Totals */}
      <div className="mt-6 flex flex-col gap-4 sm:flex-row sm:justify-between">
        <div className="flex-1">
          {notes && (
            <div className="rounded-xl bg-slate-50 p-4">
              <p className="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Nota</p>
              <p className="mt-1 text-sm text-slate-600">{notes}</p>
            </div>
          )}
        </div>
        <div className="w-full sm:w-72">
          <div className="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div className="flex items-center justify-between text-sm">
              <span className="text-slate-500">Jumlah Kuantiti</span>
              <span className="font-medium text-slate-900">{totalQty} pcs</span>
            </div>
            <div className="flex items-center justify-between border-t border-slate-200 pt-3">
              <span className="text-base font-bold text-slate-900">Jumlah Bayaran</span>
              <span className="text-2xl font-extrabold text-brand-600">{formatCurrency(amount)}</span>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="mt-10 border-t border-slate-200 pt-6 text-center">
        <p className="text-sm font-bold text-slate-900">Terima kasih atas tempahan anda!</p>
        <p className="mt-1 text-xs text-slate-500">
          {brandName} • {brandPhone} • {brandEmail}
        </p>
        <p className="mt-3 text-[10px] text-slate-400">
          Invoice ini dijana secara elektronik dan sah tanpa tandatangan.
        </p>
      </div>

      {/* Children untuk butang bukan-print */}
      {children && (
        <div className="invoice-no-print mt-6 flex flex-wrap items-center gap-3">
          {children}
        </div>
      )}
    </div>
  );
}