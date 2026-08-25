import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link } from '@inertiajs/react';
import { Package, Eye, Receipt, RotateCcw, MessageCircle } from 'lucide-react';
import { formatDate } from '@/lib/utils';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';

interface Order {
  id: number;
  order_no: string;
  status: string;
  total: number;
  pricing_status: string;
  created_at: string;
  invoice: { id: number } | null;
}

interface MemberOrdersProps {
  orders: {
    data: Order[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function MemberOrdersIndex({ orders }: MemberOrdersProps) {
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
      case 'paid': return 'bg-blue-100 text-blue-700 border-blue-200';
      case 'partial': return 'bg-violet-100 text-violet-700 border-violet-200';
      case 'processing': return 'bg-purple-100 text-purple-700 border-purple-200';
      case 'shipped': return 'bg-sky-100 text-sky-700 border-sky-200';
      case 'completed': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
      case 'cancelled': return 'bg-rose-100 text-rose-700 border-rose-200';
      default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const statusLabels: Record<string, string> = {
    pending: 'Menunggu semakan',
    paid: 'Bayaran diterima',
    partial: 'Bayaran separa',
    processing: 'Sedang diproses',
    shipped: 'Sedang dihantar',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  const pricingLabels: Record<string, string> = {
    auto_priced: 'Harga tersedia',
    pending_admin: 'Menunggu harga',
    awaiting_customer_approval: 'Luluskan harga',
    approved: 'Harga diluluskan',
  };

  return (
    <MemberLayout>
      <Head title="Order Saya" />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8">
        <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold text-slate-900">Order Saya</h1>
            <p className="mt-1 text-sm text-slate-500">Semua tempahan yang anda telah buat.</p>
          </div>
          <div className="flex flex-wrap justify-end gap-2">
            <Link
              href={route('member.orders.create')}
              className="frontend-btn-primary inline-flex whitespace-nowrap px-4 py-2"
            >
              Tempah Sekarang
            </Link>
            <a
              href={whatsappWebUrl('601169409606')}
              target={WHATSAPP_TARGET}
              className="frontend-btn-secondary inline-flex whitespace-nowrap border-emerald-600 bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700"
            >
              <MessageCircle className="h-4 w-4" />
              Tempah melalui WhatsApp
            </a>
          </div>
        </div>

        <div className="frontend-table-card">
          <div className="frontend-table-wrap">
            <table className="frontend-table">
              <thead>
                <tr>
                  <th>No. Order</th>
                  <th>Jumlah</th>
                  <th>Status</th>
                  <th>Tarikh</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {orders.data.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-14 text-center">
                      <div className="frontend-table-empty">
                        <Package className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="text-lg font-semibold text-slate-900">Tiada Order</p>
                        <p className="text-sm text-slate-500">Anda belum membuat sebarang tempahan.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  orders.data.map((order) => (
                    <tr key={order.id}>
                      <td className="font-medium text-slate-900">{order.order_no}</td>
                      <td className="font-medium">
                        {order.pricing_status === 'pending_admin' ? <span className="text-amber-600">Menunggu</span> : formatCurrency(order.total)}
                        <span className="mt-1 block text-[11px] font-normal text-slate-400">{pricingLabels[order.pricing_status] ?? ''}</span>
                      </td>
                      <td>
                        <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${getStatusColor(order.status)}`}>
                          {statusLabels[order.status] ?? order.status}
                        </span>
                      </td>
                      <td className="text-slate-500">{formatDate(order.created_at)}</td>
                      <td>
                        <div className="flex items-center gap-2">
                          <Link
                            href={route('member.orders.show', order.id)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-brand-600 hover:bg-brand-50 transition"
                          >
                            <Eye className="h-4 w-4" />
                            Lihat
                          </Link>
                          {order.invoice && (
                            <Link
                              href={route('member.invoices.show', order.invoice.id)}
                              className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                            >
                              <Receipt className="h-4 w-4" />
                              Invoice
                            </Link>
                          )}
                          <Link
                            href={route('member.orders.repeat', order.id)}
                            method="post"
                            as="button"
                            type="button"
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                          >
                            <RotateCcw className="h-4 w-4" />
                            Ulang
                          </Link>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {orders.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-5 py-4">
              <div className="flex items-center gap-2">
                {orders.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                        link.active
                          ? 'bg-brand-600 text-white'
                          : 'text-slate-600 hover:bg-slate-50'
                      }`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span
                      key={i}
                      className="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  )
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </MemberLayout>
  );
}
