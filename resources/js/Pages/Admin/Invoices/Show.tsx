import AdminLayout from '@/Components/Layouts/AdminLayout';
import PrintInvoice, { type PrintInvoiceItem } from '@/Components/PrintInvoice';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, Printer, CheckCircle, XCircle, Clock, Eye, RotateCcw } from 'lucide-react';
import { type PageProps } from '@/types';
import { useState } from 'react';

interface InvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit_price: number;
  line_total: number;
}

interface OrderItem {
  id: number;
  design: { name: string } | null;
  size: { name: string } | null;
  custom_design_description: string | null;
  requested_size: string | null;
  cut_type: string | null;
  quantity: number;
  unit_price: number;
  line_total: number;
  subtotal: number;
}

interface Invoice {
  id: number;
  invoice_no: string;
  amount: number;
  issue_date: string;
  notes: string | null;
  created_at: string;
  payment_status: string;
  payment_type: string | null;
  payment_amount: string | null;
  payment_method: string | null;
  paid_at: string | null;
  payment_submitted_at: string | null;
  payment_note: string | null;
  customer_name: string | null;
  customer_phone: string | null;
  customer_address: string | null;
  order: {
    id: number;
    order_no: string;
    customer_name: string;
    customer_phone: string;
    customer_address: string;
    material: string;
    status: string;
    tracking_no: string | null;
    subtotal: number;
    total: number;
    items: OrderItem[];
  } | null;
  user: { id: number; name: string; email: string } | null;
  approver: { id: number; name: string } | null;
  items: InvoiceItem[];
}

interface AdminInvoiceShowProps extends PageProps {
  invoice: Invoice;
  receiptUrl?: string | null;
}

