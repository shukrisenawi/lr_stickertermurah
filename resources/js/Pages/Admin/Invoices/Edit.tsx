import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Save, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';

interface InvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit_price: number;
}

interface Invoice {
  id: number;
  invoice_no: string;
  issue_date: string;
  notes: string | null;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  amount: number;
  total_paid: number;
  items: InvoiceItem[];
}

interface EditInvoiceProps {
  invoice: Invoice;
}

interface ItemForm {
  key: string;
  description: string;
  quantity: string;
  unit_price: string;
}

interface InvoiceItemPayload {
  description: string;
  quantity: number;
  unit_price: number;
}

interface FormData {
  invoice_no: string;
  issue_date: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  notes: string;
  items: InvoiceItemPayload[];
}

const makeItemKey = () => `item-${crypto.randomUUID()}`;

export default function EditInvoice({ invoice }: EditInvoiceProps) {
  const [items, setItems] = useState<ItemForm[]>(() => invoice.items.length > 0
    ? invoice.items.map((item) => ({
      key: `item-${item.id}`,
      description: item.description,
      quantity: String(item.quantity),
      unit_price: Number(item.unit_price).toFixed(2),
    }))
    : [{ key: makeItemKey(), description: 'Item Invoice', quantity: '1', unit_price: Number(invoice.amount).toFixed(2) }]);

  const { data, setData, put, processing, errors } = useForm<FormData>({
    invoice_no: invoice.invoice_no,
    issue_date: invoice.issue_date,
    customer_name: invoice.customer_name,
    customer_phone: invoice.customer_phone,
    customer_address: invoice.customer_address,
    notes: invoice.notes ?? '',
    items: [],
  });

  const calculatedTotal = useMemo(() => items.reduce((sum, item) => {
    const quantity = parseInt(item.quantity || '0', 10);
    const unitPrice = parseFloat(item.unit_price || '0');
    return sum + quantity * unitPrice;
  }, 0), [items]);

  const updateItem = (key: string, field: keyof ItemForm, value: string) => {
    setItems((current) => current.map((item) => item.key === key ? { ...item, [field]: value } : item));
  };

  const addItem = () => {
    setItems((current) => [
      ...current,
      { key: makeItemKey(), description: '', quantity: '1', unit_price: '' },
    ]);
  };

  const removeItem = (key: string) => {
    if (items.length <= 1) return;
    setItems((current) => current.filter((item) => item.key !== key));
  };

  const formatCurrency = (amount: number) => new Intl.NumberFormat('ms-MY', {
    style: 'currency',
    currency: 'MYR',
  }).format(amount);

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();

    const itemPayload = items.map(({ description, quantity, unit_price }) => ({
      description,
      quantity: parseInt(quantity || '0', 10),
      unit_price: parseFloat(unit_price || '0'),
    }));

    setData('items', itemPayload);
    put(route('admin.invoices.update', invoice.id));
  };

  return (
    <AdminLayout>
      <Head title={`Edit ${invoice.invoice_no}`} />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Edit Invoice</h2>
            <p className="admin-page-copy">Kemaskini maklumat pelanggan, item dan tarikh invoice.</p>
          </div>
          <Link href={route('admin.invoices.show', invoice.id)} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        {invoice.total_paid > 0 && (
          <div className="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            Invoice ini sudah menerima bayaran sebanyak <strong>{formatCurrency(invoice.total_paid)}</strong>. Jumlah baharu tidak boleh kurang daripada jumlah tersebut.
          </div>
        )}

        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="space-y-6 lg:col-span-2">
            <div className="admin-flat-card space-y-4 p-6">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">Maklumat Pelanggan</h3>

              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label htmlFor="customer_name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Pelanggan</label>
                  <input
                    id="customer_name"
                    type="text"
                    value={data.customer_name}
                    onChange={(event) => setData('customer_name', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    required
                  />
                  {errors.customer_name && <p className="mt-1 text-xs text-rose-600">{errors.customer_name}</p>}
                </div>
                <div>
                  <label htmlFor="customer_phone" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">No. Telefon</label>
                  <input
                    id="customer_phone"
                    type="text"
                    value={data.customer_phone}
                    onChange={(event) => setData('customer_phone', event.target.value)}
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    required
                  />
                  {errors.customer_phone && <p className="mt-1 text-xs text-rose-600">{errors.customer_phone}</p>}
                </div>
              </div>

              <div>
                <label htmlFor="customer_address" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat</label>
                <textarea
                  id="customer_address"
                  value={data.customer_address}
                  onChange={(event) => setData('customer_address', event.target.value)}
                  rows={4}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.customer_address && <p className="mt-1 text-xs text-rose-600">{errors.customer_address}</p>}
              </div>
            </div>

            <div className="admin-flat-card space-y-4 p-6">
              <div className="flex items-center justify-between gap-3">
                <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">Item Invoice</h3>
                <button type="button" onClick={addItem} className="admin-btn-secondary text-xs">
                  <Plus className="h-3 w-3" />
                  Tambah Item
                </button>
              </div>

              {items.map((item, index) => (
                <div key={item.key} className="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">Item #{index + 1}</span>
                    {items.length > 1 && (
                      <button type="button" onClick={() => removeItem(item.key)} className="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:text-rose-700">
                        <Trash2 className="h-3 w-3" />
                        Buang
                      </button>
                    )}
                  </div>
                  <input
                    type="text"
                    value={item.description}
                    onChange={(event) => updateItem(item.key, 'description', event.target.value)}
                    placeholder="Penerangan item"
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    required
                  />
                  <div className="grid grid-cols-2 gap-3">
                    <input
                      type="number"
                      min="1"
                      value={item.quantity}
                      onChange={(event) => updateItem(item.key, 'quantity', event.target.value)}
                      placeholder="Kuantiti"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                    <input
                      type="number"
                      min="0"
                      step="0.01"
                      value={item.unit_price}
                      onChange={(event) => updateItem(item.key, 'unit_price', event.target.value)}
                      placeholder="Harga unit (RM)"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                  </div>
                  <p className="text-right text-sm font-medium text-slate-900">
                    Subtotal: {formatCurrency(parseInt(item.quantity || '0', 10) * parseFloat(item.unit_price || '0'))}
                  </p>
                </div>
              ))}
              {errors.items && <p className="text-xs text-rose-600">{errors.items}</p>}
            </div>
          </div>

          <div className="space-y-6">
            <div className="admin-flat-card space-y-4 p-6">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">Butiran Invoice</h3>

              <div>
                <label htmlFor="invoice_no" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">No. Invoice</label>
                <input
                  id="invoice_no"
                  type="text"
                  value={data.invoice_no}
                  onChange={(event) => setData('invoice_no', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.invoice_no && <p className="mt-1 text-xs text-rose-600">{errors.invoice_no}</p>}
              </div>

              <div>
                <label htmlFor="issue_date" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tarikh</label>
                <input
                  id="issue_date"
                  type="date"
                  value={data.issue_date}
                  onChange={(event) => setData('issue_date', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.issue_date && <p className="mt-1 text-xs text-rose-600">{errors.issue_date}</p>}
              </div>

              <div>
                <label htmlFor="notes" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Catatan</label>
                <textarea
                  id="notes"
                  value={data.notes}
                  onChange={(event) => setData('notes', event.target.value)}
                  rows={4}
                  placeholder="Catatan tambahan..."
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
                {errors.notes && <p className="mt-1 text-xs text-rose-600">{errors.notes}</p>}
              </div>
            </div>

            <div className="admin-flat-card space-y-4 p-6">
              <div>
                <p className="text-sm font-semibold uppercase tracking-wider text-slate-500">Jumlah Baharu</p>
                <p className="mt-1 text-2xl font-bold text-brand-600">{formatCurrency(calculatedTotal)}</p>
              </div>
              <button type="submit" disabled={processing} className="admin-btn-primary w-full text-sm">
                <Save className="h-4 w-4" />
                {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
              </button>
              <Link href={route('admin.invoices.show', invoice.id)} className="admin-btn-secondary block w-full text-center text-sm">
                Batal
              </Link>
            </div>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
