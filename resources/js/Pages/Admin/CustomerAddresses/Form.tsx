import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, ChevronDown, Search, Save } from 'lucide-react';
import { useState } from 'react';

type AddressTab = 'members' | 'non-members';

interface CustomerOption {
  id: number;
  name: string;
  email: string | null;
  no_tel: string | null;
}

interface AddressValue {
  id: number;
  user_id: number | null;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

interface CustomerAddressFormProps {
  address: AddressValue | null;
  customers: CustomerOption[];
  tab: AddressTab;
}

export default function CustomerAddressForm({ address, customers, tab }: CustomerAddressFormProps) {
  const { data, setData, post, put, processing, errors } = useForm({
    user_id: address?.user_id ? String(address.user_id) : '',
    recipient_name: address?.recipient_name ?? '',
    address: address?.address ?? '',
    no_hp: address?.no_hp ?? '',
    is_default: address?.is_default ?? false,
  });

  const isEditing = address !== null;
  const initialCustomer = customers.find((customer) => String(customer.id) === data.user_id);
  const [customerSearch, setCustomerSearch] = useState(initialCustomer?.name ?? '');
  const [showCustomerOptions, setShowCustomerOptions] = useState(false);
  const searchQuery = customerSearch.trim().toLowerCase();
  const filteredCustomers = customers
    .filter((customer) => {
      if (!searchQuery) return true;
      return [customer.name, customer.email ?? '', customer.no_tel ?? '']
        .some((value) => value.toLowerCase().includes(searchQuery));
    })
    .slice(0, 50);

  const selectCustomer = (customer: CustomerOption) => {
    setData('user_id', String(customer.id));
    setCustomerSearch(customer.name);
    setShowCustomerOptions(false);
  };

  const unlinkCustomer = () => {
    setData('user_id', '');
    setData('is_default', false);
    setCustomerSearch('');
    setShowCustomerOptions(false);
  };

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();

    if (isEditing) {
      put(route('admin.customer-addresses.update', address.id));
    } else {
      post(route('admin.customer-addresses.store'));
    }
  };

  return (
    <AdminLayout>
      <Head title={isEditing ? 'Kemaskini Customer Address' : 'Tambah Customer Address'} />
      <div className="max-w-2xl space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">
              {isEditing ? 'Kemaskini Customer Address' : 'Tambah Customer Address'}
            </h2>
            <p className="admin-page-copy">Urus maklumat alamat penghantaran customer.</p>
          </div>
          <Link href={route('admin.customer-addresses.index', { tab })} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card space-y-5 p-6">
          <div>
            <label htmlFor="customer_search" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              Pautkan Kepada Ahli
            </label>
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                id="customer_search"
                type="search"
                value={customerSearch}
                onChange={(event) => {
                  setCustomerSearch(event.target.value);
                  setShowCustomerOptions(true);
                  if (data.user_id) {
                    setData('user_id', '');
                    setData('is_default', false);
                  }
                }}
                onFocus={() => setShowCustomerOptions(true)}
                onBlur={() => window.setTimeout(() => setShowCustomerOptions(false), 150)}
                placeholder="Cari nama, email atau no. telefon ahli..."
                role="combobox"
                aria-expanded={showCustomerOptions}
                aria-controls="customer-options"
                autoComplete="off"
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
              <ChevronDown className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

              {showCustomerOptions && (
                <div id="customer-options" className="absolute z-20 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl">
                  <button
                    type="button"
                    onMouseDown={(event) => event.preventDefault()}
                    onClick={unlinkCustomer}
                    className="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-slate-600 transition hover:bg-slate-50"
                  >
                    <span>Bukan Ahli (tidak dipautkan)</span>
                    {!data.user_id && <Check className="h-4 w-4 text-brand-600" />}
                  </button>
                  {filteredCustomers.map((customer) => (
                    <button
                      key={customer.id}
                      type="button"
                      onMouseDown={(event) => event.preventDefault()}
                      onClick={() => selectCustomer(customer)}
                      className="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left transition hover:bg-slate-50"
                    >
                      <span className="min-w-0">
                        <span className="block truncate text-sm font-medium text-slate-900">{customer.name}</span>
                        <span className="block truncate text-xs text-slate-500">
                          {customer.email || customer.no_tel || 'Tiada email atau telefon'}
                        </span>
                      </span>
                      {data.user_id === String(customer.id) && <Check className="h-4 w-4 shrink-0 text-brand-600" />}
                    </button>
                  ))}
                  {filteredCustomers.length === 0 && (
                    <p className="px-3 py-3 text-center text-sm text-slate-500">Ahli tidak ditemui.</p>
                  )}
                  {filteredCustomers.length === 50 && (
                    <p className="px-3 py-2 text-center text-xs text-slate-400">Taip carian yang lebih khusus untuk melihat keputusan lain.</p>
                  )}
                </div>
              )}
            </div>
            {errors.user_id && <p className="mt-1 text-sm text-rose-600">{errors.user_id}</p>}
            <p className="mt-1 text-xs text-slate-500">Taip untuk cari ahli berdasarkan nama, email atau no. telefon.</p>
          </div>

          <div>
            <label htmlFor="recipient_name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              Nama Penerima
            </label>
            <input
              id="recipient_name"
              type="text"
              value={data.recipient_name}
              onChange={(event) => setData('recipient_name', event.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              required
            />
            {errors.recipient_name && <p className="mt-1 text-sm text-rose-600">{errors.recipient_name}</p>}
          </div>

          <div>
            <label htmlFor="no_hp" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              No. Telefon
            </label>
            <input
              id="no_hp"
              type="text"
              value={data.no_hp}
              onChange={(event) => setData('no_hp', event.target.value)}
              maxLength={20}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            />
            {errors.no_hp && <p className="mt-1 text-sm text-rose-600">{errors.no_hp}</p>}
          </div>

          <div>
            <label htmlFor="address" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              Alamat
            </label>
            <textarea
              id="address"
              value={data.address}
              onChange={(event) => setData('address', event.target.value)}
              rows={5}
              maxLength={500}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              required
            />
            {errors.address && <p className="mt-1 text-sm text-rose-600">{errors.address}</p>}
          </div>

          <label className="flex items-center gap-3 text-sm text-slate-700">
            <input
              type="checkbox"
              checked={data.is_default}
              disabled={!data.user_id}
              onChange={(event) => setData('is_default', event.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
            />
            Jadikan alamat default ahli ini
          </label>
          {errors.is_default && <p className="text-sm text-rose-600">{errors.is_default}</p>}

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.customer-addresses.index', { tab })} className="admin-btn-secondary flex-1 text-sm">
              Batal
            </Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              <Save className="h-4 w-4" />
              {processing ? 'Menyimpan...' : isEditing ? 'Kemaskini' : 'Simpan'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