export default function InvoiceShow() {
  const { invoice, receiptUrl, app } = usePage<AdminInvoiceShowProps>().props;
  const [showRejectForm, setShowRejectForm] = useState(false);
  const [showApproveModal, setShowApproveModal] = useState(false);

  const { data: approveData, setData: setApproveData, post: postApprove, processing: approving, errors: approveErrors } = useForm({
    payment_note: '',
    payment_amount: invoice.payment_amount ?? String(Number(invoice.amount).toFixed(2)),
  });

  const { data: rejectData, setData: setRejectData, post: postReject, processing: rejecting } = useForm({
    payment_note: '',
  });

  const customerName = invoice.customer_name ?? invoice.order?.customer_name ?? invoice.user?.name ?? '-';
  const customerPhone = invoice.customer_phone ?? invoice.order?.customer_phone ?? '-';
  const customerAddress = invoice.customer_address ?? invoice.order?.customer_address ?? '-';

  const printItems: PrintInvoiceItem[] = invoice.items.length > 0
    ? invoice.items.map((i) => ({
        id: i.id,
        description: i.description,
        quantity: Number(i.quantity),
        unit_price: Number(i.unit_price),
        line_total: Number(i.line_total),
      }))
    : (invoice.order?.items ?? []).map((i, idx) => ({
        id: i.id ?? idx,
        description: [
          i.design?.name,
          i.custom_design_description,
          i.size?.name,
          i.requested_size ? `Saiz: ${i.requested_size}` : null,
          i.cut_type === 'die-cut' ? 'Potong Ikut Bentuk' : 'Potong Standard',
        ].filter(Boolean).join(' • ') || 'Sticker',
        quantity: Number(i.quantity),
        unit_price: Number(i.unit_price),
        line_total: Number(i.line_total ?? i.subtotal),
      }));

  const statusConfig: Record<string, { label: string; icon: typeof CheckCircle; color: string }> = {
    unpaid: { label: 'Belum Bayar', icon: Clock, color: 'text-amber-600 bg-amber-50 border-amber-200' },
    submitted: { label: 'Menunggu Pengesahan', icon: Clock, color: 'text-blue-600 bg-blue-50 border-blue-200' },
    paid: { label: 'Dibayar', icon: CheckCircle, color: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
    rejected: { label: 'Ditolak', icon: XCircle, color: 'text-rose-600 bg-rose-50 border-rose-200' },
  };
  const status = statusConfig[invoice.payment_status] ?? statusConfig.unpaid;
  const StatusIcon = status.icon;

  const handleApprove = (e: React.FormEvent) => {
    e.preventDefault();
    postApprove(route('admin.invoices.approve', invoice.id), {
      preserveScroll: true,
      onSuccess: () => setShowApproveModal(false),
    });
  };

  const handleReject = (e: React.FormEvent) => {
    e.preventDefault();
    postReject(route('admin.invoices.reject', invoice.id), {
      preserveScroll: true,
      onSuccess: () => setShowRejectForm(false),
    });
  };

  return (
    <AdminLayout>
      <Head title={`Invoice ${invoice.invoice_no}`} />
      <div className="space-y-6">
        {/* Back Link */}
        <div className="invoice-no-print">
          <Link
            href={invoice.order ? route('admin.orders.show', invoice.order.id) : route('admin.orders.index')}
            className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
          >
            <ArrowLeft className="h-4 w-4" />
            {invoice.order ? 'Kembali ke Order' : 'Kembali ke Senarai Order'}
          </Link>
        </div>

        {/* Payment Status \& Approval Panel */}
        <div className="invoice-no-print admin-flat-card p-6">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-3">
              <span className={`inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider ${status.color}`}>
                <StatusIcon className="h-4 w-4" />
                {status.label}
              </span>
              {invoice.payment_type && (
                <span className="text-xs font-medium text-slate-500">
                  Jenis: {invoice.payment_type === 'deposit' ? 'Deposit' : 'Bayaran Penuh'}
                  {invoice.payment_amount ? ` · Jumlah: RM ${Number(invoice.payment_amount).toFixed(2)}` : ''}
                </span>
              )}
              {invoice.payment_method && (
                <span className="text-xs text-slate-500">Kaedah: {invoice.payment_method}</span>
              )}
            </div>
            {invoice.approver && (
              <p className="text-xs text-slate-500">
                Disahkan oleh: <span className="font-semibold text-slate-700">{invoice.approver.name}</span>
              </p>
            )}
          </div>

          {invoice.payment_note && (
            <div className="mt-4 rounded-xl bg-slate-50 p-3">
              <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Nota Pembayaran</p>
              <p className="mt-1 text-sm text-slate-600">{invoice.payment_note}</p>
            </div>
          )}

          {/* Resit preview */}
          {receiptUrl && (
            <div className="mt-4">
              <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400">Resit Bayaran</p>
              <div className="mt-2 inline-block">
                <a href={receiptUrl} target="_blank" rel="noreferrer" className="block">
                  <img src={receiptUrl} alt="Resit" className="h-40 rounded-xl border border-slate-200 object-contain" />
                </a>
                <a href={receiptUrl} target="_blank" rel="noreferrer" className="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:underline">
                  <Eye className="h-3.5 w-3.5" />
                  Lihat resit penuh
                </a>
              </div>
            </div>
          )}

          {/* Approval Buttons */}
          {invoice.payment_status === 'submitted' && !showRejectForm && (
            <div className="mt-5 flex flex-wrap gap-3">
              <button
                type="button"
                onClick={() => setShowApproveModal(true)}
                className="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
              >
                <CheckCircle className="h-4 w-4" />
                Luluskan Pembayaran
              </button>
              <button
                type="button"
                onClick={() => setShowRejectForm(true)}
                className="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-5 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
              >
                <XCircle className="h-4 w-4" />
                Tolak Pembayaran
              </button>
            </div>
          )}

          {/* Reject Form */}
          {showRejectForm && (
            <form onSubmit={handleReject} className="mt-5 space-y-3 rounded-2xl border border-rose-200 bg-rose-50 p-5">
              <h4 className="text-sm font-bold text-rose-900">Tolak Pembayaran</h4>
              <div>
                <label htmlFor="reject-note" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Sebab Penolakan</label>
                <textarea
                  id="reject-note"
                  value={rejectData.payment_note}
                  onChange={(e) => setRejectData('payment_note', e.target.value)}
                  rows={3}
                  required
                  placeholder="cth: Resit tidak jelas, jumlah tidak sepadan..."
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>
              <div className="flex flex-wrap gap-3">
                <button
                  type="submit"
                  disabled={rejecting}
                  className="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50"
                >
                  {rejecting ? 'Menolak...' : 'Sahkan Tolak'}
                </button>
                <button
                  type="button"
                  onClick={() => setShowRejectForm(false)}
                  className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                  Batal
                </button>
              </div>
            </form>
          )}

          {/* Reset Payment */}
          {invoice.payment_status !== 'unpaid' && (
            <div className="mt-4">
              <Link
                href={route('admin.invoices.reset', invoice.id)}
                method="post"
                as="button"
                type="button"
                className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-brand-600"
              >
                <RotateCcw className="h-3.5 w-3.5" />
                Reset status pembayaran
              </Link>
            </div>
          )}
        </div>

        {/* Invoice Printable */}
        <PrintInvoice
          invoiceNo={invoice.invoice_no}
          issueDate={invoice.issue_date}
          amount={Number(invoice.amount)}
          customerName={customerName}
          customerPhone={customerPhone}
          customerAddress={customerAddress}
          items={printItems}
          notes={invoice.notes}
          paymentStatus={invoice.payment_status}
          paymentType={invoice.payment_type}
          paidAt={invoice.paid_at}
          logoUrl={app.logo_url}
        >
          <button
            type="button"
            onClick={() => window.print()}
            className="admin-btn-secondary text-sm"
          >
            <Printer className="h-4 w-4" />
            Cetak Invoice
          </button>
        </PrintInvoice>
      </div>

      {/* Approve Modal */}
      {showApproveModal && (
        <div className="invoice-no-print fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-4">
          <div className="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h3 className="text-lg font-bold text-slate-900">Luluskan Pembayaran</h3>
            <p className="mt-1 text-sm text-slate-500">Semak dan sahkan jumlah bayaran untuk <span className="font-semibold text-slate-700">{invoice.invoice_no}</span>.</p>

            <form onSubmit={handleApprove} className="mt-5 space-y-4">
              <div>
                <label htmlFor="approve-amount" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah Dibayar (RM)</label>
                <input
                  id="approve-amount"
                  type="number"
                  step="0.01"
                  min="0.01"
                  value={approveData.payment_amount}
                  onChange={(e) => setApproveData('payment_amount', e.target.value)}
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
                {approveErrors.payment_amount && <p className="mt-1 text-xs text-rose-600">{approveErrors.payment_amount}</p>}
                <p className="mt-1 text-xs text-slate-400">
                  Jumlah invoice: RM {Number(invoice.amount).toFixed(2)}
                  {invoice.payment_type === 'deposit' ? ' · Ini bayaran deposit (separuh).' : ''}
                </p>
              </div>

              <div>
                <label htmlFor="approve-note" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Nota (pilihan)</label>
                <textarea
                  id="approve-note"
                  value={approveData.payment_note}
                  onChange={(e) => setApproveData('payment_note', e.target.value)}
                  rows={2}
                  placeholder="cth: Jumlah diperbetulkan selepas semakan resit..."
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>

              <div className="flex flex-wrap gap-3 pt-1">
                <button
                  type="submit"
                  disabled={approving}
                  className="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                >
                  <CheckCircle className="h-4 w-4" />
                  {approving ? 'Meluluskan...' : 'Sahkan Lulus'}
                </button>
                <button
                  type="button"
                  onClick={() => setShowApproveModal(false)}
                  className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                  Batal
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}