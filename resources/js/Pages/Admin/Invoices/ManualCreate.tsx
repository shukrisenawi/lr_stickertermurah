import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Save, Trash2, Search, ChevronDown } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface CustomerAddress {
  id: number;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

interface Customer {
  id: number;
  name: string;
  email: string;
  addresses: CustomerAddress[];
}

interface InvoiceItemForm {
  id: string;
  description: string;
  quantity: string;
  unit_price: string;
}

interface InvoiceItemPayload {
  description: string;
  quantity: number;
  unit_price: number;
}

interface ManualCreateProps {
  customers: Customer[];
}

interface FormData {
  user_id: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  invoice_no: string;
  issue_date: string;
  amount: string;
  notes: string;
  items: InvoiceItemPayload[];
}

export default function ManualCreate({ customers }: ManualCreateProps) {
  // Auto-select user from query parameter ?user_id=
  const urlParams = new URLSearchParams(window.location.search);
  const preselectedUserId = urlParams.get('user_id') ?? '';

  const [items, setItems] = useState<InvoiceItemForm[]>([
    { id: crypto.randomUUID(), description: '', quantity: '1', unit_price: '' },
  ]);

  const { data, setData, post, processing, errors } = useForm<FormData>({
    user_id: preselectedUserId,
    customer_name: '',
    customer_phone: '',
    customer_address: '',
    invoice_no: '',
    issue_date: new Date().toISOString().split('T')[0],
    amount: '',
    notes: '',
    items: [],
  });

  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null);
  const [customerSearch, setCustomerSearch] = useState('');
  const [showCustomerDropdown, setShowCustomerDropdown] = useState(false);

  const filteredCustomers = useMemo(() => {
    const q = customerSearch.toLowerCase().trim();
    if (!q) return customers;
    return customers.filter((c) => {
      const phones = c.addresses.map((a) => a.no_hp ?? '').join(' ').toLowerCase();
      return c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q) || phones.includes(q);
    });
  }, [customers, customerSearch]);

  const selectedCustomer = useMemo(() => {
    return customers.find((c) => String(c.id) === data.user_id) ?? null;
  }, [customers, data.user_id]);

  useEffect(() => {
    if (selectedCustomer) {
      const defaultAddr = selectedCustomer.addresses.find((a) => a.is_default) ?? selectedCustomer.addresses[0] ?? null;
      setData('customer_name', defaultAddr?.recipient_name ?? selectedCustomer.name);
      setData('customer_phone', defaultAddr?.no_hp ?? '');
      setData('customer_address', defaultAddr?.address ?? '');
      setSelectedAddressId(defaultAddr?.id ?? null);
    }
  }, [selectedCustomer, setData]);

  const handleAddressSelect = (addressId: number) => {
    const addr = selectedCustomer?.addresses.find((a) => a.id === addressId) ?? null;
    if (addr) {
      setSelectedAddressId(addressId);
      setData('customer_name', addr.recipient_name ?? selectedCustomer?.name ?? '');
      setData('customer_phone', addr.no_hp ?? '');
      setData('customer_address', addr.address);
    }
  };

  const calculatedTotal = useMemo(() => {
    return items.reduce((sum, item) => {
      const qty = parseInt(item.quantity || '0', 10);
      const price = parseFloat(item.unit_price || '0');
      return sum + qty * price;
    }, 0);
  }, [items]);

  useEffect(() => {
    setData('amount', calculatedTotal.toFixed(2));
  }, [calculatedTotal, setData]);

  const updateItem = (id: string, field: keyof InvoiceItemForm, value: string) => {
    setItems((prev) => prev.map((item) => (item.id === id ? { ...item, [field]: value } : item)));
  };

  const addItem = () => {
    setItems((prev) => [
      ...prev,
      { id: crypto.randomUUID(), description: '', quantity: '1', unit_price: '' },
    ]);
  };

  const removeItem = (id: string) => {
    if (items.length <= 1) return;
    setItems((prev) => prev.filter((item) => item.id !== id));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    setData(
      'items',
      items.map(({ description, quantity, unit_price }) => ({
        description,
        quantity: parseInt(quantity || '0', 10),
        unit_price: parseFloat(unit_price || '0'),
      }))
    );

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
            <p className="admin-page-copy">Pilih pelanggan, tambah item, dan jana invoice berasingan.</p>
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
                Maklumat Pelanggan
              </h3>

              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Pelanggan
                </label>
                <div className="relative">
                  {/* Selected customer chip or search input */}
                  {selectedCustomer ? (
                    <div className="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm">
                      <div className="min-w-0 flex-1">
                        <p className="truncate font-medium text-slate-900">{selectedCustomer.name}</p>
                        <p className="truncate text-xs text-slate-500">{selectedCustomer.email}</p>
                      </div>
                      <button
                        type="button"
                        onClick={() => {
                          setData('user_id', '');
                          setCustomerSearch('');
                          setShowCustomerDropdown(true);
                        }}
                        className="ml-2 shrink-0 text-xs font-semibold text-brand-600 hover:underline"
                      >
                        Tukar
                      </button>
                    </div>
                  ) : (
                    <div className="relative">
                      <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                      <input
                        type="text"
                        autoComplete="off"
                        autoCorrect="off"
                        autoCapitalize="off"
                        spellCheck={false}
                        value={customerSearch}
                        onChange={(e) => {
                          setCustomerSearch(e.target.value);
                          setShowCustomerDropdown(true);
                        }}
                        onFocus={() => setShowCustomerDropdown(true)}
                        onBlur={() => setTimeout(() => setShowCustomerDropdown(false), 200)}
                        placeholder="Cari nama, emel atau no telefon..."
                        className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      />
                      <ChevronDown className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    </div>
                  )}

                  {/* Dropdown list */}
                  {showCustomerDropdown && !selectedCustomer && (
                    <div className="absolute z-30 mt-1 max-h-72 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg">
                      {filteredCustomers.length === 0 ? (
                        <p className="px-4 py-6 text-center text-sm text-slate-400">Tiada pelanggan dijumpai.</p>
                      ) : (
                        filteredCustomers.map((customer) => {
                          const phone = customer.addresses.find((a) => a.is_default)?.no_hp ?? customer.addresses[0]?.no_hp ?? '';
                          return (
                            <button
                              key={customer.id}
                              type="button"
                              onClick={() => {
                                setData('user_id', String(customer.id));
                                setCustomerSearch('');
                                setShowCustomerDropdown(false);
                              }}
                              className="flex w-full items-start gap-3 px-4 py-2.5 text-left transition hover:bg-brand-50"
                            >
                              <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium text-slate-900">{customer.name}</p>
                                <p className="truncate text-xs text-slate-500">
                                  {phone && <span>{phone} • </span>}{customer.email}
                                </p>
                              </div>
                            </button>
                          );
                        })
                      )}
                    </div>
                  )}
                </div>
                {errors.user_id && (
                  <p className="mt-1 text-xs text-rose-600">{errors.user_id}</p>
                )}
              </div>

              {/* Dropdown Alamat jika pelanggan ada lebih dari satu alamat */}
              {selectedCustomer && selectedCustomer.addresses.length > 0 && (
                <div>
                  <label htmlFor="address_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Pilih Alamat ({selectedCustomer.addresses.length} alamat tersimpan)
                  </label>
                  <select
                    id="address_id"
                    value={selectedAddressId ?? ''}
                    onChange={(e) => handleAddressSelect(parseInt(e.target.value))}
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  >
                    {selectedCustomer.addresses.map((addr) => (
                      <option key={addr.id} value={addr.id}>
                        {addr.is_default ? '★ Utama — ' : ''}{addr.recipient_name ?? selectedCustomer.name} · {addr.no_hp ?? ''} — {addr.address.slice(0, 60)}{addr.address.length > 60 ? '...' : ''}
                      </option>
                    ))}
                  </select>
                  <p className="mt-1 text-xs text-slate-400">Pilih alamat untuk auto-isi No. Telefon & Alamat di bawah.</p>
                </div>
              )}

              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label htmlFor="customer_name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    Nama Pelanggan
                  </label>
                  <input
                    id="customer_name"
                    type="text"
                    value={data.customer_name}
                    onChange={(e) => setData('customer_name', e.target.value)}
                    placeholder="Nama penuh"
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    required
                  />
                  {errors.customer_name && (
                    <p className="mt-1 text-xs text-rose-600">{errors.customer_name}</p>
                  )}
                </div>
                <div>
                  <label htmlFor="customer_phone" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                    No. Telefon
                  </label>
                  <input
                    id="customer_phone"
                    type="text"
                    value={data.customer_phone}
                    onChange={(e) => setData('customer_phone', e.target.value)}
                    placeholder="0123456789"
                    className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    required
                  />
                  {errors.customer_phone && (
                    <p className="mt-1 text-xs text-rose-600">{errors.customer_phone}</p>
                  )}
                </div>
              </div>

              <div>
                <label htmlFor="customer_address" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Alamat
                </label>
                <textarea
                  id="customer_address"
                  value={data.customer_address}
                  onChange={(e) => setData('customer_address', e.target.value)}
                  rows={3}
                  placeholder="Alamat penuh penghantaran"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.customer_address && (
                  <p className="mt-1 text-xs text-rose-600">{errors.customer_address}</p>
                )}
              </div>
            </div>

            <div className="admin-flat-card p-6 space-y-4">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                  Item Invoice
                </h3>
                <button
                  type="button"
                  onClick={addItem}
                  className="admin-btn-secondary text-xs"
                >
                  <Plus className="h-3 w-3" />
                  Tambah Item
                </button>
              </div>

              {items.map((item, index) => (
                <div key={item.id} className="rounded-xl border border-slate-200 bg-white p-4 space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                      Item #{index + 1}
                    </span>
                    {items.length > 1 && (
                      <button
                        type="button"
                        onClick={() => removeItem(item.id)}
                        className="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:text-rose-700"
                      >
                        <Trash2 className="h-3 w-3" />
                        Buang
                      </button>
                    )}
                  </div>
                  <div>
                    <input
                      type="text"
                      value={item.description}
                      onChange={(e) => updateItem(item.id, 'description', e.target.value)}
                      placeholder="Penerangan item"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                  </div>
                  <div className="grid grid-cols-2 gap-3">
                    <div>
                      <input
                        type="number"
                        min="1"
                        value={item.quantity}
                        onChange={(e) => updateItem(item.id, 'quantity', e.target.value)}
                        placeholder="Kuantiti"
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        value={item.unit_price}
                        onChange={(e) => updateItem(item.id, 'unit_price', e.target.value)}
                        placeholder="Harga unit (RM)"
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                  </div>
                  <div className="text-right text-sm font-medium text-slate-900">
                    Subtotal: {formatCurrency(parseInt(item.quantity || '0', 10) * parseFloat(item.unit_price || '0'))}
                  </div>
                </div>
              ))}

              {(errors as Record<string, string>).items && (
                <p className="text-xs text-rose-600">{(errors as Record<string, string>).items}</p>
              )}
            </div>
          </div>

          <div className="space-y-6">
            <div className="admin-flat-card p-6 space-y-4">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
                Butiran Invoice
              </h3>

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

            <div className="admin-flat-card p-6">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
                Jumlah Keseluruhan
              </h3>
              <div className="mb-4 text-2xl font-bold text-brand-600">
                {formatCurrency(calculatedTotal)}
              </div>
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
