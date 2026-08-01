import MemberLayout from '@/Components/Layouts/MemberLayout';
import PrintInvoice, { type PrintInvoiceItem } from '@/Components/PrintInvoice';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, Eye, Printer, Upload, CreditCard, CheckCircle, XCircle, RotateCcw, MessageCircle, ImageOff } from 'lucide-react';
import { type PageProps } from '@/types';
import { useState, useEffect } from 'react';

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
  payment_status: string;
  payment_type: string | null;
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
    items: OrderItem[];
  } | null;
  items: InvoiceItem[];
}

interface MemberInvoiceShowProps extends PageProps {
  invoice: Invoice;
  paymentSettings: {
    bank_name: string;
    bank_account_no: string;
    bank_account_name: string;
    admin_phone: string;
    admin_email: string;
    deposit_amount: number;
    qr_image_url: string | null;
  } | null;
  receiptUrl: string | null;
  totalPaid: number;
  balanceDue: number;
}

export default function MemberInvoiceShow() {
  const { invoice, paymentSettings, receiptUrl, totalPaid, balanceDue, app } = usePage<MemberInvoiceShowProps>().props;
  const [cameWithPay] = useState(() => new URLSearchParams(window.location.search).get('pay') === '1');
  const [showPaymentInfo, setShowPaymentInfo] = useState(cameWithPay);
  const [showPaymentForm, setShowPaymentForm] = useState(cameWithPay && invoice.payment_status !== 'submitted');
  const [receiptPreview, setReceiptPreview] = useState<string | null>(null);
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [showErrorModal, setShowErrorModal] = useState(false);
  const [errorMessages, setErrorMessages] = useState<string[]>([]);

  const { data, setData, post, processing, reset, errors } = useForm({
    payment_receipt: null as File | null,
    payment_type: 'full' as 'deposit' | 'full' | 'custom',
    payment_amount: balanceDue.toFixed(2),
    payment_method: '',
  });

  const minDeposit = Number(paymentSettings?.deposit_amount ?? 20);
  const maxDeposit = Math.max(minDeposit, balanceDue - 0.01);
  const mustPayFull = balanceDue <= minDeposit;
  const isPartial = invoice.payment_status === 'partial';

  useEffect(() => {
    if (data.payment_type === 'full') {
      setData('payment_amount', balanceDue.toFixed(2));
    } else if (data.payment_type === 'deposit') {
      setData('payment_amount', minDeposit.toFixed(2));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data.payment_type, balanceDue, minDeposit, setData]);

  useEffect(() => {
    if (mustPayFull && !isPartial) {
      setData('payment_type', 'full');
      setData('payment_amount', balanceDue.toFixed(2));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mustPayFull, isPartial, balanceDue, setData]);

  const customerName = invoice.customer_name ?? invoice.order?.customer_name ?? '-';
  const customerPhone = invoice.customer_phone ?? invoice.order?.customer_phone ?? '-';
  const customerAddress = invoice.customer_address ?? invoice.order?.customer_address ?? '-';

  // Gunakan InvoiceItem jika ada, jika tidak fallback ke OrderItem
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

  const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(Number(amount));

  const whatsappLink = `https://wa.me/${(paymentSettings?.admin_phone ?? '601169409606').replace(/\D/g, '')}?text=${encodeURIComponent(`Invoice ${invoice.invoice_no}`)}`;

  const handleSubmitPayment = (e: React.FormEvent) => {
    e.preventDefault();

    const missing: string[] = [];
    if (!data.payment_method) missing.push('Sila pilih kaedah bayaran.');
    if (!data.payment_receipt) missing.push('Sila muat naik resit bayaran.');

    if (missing.length > 0) {
      setErrorMessages(missing);
      setShowErrorModal(true);
      return;
    }

    post(route('member.invoices.payment.upload', invoice.id), {
      preserveScroll: true,
      onSuccess: () => {
        setShowPaymentForm(false);
        setShowSuccessModal(true);
        setReceiptPreview(null);
        reset();
      },
      onError: (errs) => {
        const messages = Object.values(errs);
        if (messages.length > 0) {
          setErrorMessages(messages);
          setShowErrorModal(true);
        }
      },
    });
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setData('payment_receipt', file);
    if (file) {
      setReceiptPreview(URL.createObjectURL(file));
    }
  };

  return (
    <MemberLayout>
      <Head title={`Invoice ${invoice.invoice_no}`} />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8 space-y-6">
        {/* Back Link */}
        <div className="invoice-no-print">
          {!cameWithPay || showPaymentInfo ? (
            <Link
              href={route('member.invoices.index')}
              className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
            >
              <ArrowLeft className="h-4 w-4" />
              Kembali ke Invoice
            </Link>
          ) : (
            <button
              type="button"
              onClick={() => {
                setShowPaymentInfo(true);
                router.replace({ url: `${route('member.invoices.show', invoice.id)}?pay=1`, preserveState: true });
              }}
              className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
            >
              <ArrowLeft className="h-4 w-4" />
              Kembali ke Maklumat Bayaran
            </button>
          )}
        </div>

        {/* Payment Info Card (jika belum bayar / submitted / rejected) */}
        {showPaymentInfo && invoice.payment_status !== 'paid' && paymentSettings && (
          <div className="invoice-no-print frontend-flat-card p-6">
            <div className="flex items-center justify-between gap-3">
              <h3 className="flex items-center gap-2 text-base font-bold text-slate-900">
                <CreditCard className="h-5 w-5 text-brand-600" />
                Maklumat Bayaran
              </h3>
              <button
                type="button"
                onClick={() => {
                  setShowPaymentInfo(false);
                  setShowPaymentForm(false);
                  router.replace({ url: route('member.invoices.show', invoice.id), preserveState: true });
                }}
                className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
              >
                <Eye className="h-4 w-4" />
                Lihat Invoice
              </button>
            </div>

            <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div className="space-y-1">
                <p className="text-xs text-slate-500">Bank</p>
                <p className="text-sm font-bold text-slate-900">{paymentSettings.bank_name}</p>
                <p className="text-xs text-slate-500">Akaun: {paymentSettings.bank_account_no}</p>
                <p className="text-xs text-slate-500">{paymentSettings.bank_account_name}</p>
                {paymentSettings.admin_email && (
                  <p className="text-xs text-slate-500">Email: {paymentSettings.admin_email}</p>
                )}
              </div>
              {paymentSettings.qr_image_url && (
                <div className="flex justify-center sm:justify-end">
                  <img src={paymentSettings.qr_image_url} alt="QR Payment" className="h-36 w-36 rounded-xl border border-slate-100 object-contain" />
                </div>
              )}
            </div>

            <div className="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-700">
              <p className="font-semibold">Langkah Bayaran:</p>
              <ol className="mt-1 list-decimal space-y-0.5 pl-4">
                <li>Bayar via bank transfer / QR code di atas</li>
                <li>Screenshot / download resit bayaran</li>
                <li>Klik butang "Hantar Resit Bayaran" di bawah</li>
                <li>Menunggu admin sahkan pembayaran anda</li>
              </ol>
            </div>

            {!showPaymentForm ? (
              invoice.payment_status === 'submitted' ? (
                <div className="mt-5 flex flex-wrap items-center gap-3 rounded-xl bg-blue-50 p-4 text-sm text-blue-700">
                  <span className="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                    <CheckCircle className="h-4 w-4" />
                  </span>
                  <p>
                    Resit bayaran anda telah dihantar dan sedang menunggu pengesahan admin.
                    Klik <strong>Batalkan &amp; Hantar Semula</strong> untuk menggantikan resit.
                  </p>
                  <Link
                    href={route('member.invoices.payment.cancel', invoice.id)}
                    method="delete"
                    as="button"
                    type="button"
                    className="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100"
                  >
                    <RotateCcw className="h-4 w-4" />
                    Batalkan &amp; Hantar Semula
                  </Link>
                </div>
              ) : (
                <div className="mt-5 flex flex-wrap gap-3">
                  <button
                    type="button"
                    onClick={() => setShowPaymentForm(true)}
                    className="frontend-btn-primary text-sm"
                  >
                    <Upload className="h-4 w-4" />
                    Hantar Resit Bayaran
                  </button>
                  <a
                    href={whatsappLink}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600"
                  >
                    <MessageCircle className="h-4 w-4" />
                    WhatsApp Admin
                  </a>
                </div>
              )
            ) : (
              <form onSubmit={handleSubmitPayment} noValidate className="mt-5 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <h4 className="text-sm font-bold text-slate-900">Hantar Resit Bayaran</h4>

                {totalPaid > 0 && (
                  <div className="rounded-xl bg-emerald-50 p-3 text-xs text-emerald-700">
                    <p>
                      Jumlah Keseluruhan: <strong>{formatCurrency(Number(invoice.amount))}</strong>
                      {' · '}Sudah dibayar: <strong>{formatCurrency(totalPaid)}</strong>
                      {' · '}Baki: <strong>{formatCurrency(balanceDue)}</strong>
                    </p>
                  </div>
                )}

                <div>
                  <label className="text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="payment-type">Jenis Bayaran</label>
                  <div className="mt-2 grid grid-cols-2 gap-3">
                    {isPartial ? (
                      <button
                        type="button"
                        onClick={() => setData('payment_type', 'custom')}
                        className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                          data.payment_type === 'custom'
                            ? 'border-brand-600 bg-brand-50'
                            : 'border-slate-200 bg-white hover:border-brand-200'
                        }`}
                      >
                        <p className="text-sm font-bold text-slate-900">Jumlah Lain</p>
                        <p className="text-xs text-slate-500">Bayar separa sebarang jumlah</p>
                      </button>
                    ) : (
                      <button
                        type="button"
                        disabled={mustPayFull}
                        onClick={() => setData('payment_type', 'deposit')}
                        className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                          mustPayFull
                            ? 'cursor-not-allowed border-slate-200 bg-slate-100 opacity-60'
                            : data.payment_type === 'deposit'
                              ? 'border-brand-600 bg-brand-50'
                              : 'border-slate-200 bg-white hover:border-brand-200'
                        }`}
                      >
                        <p className="text-sm font-bold text-slate-900">Deposit</p>
                        <p className="text-xs text-slate-500">Min RM {minDeposit.toFixed(2)}</p>
                      </button>
                    )}
                    <button
                      type="button"
                      onClick={() => setData('payment_type', 'full')}
                      className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                        data.payment_type === 'full'
                          ? 'border-brand-600 bg-brand-50'
                          : 'border-slate-200 bg-white hover:border-brand-200'
                      }`}
                    >
                      <p className="text-sm font-bold text-slate-900">Bayaran Penuh</p>
                      <p className="text-xs text-slate-500">{formatCurrency(balanceDue)}</p>
                    </button>
                  </div>
                </div>

                {(data.payment_type === 'deposit' || data.payment_type === 'custom') && (
                  <div>
                    <label htmlFor="payment-amount" className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                      {data.payment_type === 'deposit' ? 'Jumlah Deposit (RM)' : 'Jumlah Bayaran (RM)'}
                    </label>
                    <input
                      id="payment-amount"
                      type="number"
                      min={data.payment_type === 'deposit' ? minDeposit : 0.01}
                      max={data.payment_type === 'deposit' ? maxDeposit : balanceDue}
                      step="0.01"
                      value={data.payment_amount}
                      onChange={(e) => setData('payment_amount', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    <p className="mt-1 text-xs text-slate-400">
                      {data.payment_type === 'deposit'
                        ? `Minimum RM ${minDeposit.toFixed(2)} · Maksimum kurang daripada RM ${balanceDue.toFixed(2)}`
                        : `Maksimum RM ${balanceDue.toFixed(2)}`}
                    </p>
                    {errors.payment_amount && <p className="mt-1 text-xs text-rose-600">{errors.payment_amount}</p>}
                  </div>
                )}

                <div>
                  <label htmlFor="payment-method" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Kaedah Bayaran</label>
                  <select
                    id="payment-method"
                    required
                    value={data.payment_method}
                    onChange={(e) => setData('payment_method', e.target.value)}
                    className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  >
                    <option value="">Pilih kaedah bayaran</option>
                    <option value="bank in">Bank In</option>
                    <option value="transfer">Transfer</option>
                    <option value="qr">QR Code</option>
                  </select>
                  {errors.payment_method && <p className="mt-1 text-xs text-rose-600">{errors.payment_method}</p>}
                </div>

                <div>
                  <label htmlFor="payment-receipt" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Resit Bayaran</label>
                  <div className="mt-1 flex items-center gap-4">
                    {receiptPreview ? (
                      <img src={receiptPreview} alt="Resit preview" className="h-24 w-24 rounded-xl border border-slate-200 object-cover" />
                    ) : (
                      <div className="flex h-24 w-24 items-center justify-center rounded-xl bg-slate-100">
                        <ImageOff className="h-8 w-8 text-slate-300" />
                      </div>
                    )}
                    <div className="flex-1">
                      <input
                        id="payment-receipt"
                        type="file"
                        required
                        accept="image/*"
                        onChange={handleFileChange}
                        className="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-700"
                      />
                      <p className="mt-1 text-xs text-slate-400">JPG, PNG, WebP. Maks 5MB.</p>
                      {errors.payment_receipt && <p className="mt-1 text-xs text-rose-600">{errors.payment_receipt}</p>}
                    </div>
                  </div>
                </div>

                <div className="flex flex-wrap gap-3">
                  <button
                    type="submit"
                    disabled={processing}
                    className="frontend-btn-primary text-sm"
                  >
                    {processing ? 'Menghantar...' : 'Hantar Resit'}
                  </button>
                  {invoice.payment_status === 'submitted' && (
                    <Link
                      href={route('member.invoices.payment.cancel', invoice.id)}
                      method="delete"
                      as="button"
                      type="button"
                      className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                      <RotateCcw className="h-4 w-4" />
                      Batalkan & Hantar Semula
                    </Link>
                  )}
                  <button
                    type="button"
                    onClick={() => { setShowPaymentForm(false); reset(); setReceiptPreview(receiptUrl); }}
                    className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                  >
                    Tutup
                  </button>
                </div>
              </form>
            )}
          </div>
        )}

        {/* Invoice Printable */}
        {!showPaymentInfo && (
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
              className="frontend-btn-primary text-sm"
            >
              <Printer className="h-4 w-4" />
              Cetak Invoice
            </button>
            {invoice.payment_status !== 'paid' && (
              <button
                type="button"
                onClick={() => { setShowPaymentInfo(true); setShowPaymentForm(true); }}
                className="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700"
              >
                <CreditCard className="h-4 w-4" />
                Bayar
              </button>
            )}
            {invoice.order && (
              <Link
                href={route('member.orders.repeat', invoice.order.id)}
                method="post"
                as="button"
                type="button"
                className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              >
                <RotateCcw className="h-4 w-4" />
                Ulang Tempahan
              </Link>
            )}
          </PrintInvoice>
        )}
      </div>

      {/* Success Modal */}
      {showSuccessModal && (
        <div className="invoice-no-print fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-4">
          <div className="w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-2xl">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
              <CheckCircle className="h-9 w-9 text-emerald-600" />
            </div>
            <h3 className="mt-5 text-xl font-bold text-slate-900">Resit Dihantar!</h3>
            <p className="mt-2 text-sm leading-relaxed text-slate-500">
              Resit bayaran untuk <span className="font-semibold text-slate-700">{invoice.invoice_no}</span> telah berjaya dihantar
              kepada admin. Sila tunggu pengesahan pembayaran anda.
            </p>
            <button
              type="button"
              onClick={() => setShowSuccessModal(false)}
              className="frontend-btn-primary mt-6 w-full justify-center text-sm"
            >
              OK, Faham
            </button>
          </div>
        </div>
      )}
      {/* Error Modal */}
      {showErrorModal && (
        <div className="invoice-no-print fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-4">
          <div className="w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-2xl">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100">
              <XCircle className="h-9 w-9 text-rose-600" />
            </div>
            <h3 className="mt-5 text-xl font-bold text-slate-900">Resit Tidak Dihantar</h3>
            <div className="mt-3 space-y-2 text-left">
              {errorMessages.map((msg) => (
                <p key={msg} className="rounded-xl bg-rose-50 px-4 py-2.5 text-sm font-medium text-rose-700">
                  {msg}
                </p>
              ))}
            </div>
            <button
              type="button"
              onClick={() => setShowErrorModal(false)}
              className="frontend-btn-primary mt-6 w-full justify-center text-sm"
            >
              Cuba Semula
            </button>
          </div>
        </div>
      )}
    </MemberLayout>
  );
}