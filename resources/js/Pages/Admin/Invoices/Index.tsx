import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Search, Receipt, Eye, Plus } from 'lucide-react';
import { useState } from 'react';
import { formatDate } from '@/lib/utils';

interface Invoice {
  id: number;
  invoice_no: string;
  amount: number;
  issue_date: string;
  payment_status: string;
  customer_name: string | null;
  customer_phone: string | null;
  order: { order_no: string } | null;
  user: { name: string; email: string } | null;
  approver: { name: string } | null;
}

interface InvoicesIndexProps {
  invoices: {
    data: Invoice[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  filters: {
    search: string;
    payment_status: string;
  };
}

export default function InvoicesIndex({ invoices, filters }: InvoicesIndexProps) {
  const { data, setData, get } = useForm({
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

  const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(amount);

  const statusConfig: Record<string, { label: string; class: string }> = {
    unpaid: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-700 border-amber-200' },
    submitted: { label: 'Menunggu', class: 'bg-blue-100 text-blue-700 border-blue-200' },
    paid: { label: 'Dibayar', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' },
    rejected: { label: 'Ditolak', class: 'bg-rose-100 text-rose-700 border-rose-200' },
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
          <form onSubmit={handleSearch} className="flex flex-1 items-center gap-3">
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
            <select
              value={data.payment_status}
              onChange={(e) => {
                setData('payment_status', e.target.value);
                handleSearch(e as unknown as React.FormEvent);
              }}
              className="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none"
            >
              <option value="">Semua Status</option>
              <option value="unpaid">Belum Bayar</option>
              <option value="submitted">Menunggu</option>
              <option value="paid">Dibayar</option>
              <option value="rejected">Ditolak</option>
            </select>
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
                  <th>Status</th>
                  <th>Tarikh</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {invoices.data.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="py-16 text-center">
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
                    return (
                      <tr key={inv.id}>
                        <td className="font-medium text-slate-900">{inv.invoice_no}</td>
                        <td>
                          <p className="text-sm font-medium text-slate-900">{customerName}</p>
                          {inv.customer_phone && <p className="text-xs text-slate-500">{inv.customer_phone}</p>}
                        </td>
                        <td className="font-medium">{formatCurrency(inv.amount)}</td>
                        <td>
                          <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${status.class}`}>
                            {status.label}
                          </span>
                        </td>
                        <td className="text-slate-500">{formatDate(inv.issue_date)}</td>
                        <td>
                          <Link
                            href={route('admin.invoices.show', inv.id)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-brand-600 hover:bg-brand-50 transition"
                          >
                            <Eye className="h-4 w-4" />
                            Lihat
                          </Link>
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