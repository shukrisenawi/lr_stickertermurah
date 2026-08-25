import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, CheckCircle, CreditCard, Info, MessageCircle, ShoppingCart } from 'lucide-react';
import { type PageProps } from '@/types';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';

interface OrderThankYouProps extends PageProps {
  order: {
    id: number;
    order_no: string;
    total: number;
    subtotal: number;
    deposit_amount: number | null;
    balance_due: number | null;
    payment_status: string;
    status: string;
    pricing_status: string;
    invoice: { id: number; invoice_no: string } | null;
    items: Array<{
      quantity: number;
      line_total: number;
      cut_type: string;
      customer_design_path: string | null;
      customer_design_paths: string[] | null;
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
  const item = order.items[0] ?? null;

  const isPending = order.pricing_status === 'pending_admin';
  const deposit = Number(order.deposit_amount ?? (paymentSettings?.deposit_amount ?? 20));
  const total = Number(order.total ?? 0);
  const balanceDue = Number(order.balance_due ?? 0);
  const designCount = item?.customer_design_paths?.length ?? (item?.customer_design_path ? 1 : 0);
  const configuredWhatsappPhone = (paymentSettings?.admin_phone ?? '601169409606').replace(/\D/g, '');
  const whatsappPhone = configuredWhatsappPhone
    ? (configuredWhatsappPhone.startsWith('0') ? `60${configuredWhatsappPhone.slice(1)}` : configuredWhatsappPhone)
    : '601169409606';
  const whatsappLink = whatsappWebUrl(whatsappPhone, `Saya ingin bertanya tentang tempahan ${order.order_no}.`);

  const orderDetails = [
    { label: 'Design', value: item?.design?.name ?? item?.custom_design_description ?? 'Custom' },
    { label: 'Saiz', value: item?.size?.name ?? item?.requested_size ?? '-' },
    { label: 'Kuantiti', value: item?.quantity ? `${item.quantity} pcs` : '-' },
    { label: 'Jenis potong', value: item?.cut_type === 'die-cut' ? 'Ikut bentuk' : 'Standard' },
  ];

  return (
    <FrontendLayout hideNavbar>
      <Head title="Tempahan Diterima" />
      <PublicHeader active="design" />
      <div className="frontend-shell min-h-screen bg-slate-50/70 pb-20">
        <div className="mx-auto max-w-[1120px] px-4 py-8 sm:py-10 lg:px-8">
          <section className="relative overflow-hidden rounded-[2rem] bg-brand-900 px-5 py-8 text-white shadow-xl shadow-brand-900/10 sm:px-8 sm:py-10">
            <div className="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border-[28px] border-white/5" />
            <div className="pointer-events-none absolute -bottom-24 right-[20%] h-44 w-44 rounded-full border-[20px] border-brand-700/60" />
            <div className="relative max-w-2xl">
              <div className="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-400/15 px-3 py-1.5 text-xs font-bold text-emerald-100">
                <CheckCircle className="h-4 w-4 text-emerald-300" />
                Tempahan berjaya direkodkan
              </div>
              <h1 className="mt-5 max-w-xl text-3xl font-extrabold tracking-tight sm:text-4xl">Terima kasih, tempahan anda diterima.</h1>
              <p className="mt-3 max-w-xl text-sm leading-relaxed text-brand-100 sm:text-base">Admin akan semak maklumat tempahan anda dan menghubungi anda jika ada perkara yang perlu disahkan.</p>

              <div className="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div className="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                  <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-200">No. Order</p>
                  <p className="mt-1 text-lg font-extrabold tracking-tight text-white">{order.order_no}</p>
                </div>
                <div className="rounded-2xl border border-white/10 bg-white/10 px-4 py-3">
                  <p className="text-[11px] font-bold uppercase tracking-[0.16em] text-brand-200">Status</p>
                  <p className="mt-1 text-lg font-extrabold tracking-tight text-white">{isPending ? 'Menunggu semakan' : 'Menunggu proses'}</p>
                </div>
              </div>
            </div>
          </section>

          <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.8fr)]">
            <div className="space-y-6">
              <section className="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                    <ShoppingCart className="h-5 w-5" />
                  </div>
                  <div>
                    <h2 className="text-lg font-bold text-slate-900">Butiran tempahan</h2>
                    <p className="text-sm text-slate-500">Semak semula maklumat yang telah dihantar.</p>
                  </div>
                </div>

                <dl className="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                  {orderDetails.map((detail) => (
                    <div key={detail.label} className="rounded-2xl bg-slate-50 px-4 py-3">
                      <dt className="text-xs font-semibold text-slate-500">{detail.label}</dt>
                      <dd className="mt-1 truncate text-sm font-bold text-slate-900">{detail.value}</dd>
                    </div>
                  ))}
                  {designCount > 0 && (
                    <div className="rounded-2xl bg-emerald-50 px-4 py-3">
                      <dt className="text-xs font-semibold text-emerald-700">Design dihantar</dt>
                      <dd className="mt-1 text-sm font-bold text-emerald-800">{designCount} fail</dd>
                    </div>
                  )}
                </dl>
              </section>

              <section className="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div className="flex items-center justify-between gap-4">
                  <div>
                    <h2 className="text-lg font-bold text-slate-900">Ringkasan bayaran</h2>
                    <p className="mt-1 text-sm text-slate-500">{isPending ? 'Jumlah akan disahkan oleh admin.' : 'Jumlah berdasarkan maklumat semasa.'}</p>
                  </div>
                  {!isPending && <p className="text-xl font-extrabold text-brand-600">RM {total.toFixed(2)}</p>}
                </div>

                {isPending ? (
                  <div className="mt-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                    <Info className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                    <div>
                      <p className="text-sm font-bold text-amber-800">Harga sedang disemak</p>
                      <p className="mt-1 text-xs leading-relaxed text-amber-700">Admin akan semak saiz custom dan maklumkan harga sebelum invoice dikeluarkan.</p>
                    </div>
                  </div>
                ) : (
                  <div className="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                    {!order.invoice && (
                      <div className="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p className="font-bold text-emerald-800">Harga telah dikira</p>
                        <p className="mt-1 text-xs leading-relaxed text-emerald-700">Admin akan sediakan invoice untuk anda. Semak menu invoice selepas itu.</p>
                      </div>
                    )}
                    <div className="flex items-center justify-between">
                      <span className="text-slate-500">Jumlah</span>
                      <span className="font-bold text-slate-900">RM {total.toFixed(2)}</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-slate-500">Deposit sekarang</span>
                      <span className="font-bold text-brand-600">RM {deposit.toFixed(2)}</span>
                    </div>
                    <div className="flex items-center justify-between border-t border-slate-100 pt-3">
                      <span className="font-semibold text-slate-700">Baki selepas confirm</span>
                      <span className="font-bold text-slate-900">RM {balanceDue.toFixed(2)}</span>
                    </div>
                  </div>
                )}
              </section>
            </div>

            <aside className="space-y-6">
              {!isPending && order.invoice && paymentSettings ? (
                <section className="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                      <CreditCard className="h-5 w-5" />
                    </div>
                    <div>
                      <h2 className="text-lg font-bold text-slate-900">Maklumat bayaran</h2>
                      <p className="text-sm text-slate-500">Invoice {order.invoice.invoice_no}</p>
                    </div>
                  </div>

                  <div className="mt-5 rounded-2xl bg-slate-50 p-4">
                    <p className="text-xs font-semibold text-slate-500">Bank</p>
                    <p className="mt-1 text-sm font-bold text-slate-900">{paymentSettings.bank_name}</p>
                    <p className="mt-3 text-xs text-slate-500">No. akaun</p>
                    <p className="mt-1 text-sm font-bold text-slate-900">{paymentSettings.bank_account_no}</p>
                    <p className="mt-1 text-xs text-slate-500">{paymentSettings.bank_account_name}</p>
                  </div>

                  {paymentSettings.qr_image_url && (
                    <div className="mt-5 flex justify-center rounded-2xl border border-slate-100 bg-white p-4">
                      <img src={paymentSettings.qr_image_url} alt="QR bayaran" className="h-40 w-40 object-contain" />
                    </div>
                  )}

                  <a
                    href={whatsappLink}
                    target={WHATSAPP_TARGET}
                    className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600 active:scale-[0.98]"
                  >
                    <MessageCircle className="h-4 w-4" />
                    Hantar Resit via WhatsApp
                  </a>
                </section>
              ) : (
                <section className="rounded-[1.5rem] border border-brand-100 bg-brand-50 p-5 sm:p-6">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-600">
                      <MessageCircle className="h-5 w-5" />
                    </div>
                    <div>
                      <h2 className="text-lg font-bold text-brand-900">Perlukan bantuan?</h2>
                      <p className="text-sm text-brand-700">Tanya admin tentang tempahan anda.</p>
                    </div>
                  </div>
                  <p className="mt-4 text-sm leading-relaxed text-brand-800">Jika ada soalan tentang design, saiz atau harga, terus WhatsApp admin untuk bantuan.</p>
                  <a
                    href={whatsappLink}
                    target={WHATSAPP_TARGET}
                    className="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700 active:scale-[0.98]"
                  >
                    <MessageCircle className="h-4 w-4" />
                    WhatsApp Admin
                  </a>
                </section>
              )}

              <section className="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 className="text-lg font-bold text-slate-900">Apa seterusnya?</h2>
                <p className="mt-2 text-sm leading-relaxed text-slate-500">Simpan nombor order ini untuk rujukan apabila berhubung dengan admin.</p>
                <div className="mt-5 grid grid-cols-1 gap-3">
                  <Link href={route('orders.create')} className="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-600/20 transition hover:bg-brand-700 active:scale-[0.98]">
                    <ShoppingCart className="h-4 w-4" />
                    Tempah Lagi
                  </Link>
                  <Link href="/" className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-brand-200 hover:text-brand-700 active:scale-[0.98]">
                    Kembali ke Home
                    <ArrowRight className="h-4 w-4" />
                  </Link>
                </div>
              </section>
            </aside>
          </div>
        </div>
      </div>
    </FrontendLayout>
  );
}
