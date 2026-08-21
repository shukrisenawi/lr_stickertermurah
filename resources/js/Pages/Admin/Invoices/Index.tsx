import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search, Receipt, Eye, Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { formatDate } from '@/lib/utils';

interface Invoice {
  id: number;
  invoice_no: string;
  amount: number;
  issue_date: string;
  payment_status: string;
  payment_type: string | null;
  payment_amount: string | null;
  total_paid: string | null;
  customer_name: string | null;
  customer_phone: string | null;
  tracking_no: string | null;
  order: { order_no: string; tracking_no: string | null } | null;
  user: { name: string; email: string } | null;
  approver: { name: string } | null;
}

function TrackingNumberForm({ invoiceId, initialTrackingNo }: { invoiceId: number; initialTrackingNo: string }) {
  const { data, setData, put, processing } = useForm({ tracking_no: initialTrackingNo });

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    put(route('admin.invoices.tracking.update', invoiceId), { preserveScroll: true });
  };

  return (
    <form onSubmit={handleSubmit} className="flex min-w-[210px] items-center gap-2">
      <input
        type="text"
        value={data.tracking_no}
        onChange={(event) => setData('tracking_no', event.target.value)}
        placeholder="No. tracking J&T"
        aria-label="No. tracking J&T"
        className="w-36 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
      />
      <button type="submit" disabled={processing} className="rounded-lg bg-brand-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-700 disabled:opacity-50">
        {processing ? '...' : 'Simpan'}
      </button>
    </form>
  );
}

