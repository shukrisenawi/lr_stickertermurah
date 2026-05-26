import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  total: number;
  created_at: string;
  user: { name: string } | null;
}

interface ManualCreateProps {
  orders: Order[];
}

export default function ManualCreate({ orders }: ManualCreateProps) {
  const { data, setData, post, processing, errors } = useForm({
    order_id: '',
    invoice_no: '',
    issue_date: new Date().toISOString().split('T')[0],
    amount: '',
    notes: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.invoices.manual.store'));
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  return (
    <AdminLayout>
      <Head title="Invoice Manual" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Cipta Invoice Manual</h2>
            <p className="admin-page-copy">Pilih order dan isi butiran invoice secara manual.</p>
          </div>
          <Link href={route('admin.invoices.create')} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-6">
            <div className="admin-flat-card p-6 space-y-4">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                Butiran Invoice
              </h3>

              <div>
                  <label htmlFor="order_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Order
                  </label>
                  <select
                    id="order_id"
                    value={data.order_id}
                    onChange={(e) => setData('order_id', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                >
                  <option value="">Pilih order...</option>
                  {orders.map((order) => (
                    <option key={order.id} value={order.id}>
                      {order.order_no} — {order.customer_name} ({formatCurrency(order.total)})
                    </option>
                  ))}
                </select>
                {errors.order_id && (
                  <p className="mt-1 text-xs text-rose-600">{errors.order_id}</p>
                )}
              </div>

              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label htmlFor="invoice_no" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    No. Invoice
                  </label>
                  <input
                    id="invoice_no"
                    type="text"
                    value={data.invoice_no}
                    onChange={(e) => setData('invoice_no', e.target.value)}
                    placeholder="Auto-jana jika dibiarkan kosong"
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  />
                  {errors.invoice_no && (
                    <p className="mt-1 text-xs text-rose-600">{errors.invoice_no}</p>
                  )}
                </div>
                <div>
                  <label htmlFor="issue_date" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Tarikh
                  </label>
                  <input
                    id="issue_date"
                    type="date"
                    value={data.issue_date}
                    onChange={(e) => setData('issue_date', e.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    required
                  />
                  {errors.issue_date && (
                    <p className="mt-1 text-xs text-rose-600">{errors.issue_date}</p>
                  )}
                </div>
              </div>

              <div>
                <label htmlFor="amount" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Jumlah (RM)
                </label>
                <input
                  id="amount"
                  type="number"
                  step="0.01"
                  min="0"
                  value={data.amount}
                  onChange={(e) => setData('amount', e.target.value)}
                  placeholder="0.00"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.amount && (
                  <p className="mt-1 text-xs text-rose-600">{errors.amount}</p>
                )}
              </div>

              <div>
                <label htmlFor="notes" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Catatan
                </label>
                <textarea
                  id="notes"
                  value={data.notes}
                  onChange={(e) => setData('notes', e.target.value)}
                  rows={3}
                  placeholder="Catatan tambahan..."
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
                {errors.notes && (
                  <p className="mt-1 text-xs text-rose-600">{errors.notes}</p>
                )}
              </div>
            </div>
          </div>

          <div className="space-y-6">
            <div className="admin-flat-card p-6">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
                Tindakan
              </h3>
              <div className="space-y-3">
                <button
                  type="submit"
                  disabled={processing}
                  className="admin-btn-primary w-full text-sm"
                >
                  <Save className="h-4 w-4" />
                  {processing ? 'Menyimpan...' : 'Simpan Invoice'}
                </button>
                <Link
                  href={route('admin.invoices.create')}
                  className="admin-btn-secondary w-full text-sm text-center block"
                >
                  Batal
                </Link>
              </div>
            </div>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
