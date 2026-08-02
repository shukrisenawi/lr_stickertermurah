import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, usePage } from '@inertiajs/react';
import { CheckCircle, MessageCircle, CreditCard } from 'lucide-react';
import { type PageProps } from '@/types';

interface OrderThankYouProps extends PageProps {
  order: {
    id: number;
    order_no: string;
    total: number;
    subtotal: number;
    deposit_amount: number;
    balance_due: number;
    payment_status: string;
    status: string;
    pricing_status: string;
    invoice: { id: number; invoice_no: string } | null;
    items: Array<{
      quantity: number;
      line_total: number;
      cut_type: string;
      customer_design_path: string | null;
      design: { name: string } | null;
      size: { name: string } | null;
      custom_design_description: string | null;
      requested_size: string | null;
    }>;
  };
  paymentSettings: {
    bank_name: string;
    bank_account_no: string;
    bank_account_name: string;
    admin_phone: string;
    deposit_amount: number;
    qr_image_url: string | null;
  } | null;
}

export default function OrderThankYou() {
  const { order, paymentSettings } = usePage<OrderThankYouProps>().props;
  const item = order.items[0];

  const isPending = order.pricing_status === 'pending_admin';
  const deposit = order.deposit_amount ?? (paymentSettings?.deposit_amount ?? 20);

  const whatsappLink = `https://wa.me/${(paymentSettings?.admin_phone ?? '601169409606').replace(/\D/g, '')}?text=Order%20${encodeURIComponent(order.order_no)}`;

  return (
    <FrontendLayout>
      <Head title="Tempahan Berjaya" />
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-xl px-4 py-16 lg:px-8 text-center">
          <CheckCircle className="mx-auto h-16 w-16 text-emerald-500" />
          <h1 className="mt-6 text-3xl font-extrabold text-slate-900">Tempahan Diterima!</h1>
          <p className="mt-2 text-slate-500">Terima kasih. Kami akan proses tempahan anda segera.</p>

          <div className="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-left">
            <p className="text-sm text-slate-500">No. Order</p>
            <p className="text-2xl font-extrabold text-brand-600">{order.order_no}</p>

            <div className="mt-4 space-y-2 border-t border-slate-100 pt-4">
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Design</span>
                <span className="font-medium text-slate-900">{item?.design?.name ?? item?.custom_design_description ?? 'Custom'}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Saiz</span>
                <span className="font-medium text-slate-900">{item?.size?.name ?? item?.requested_size ?? '-'}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Kuantiti</span>
                <span className="font-medium text-slate-900">{item?.quantity ?? '-'} pcs</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Potong</span>
                <span className="font-medium text-slate-900">{item?.cut_type === 'die-cut' ? 'Ikut Bentuk' : 'Standard'}</span>
              </div>
              {item?.customer_design_path && (
                <div className="flex justify-between text-sm">
                  <span className="text-slate-500">Design Hantar</span>
                  <span className="font-medium text-emerald-600">Ya</span>
                </div>
              )}
            </div>

            <div className="mt-4 border-t border-slate-100 pt-4">
              {isPending ? (
                <div className="rounded-xl bg-amber-50 p-3 text-center">
                  <p className="text-sm font-bold text-amber-700">Harga Pending</p>
                  <p className="text-xs text-amber-600">Admin akan semak saiz & kuantiti custom anda dan maklumkan harga.</p>
                </div>
              ) : (
                <>
                  {!order.invoice && (
                    <div className="mb-3 rounded-xl bg-emerald-50 p-3 text-center">
                      <p className="text-sm font-bold text-emerald-700">Harga telah dikira</p>
                      <p className="text-xs text-emerald-600">Admin akan mencipta invoice untuk anda. Sila semak menu Invoice Saya selepas itu.</p>
                    </div>
                  )}
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Jumlah</span>
                    <span className="font-bold text-slate-900">RM {Number(order.total).toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm mt-1">
                    <span className="text-slate-500">Deposit (sekarang)</span>
                    <span className="font-bold text-brand-600">RM {Number(deposit).toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm mt-1">
                    <span className="text-slate-500">Baki (selepas confirm)</span>
                    <span className="font-medium text-slate-700">RM {Number(order.balance_due).toFixed(2)}</span>
                  </div>
                </>
              )}
            </div>
          </div>

          {/* Payment Info */}
          {!isPending && order.invoice && paymentSettings && (
            <div className="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-left">
              <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
                <CreditCard className="h-5 w-5 text-brand-600" />
                Maklumat Bayaran
              </h3>

              <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <p className="text-xs text-slate-500">Bank</p>
                  <p className="text-sm font-bold text-slate-900">{paymentSettings.bank_name}</p>
                  <p className="text-xs text-slate-500 mt-1">Akaun: {paymentSettings.bank_account_no}</p>
                  <p className="text-xs text-slate-500">{paymentSettings.bank_account_name}</p>
                </div>
                {paymentSettings.qr_image_url && (
                  <div className="flex justify-center">
                    <img src={paymentSettings.qr_image_url} alt="QR Payment" className="h-32 w-32 rounded-xl object-contain border border-slate-100" />
                  </div>
                )}
              </div>

              <div className="mt-4 flex flex-wrap gap-3">
                <a
                  href={whatsappLink}
                  target="_blank"
                  rel="noreferrer"
                  className="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-600 shadow-lg shadow-emerald-500/20"
                >
                  <MessageCircle className="h-4 w-4" />
                  Hantar Resit via WhatsApp
                </a>
              </div>
            </div>
          )}
        </div>
      </div>
    </FrontendLayout>
  );
}
