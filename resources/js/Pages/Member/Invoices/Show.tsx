import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link } from '@inertiajs/react';
import { Receipt, ArrowLeft } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Invoice {
  id: number;
  invoice_no: string;
  amount: number;
  issue_date: string;
  notes: string | null;
  order: {
    order_no: string;
    customer_name: string;
    customer_phone: string;
    customer_address: string;
    items: Array<{
      design: { name: string } | null;
      size: { name: string } | null;
      quantity: number;
      unit_price: number;
      subtotal: number;
    }>;
  };
}

interface InvoiceShowProps {
  invoice: Invoice;
}

export default function MemberInvoiceShow({ invoice }: InvoiceShowProps) {
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  return (
    <MemberLayout>
      <Head title={`Invoice ${invoice.invoice_no}`} />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8 space-y-6">
        <Link
          href={route('member.orders.index')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          Kembali ke Order Saya
        </Link>

        <div className="frontend-flat-card p-8">
          <div className="flex items-center justify-between mb-8">
            <div className="flex items-center gap-3">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-600">
                <Receipt className="h-6 w-6" />
              </div>
              <div>
                <h1 className="text-2xl font-bold text-slate-900">{invoice.invoice_no}</h1>
                <p className="text-sm text-slate-500">{formatDate(invoice.issue_date)}</p>
              </div>
            </div>
            <div className="text-right">
              <p className="text-sm text-slate-500">Jumlah</p>
              <p className="text-2xl font-bold text-brand-600">{formatCurrency(invoice.amount)}</p>
            </div>
          </div>

          <div className="border-t border-slate-200 pt-6 space-y-4">
            <div>
              <h3 className="text-sm font-semibold text-slate-900 mb-2">Maklumat Order</h3>
              <p className="text-sm text-slate-600">{invoice.order.order_no}</p>
              <p className="text-sm text-slate-600">{invoice.order.customer_name}</p>
              <p className="text-sm text-slate-600">{invoice.order.customer_phone}</p>
              <p className="text-sm text-slate-600">{invoice.order.customer_address}</p>
            </div>

            {invoice.notes && (
              <div>
                <h3 className="text-sm font-semibold text-slate-900 mb-2">Nota</h3>
                <p className="text-sm text-slate-600">{invoice.notes}</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </MemberLayout>
  );
}
