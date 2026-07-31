import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
  ArrowLeft,
  Package,
  Receipt,
  User,
  Phone,
  MapPin,
  FileText,
} from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface OrderItem {
  id: number;
  design: { name: string } | null;
  size: { name: string } | null;
  quantity: number;
  unit_price: number;
  subtotal: number;
}

interface Order {
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
  custom_request: string | null;
  created_at: string;
  items: OrderItem[];
}

interface InvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit_price: number;
  line_total: number;
}

interface UserInfo {
  id: number;
  name: string;
  email: string;
}

interface Invoice {
  id: number;
  invoice_no: string;
  amount: number;
  issue_date: string;
  notes: string | null;
  created_at: string;
  order: Order | null;
  user: UserInfo | null;
  customer_name: string | null;
  customer_phone: string | null;
  customer_address: string | null;
  items: InvoiceItem[];
}

interface InvoiceShowProps {
  invoice: Invoice;
}

export default function InvoiceShow({ invoice }: InvoiceShowProps) {
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  const customerName = invoice.customer_name ?? invoice.order?.customer_name ?? invoice.user?.name ?? '-';
  const customerPhone = invoice.customer_phone ?? invoice.order?.customer_phone ?? '-';
  const customerAddress = invoice.customer_address ?? invoice.order?.customer_address ?? '-';

  return (
    <AdminLayout>
      <Head title={`Invoice ${invoice.invoice_no}`} />
      <div className="space-y-6">
        {/* Back Link */}
        <Link
          href={invoice.order ? route('admin.orders.show', invoice.order.id) : route('admin.orders.index')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          {invoice.order ? 'Kembali ke Order' : 'Kembali ke Senarai Order'}
        </Link>

        {/* Invoice Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <div className="admin-icon-badge">
              <Receipt className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-xl font-bold text-slate-900">{invoice.invoice_no}</h1>
              <p className="text-sm text-slate-500">{formatDate(invoice.issue_date)}</p>
            </div>
          </div>
          <span className="inline-flex rounded-full border border-emerald-200 bg-emerald-100 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-700">
            Aktif
          </span>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          {/* Invoice Summary */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
              Maklumat Invoice
            </h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">No. Invoice</span>
                <span className="text-sm font-medium text-slate-900">{invoice.invoice_no}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Tarikh</span>
                <span className="text-sm font-medium text-slate-900">{formatDate(invoice.issue_date)}</span>
              </div>
              {invoice.order && (
                <div className="flex items-center justify-between">
                  <span className="text-sm text-slate-500">No. Order</span>
                  <Link
                    href={route('admin.orders.show', invoice.order.id)}
                    className="text-sm font-medium text-brand-600 hover:underline"
                  >
                    {invoice.order.order_no}
                  </Link>
                </div>
              )}
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm font-semibold text-slate-900">Jumlah</span>
                <span className="text-lg font-bold text-brand-600">
                  {formatCurrency(invoice.amount)}
                </span>
              </div>
            </div>
          </div>

          {/* Customer Info */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
              Maklumat Pelanggan
            </h3>
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <User className="h-4 w-4 text-slate-400" />
                <span className="text-sm text-slate-900">{customerName}</span>
              </div>
              <div className="flex items-center gap-3">
                <Phone className="h-4 w-4 text-slate-400" />
                <span className="text-sm text-slate-900">{customerPhone}</span>
              </div>
              <div className="flex items-start gap-3">
                <MapPin className="h-4 w-4 text-slate-400 mt-0.5" />
                <span className="text-sm text-slate-900">{customerAddress}</span>
              </div>
            </div>
          </div>

          {/* Notes */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
              Catatan
            </h3>
            {invoice.notes ? (
              <p className="text-sm text-slate-700">{invoice.notes}</p>
            ) : (
              <p className="text-sm text-slate-400 italic">Tiada catatan</p>
            )}
          </div>
        </div>

        {/* Invoice Items */}
        <div className="admin-flat-card">
          <div className="admin-card-header">
            <div className="flex items-center gap-3">
              <div className="admin-icon-badge">
                <Package className="h-5 w-5" />
              </div>
              <h3 className="text-lg font-bold text-slate-900">Item Invoice</h3>
            </div>
          </div>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Penerangan</th>
                  <th>Kuantiti</th>
                  <th>Harga Unit</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {invoice.items.length === 0 && invoice.order?.items && invoice.order.items.length > 0 ? (
                  invoice.order.items.map((item) => (
                    <tr key={item.id}>
                      <td>{item.design?.name || '-'}</td>
                      <td>{item.size?.name || '-'}</td>
                      <td>{item.quantity}</td>
                      <td>{formatCurrency(item.unit_price)}</td>
                      <td className="font-medium">{formatCurrency(item.subtotal)}</td>
                    </tr>
                  ))
                ) : invoice.items.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="py-12 text-center text-sm text-slate-500">
                      Tiada item
                    </td>
                  </tr>
                ) : (
                  invoice.items.map((item) => (
                    <tr key={item.id}>
                      <td>{item.description}</td>
                      <td>{item.quantity}</td>
                      <td>{formatCurrency(item.unit_price)}</td>
                      <td className="font-medium">{formatCurrency(item.line_total)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* Print button placeholder */}
        <div className="flex flex-wrap items-center gap-3">
          <button
            type="button"
            onClick={() => window.print()}
            className="admin-btn-secondary text-sm"
          >
            <FileText className="h-4 w-4" />
            Cetak Invoice
          </button>
        </div>
      </div>
    </AdminLayout>
  );
}
