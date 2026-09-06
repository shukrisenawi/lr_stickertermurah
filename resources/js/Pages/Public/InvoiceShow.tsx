import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import CustomQuoteCalculator, { type CustomQuoteCalculatorItem } from '@/Components/CustomQuoteCalculator';
import PrintInvoice, { type PrintInvoiceItem } from '@/Components/PrintInvoice';
import { Head, usePage } from '@inertiajs/react';
import { Download, Printer } from 'lucide-react';
import { type PageProps } from '@/types';

interface InvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit_price: number;
  line_total: number;
}

interface PublicInvoice {
  invoice_no: string;
  issue_date: string;
  amount: number;
  notes: string | null;
  payment_status: string;
  payment_type: string | null;
  paid_at: string | null;
  customer_name: string | null;
  customer_phone: string | null;
  customer_address: string | null;
  tracking_no: string | null;
  order: { tracking_no: string | null } | null;
  items: InvoiceItem[];
  custom_quotes: CustomQuoteCalculatorItem[];
}

interface PublicInvoiceProps extends PageProps {
  invoice: PublicInvoice;
  minimumA3SheetsWithoutDesign: number;
  pdfUrl: string;
}

export default function InvoiceShow() {
  const { invoice, app, minimumA3SheetsWithoutDesign, pdfUrl } = usePage<PublicInvoiceProps>().props;
  const items: PrintInvoiceItem[] = invoice.items.map((item) => ({
    id: item.id,
    description: item.description,
    quantity: Number(item.quantity),
    unit_price: Number(item.unit_price),
    line_total: Number(item.line_total),
  }));

  return (
    <FrontendLayout hideNavbar>
      <Head title={`Invoice ${invoice.invoice_no}`} />
      <div className="min-h-screen bg-slate-50 px-4 py-8 sm:py-12">
        <div className="mx-auto max-w-[900px]">
          <div className="mb-5 rounded-2xl border border-brand-100 bg-brand-50 px-5 py-4 text-sm text-brand-900">
            Invoice ini dikongsi oleh StickerTermurah untuk rujukan anda.
          </div>
          <CustomQuoteCalculator items={invoice.custom_quotes} minimumA3SheetsWithoutDesign={minimumA3SheetsWithoutDesign} className="invoice-no-print mb-6" />
          <PrintInvoice
            invoiceNo={invoice.invoice_no}
            issueDate={invoice.issue_date}
            amount={Number(invoice.amount)}
            customerName={invoice.customer_name ?? '-'}
            customerPhone={invoice.customer_phone ?? '-'}
            customerAddress={invoice.customer_address ?? '-'}
            items={items}
            notes={invoice.notes}
            paymentStatus={invoice.payment_status}
            paymentType={invoice.payment_type}
            paidAt={invoice.paid_at}
            trackingNo={invoice.tracking_no ?? invoice.order?.tracking_no}
            brandName={app.company_name}
            brandAddress={app.company_address}
            brandPhone={app.company_phone}
            logoUrl={app.company_logo_url}
            brandEmail={app.admin_email}
          >
            <a href={pdfUrl} download className="admin-btn-secondary text-sm">
              <Download className="h-4 w-4" />
              Muat Turun PDF
            </a>
            <button type="button" onClick={() => window.print()} className="admin-btn-secondary text-sm">
              <Printer className="h-4 w-4" />
              Cetak Invoice
            </button>
          </PrintInvoice>
        </div>
      </div>
    </FrontendLayout>
  );
}
