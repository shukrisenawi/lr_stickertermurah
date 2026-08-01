import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link } from '@inertiajs/react';
import { CreditCard, Eye, FileText } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Invoice {
  id: number;
  invoice_no: string;
  amount: number;
  payment_status: string;
  issue_date: string;
  order: { order_no: string } | null;
}

interface MemberInvoicesProps {
  invoices: {
    data: Invoice[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function MemberInvoicesIndex({ invoices }: MemberInvoicesProps) {
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'paid': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
      case 'submitted': return 'bg-blue-100 text-blue-700 border-blue-200';
      case 'rejected': return 'bg-rose-100 text-rose-700 border-rose-200';
      case 'unpaid':
      default: return 'bg-amber-100 text-amber-700 border-amber-200';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  return (
    <MemberLayout>
      <Head title="Invoice Saya" />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8">
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-slate-900">Invoice Saya</h1>
          <p className="mt-1 text-sm text-slate-500">Semua invoice yang disediakan untuk anda.</p>
        </div>

        <div className="frontend-table-card">
          <div className="frontend-table-wrap">
            <table className="frontend-table">
              <thead>
                <tr>
                  <th>No. Invoice</th>
                  <th>No. Order</th>
                  <th>Jumlah</th>
                  <th>Status Bayaran</th>
                  <th>Tarikh</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {invoices.data.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="py-14 text-center">
                      <div className="frontend-table-empty">
                        <FileText className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="text-lg font-semibold text-slate-900">Tiada Invoice</p>
                        <p className="text-sm text-slate-500">Anda belum mempunyai sebarang invoice.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  invoices.data.map((invoice) => (
                    <tr key={invoice.id}>
                      <td className="font-medium text-slate-900">{invoice.invoice_no}</td>
                      <td className="text-slate-500">{invoice.order?.order_no ?? '-'}</td>
                      <td className="font-medium">{formatCurrency(invoice.amount)}</td>
                      <td>
                        <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider ${getStatusColor(invoice.payment_status)}`}>
                          {invoice.payment_status}
                        </span>
                      </td>
                      <td className="text-slate-500">{formatDate(invoice.issue_date)}</td>
                      <td>
                        <div className="flex items-center gap-2">
                          <Link
                            href={route('member.invoices.show', invoice.id)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-brand-600 hover:bg-brand-50 transition"
                          >
                            <Eye className="h-4 w-4" />
                            Lihat
                          </Link>
                          {invoice.payment_status !== 'paid' && (
                            <Link
                              href={`${route('member.invoices.show', invoice.id)}?pay=1`}
                              className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 transition"
                            >
                              <CreditCard className="h-4 w-4" />
                              Bayar
                            </Link>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {invoices.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-5 py-4">
              <div className="flex items-center gap-2">
                {invoices.links.map((link, i) => {
                  const isPrevNext = link.label.includes('&laquo;') || link.label.includes('&raquo;');
                  const label = isPrevNext
                    ? link.label.replace('&laquo;', '«').replace('&raquo;', '»').replace(' Previous', '').replace('Next ', '')
                    : link.label;
                  return link.url ? (
                    <Link
                      key={`page-${i}`}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                        link.active
                          ? 'bg-brand-600 text-white'
                          : 'text-slate-600 hover:bg-slate-50'
                      }`}
                    >
                      {label}
                    </Link>
                  ) : (
                    <span
                      key={`page-${i}`}
                      className="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                    >
                      {label}
                    </span>
                  );
                })}
              </div>
            </div>
          )}
        </div>
      </div>
    </MemberLayout>
  );
}