interface InvoicesIndexProps {
  invoices: {
    data: Invoice[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  counts: {
    all: number;
    unpaid: number;
    partial: number;
    paid: number;
  };
  filters: {
    search: string;
    payment_status: string;
  };
}

export default function InvoicesIndex({ invoices, counts, filters }: InvoicesIndexProps) {
  const { data, setData, get, delete: destroy } = useForm({
    q: filters.search,
    payment_status: filters.payment_status,
  });
  const [searching, setSearching] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearching(true);
    get(route('admin.invoices.index'), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  const tabs: Array<{ key: string; label: string; count: number }> = [
    { key: '', label: 'Semua', count: counts.all },
    { key: 'unpaid', label: 'Belum Bayar', count: counts.unpaid },
    { key: 'partial', label: 'Bayaran Separa', count: counts.partial },
    { key: 'paid', label: 'Telah Bayar', count: counts.paid },
  ];

  const changeTab = (key: string) => {
    setData('payment_status', key);
    get(route('admin.invoices.index', { payment_status: key || undefined, q: data.q || undefined }), {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleDelete = (id: number, invoiceNo: string) => {
    if (confirm(`Adakah anda pasti mahu memadam invoice ${invoiceNo}?`)) {
      destroy(route('admin.invoices.destroy', id));
    }
  };

  const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(amount);

  const statusConfig: Record<string, { label: string; class: string }> = {
    unpaid: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-700 border-amber-200' },
    submitted: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-700 border-amber-200' },
    rejected: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-700 border-amber-200' },
    partial: { label: 'Bayaran Separa', class: 'bg-violet-100 text-violet-700 border-violet-200' },
    paid: { label: 'Telah Bayar', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
  };

  return (
    <AdminLayout>
      <Head title="Senarai Invoice" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Invoice</h2>
            <p className="admin-page-copy">Semua invoice termasuk yang dicipta manual.</p>
          </div>
          <Link href={route('admin.invoices.manual.create')} className="admin-btn-primary text-sm">
            <Plus className="h-4 w-4" />
            Tambah Invoice
          </Link>
        </div>

        <div className="admin-toolbar-card">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div className="flex flex-wrap items-center gap-1 rounded-xl bg-slate-100 p-1">
              {tabs.map((tab) => (
                <button
                  key={tab.key}
                  type="button"
                  onClick={() => changeTab(tab.key)}
                  className={`inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition ${
                    data.payment_status === tab.key
                      ? 'bg-white text-brand-700 shadow-sm'
                      : 'text-slate-500 hover:text-slate-700'
                  }`}
                >
                  {tab.label}
                  <span className={`rounded-full px-2 py-0.5 text-[11px] font-bold ${data.payment_status === tab.key ? 'bg-brand-100 text-brand-700' : 'bg-slate-200 text-slate-500'}`}>
                    {tab.count}
                  </span>
                </button>
              ))}
            </div>
          </div>

          <form onSubmit={handleSearch} className="mt-3 flex flex-1 items-center gap-3">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={data.q}
                onChange={(e) => setData('q', e.target.value)}
                placeholder="Cari no invoice, nama, telefon..."
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <button type="submit" disabled={searching} className="admin-btn-primary text-sm">
              {searching ? 'Mencari...' : 'Cari'}
            </button>
          </form>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>No. Invoice</th>
                  <th>Pelanggan</th>
                  <th>Jumlah</th>
                  <th>Bayaran</th>
                   <th>Status</th>
                   <th>Tarikh</th>
                   {data.payment_status === 'paid' && <th>Tracking J&T</th>}
                   <th></th>
                </tr>
              </thead>
              <tbody>
                {invoices.data.length === 0 ? (
                  <tr>
                    <td colSpan={data.payment_status === 'paid' ? 8 : 7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Receipt className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Invoice</p>
                        <p className="admin-table-empty-copy">Belum ada invoice lagi.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  invoices.data.map((inv) => {
                    const status = statusConfig[inv.payment_status] ?? statusConfig.unpaid;
                    const customerName = inv.customer_name ?? inv.user?.name ?? '-';
                    const displayPaid = inv.payment_status === 'partial' || inv.payment_status === 'paid'
                      ? Number(inv.total_paid ?? 0)
                      : Number(inv.payment_amount ?? 0);
                    return (
                      <tr key={inv.id}>
                        <td className="font-medium text-slate-900">{inv.invoice_no}</td>
                        <td>
                          <p className="text-sm font-medium text-slate-900">{customerName}</p>
                          {inv.customer_phone && <p className="text-xs text-slate-500">{inv.customer_phone}</p>}
                        </td>
                        <td className="font-medium">{formatCurrency(inv.amount)}</td>
                        <td>
                          {displayPaid > 0 ? (
                            <div>
                              <p className="font-medium text-emerald-600">{formatCurrency(displayPaid)}</p>
                              {inv.payment_status === 'partial' && Number(inv.total_paid ?? 0) > 0 && (
                                <p className="text-[11px] text-slate-400">Baki: {formatCurrency(Math.max(0, inv.amount - Number(inv.total_paid ?? 0)))}</p>
                              )}
                            </div>
                          ) : (
                            <span className="text-slate-400">-</span>
                          )}
                        </td>
                        <td>
                          <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${status.class}`}>
                            {status.label}
                          </span>
                        </td>
                        <td className="text-slate-500">{formatDate(inv.issue_date)}</td>
                        {data.payment_status === 'paid' && (
                          <td>
                            <TrackingNumberForm
                              invoiceId={inv.id}
                              initialTrackingNo={inv.tracking_no ?? inv.order?.tracking_no ?? ''}
                            />
                          </td>
                        )}
                        <td>
                          <div className="flex items-center gap-1">
                            <Link
                              href={route('admin.invoices.edit', inv.id)}
                              aria-label={`Edit invoice ${inv.invoice_no}`}
                              title="Edit invoice"
                              className="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 hover:text-brand-600"
                            >
                              <Pencil className="h-4 w-4" />
                            </Link>
                            <Link
                              href={`${route('admin.invoices.show', inv.id)}${data.payment_status ? `?tab=${data.payment_status}` : ''}`}
                              className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-sm font-medium text-brand-600 transition hover:bg-brand-50"
                            >
                              <Eye className="h-4 w-4" />
                              Lihat
                            </Link>
                            <button
                              type="button"
                              onClick={() => handleDelete(inv.id, inv.invoice_no)}
                              aria-label={`Padam invoice ${inv.invoice_no}`}
                              className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                            >
                              <Trash2 className="h-4 w-4" />
                              Padam
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>

          {invoices.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {invoices.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                  )
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
