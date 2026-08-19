import AdminLayout from '@/Components/Layouts/AdminLayout';
import { type PageProps } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import {
  AlertCircle,
  CheckCircle2,
  ChevronDown,
  ContactRound,
  Link2,
  Phone,
  Search,
  ShieldCheck,
  Unlink,
  UserPlus,
  Users,
} from 'lucide-react';
import { useState } from 'react';

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
  email: string | null;
  no_tel: string | null;
  addresses: CustomerAddress[];
}

interface Connection {
  email: string | null;
  connected_at: string | null;
}

interface GoogleContactsProps {
  isConfigured: boolean;
  callbackUrl: string;
  connection: Connection | null;
  customers: Customer[];
}

interface ManualForm {
  name: string;
  phone: string;
  email: string;
  address: string;
}

interface CustomerForm {
  customer_id: string;
  address_id: string;
}

const fieldClass = 'mt-1.5';

export default function GoogleContacts({ isConfigured, callbackUrl, connection, customers }: GoogleContactsProps) {
  const [customerSearch, setCustomerSearch] = useState('');
  const [showCustomerDropdown, setShowCustomerDropdown] = useState(false);
  const manualForm = useForm<ManualForm>({
    name: '',
    phone: '',
    email: '',
    address: '',
  });
  const customerForm = useForm<CustomerForm>({
    customer_id: '',
    address_id: '',
  });

  const selectedCustomer = customers.find((customer) => String(customer.id) === customerForm.data.customer_id) ?? null;
  const selectedAddress = selectedCustomer?.addresses.find((address) => String(address.id) === customerForm.data.address_id) ?? null;
  const query = customerSearch.trim().toLowerCase();
  const filteredCustomers = customers
    .filter((customer) => {
      if (query === '') return true;

      const searchable = [
        customer.name,
        customer.email ?? '',
        customer.no_tel ?? '',
        ...customer.addresses.flatMap((address) => [address.recipient_name ?? '', address.no_hp ?? '']),
      ].join(' ').toLowerCase();

      return searchable.includes(query);
    })
    .slice(0, 50);

  const selectCustomer = (customer: Customer) => {
    const defaultAddress = customer.addresses.find((address) => address.is_default) ?? customer.addresses[0] ?? null;
    customerForm.setData({
      customer_id: String(customer.id),
      address_id: defaultAddress ? String(defaultAddress.id) : '',
    });
    setCustomerSearch('');
    setShowCustomerDropdown(false);
  };

  const disconnect = () => {
    if (window.confirm('Putuskan sambungan akaun Google Contacts ini?')) {
      router.post(route('admin.contacts.google.disconnect'));
    }
  };

  return (
    <AdminLayout>
      <Head title="Contact" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <ContactRound className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Google People</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Contact</h2>
            <p className="admin-page-copy">Tambah contact manual atau terus daripada rekod customer.</p>
          </div>

          {connection && (
            <div className="admin-page-actions">
              <span className="admin-status bg-emerald-50 text-emerald-700">
                <CheckCircle2 className="h-3.5 w-3.5" />
                {connection.email ?? 'Google disambungkan'}
              </span>
              <button type="button" onClick={disconnect} className="admin-btn-secondary">
                <Unlink className="h-4 w-4" />
                Putuskan
              </button>
            </div>
          )}
        </div>

        {!isConfigured && (
          <div className="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
            <div className="flex items-start gap-3">
              <AlertCircle className="mt-0.5 h-5 w-5 shrink-0" />
              <div>
                <p className="font-semibold">Google OAuth belum dikonfigurasi</p>
                <p className="mt-1 text-sm text-amber-800">
                  Isi <code>GOOGLE_CLIENT_ID</code> dan <code>GOOGLE_CLIENT_SECRET</code>, aktifkan People API, kemudian daftar callback <code>{callbackUrl}</code> dalam Google Cloud.
                </p>
              </div>
            </div>
          </div>
        )}

        {isConfigured && !connection && (
          <div className="admin-flat-card overflow-hidden">
            <div className="grid gap-0 lg:grid-cols-[1.2fr_0.8fr]">
              <div className="p-6 sm:p-8">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                  <Link2 className="h-6 w-6" />
                </div>
                <h3 className="mt-5 text-xl font-bold text-slate-900">Sambungkan Google Contacts</h3>
                <p className="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                  Kebenaran Google diperlukan untuk menyemak nombor sedia ada dan menyimpan contact baharu ke akaun pilihan anda.
                </p>
                <div className="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                  <p className="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">Authorized redirect URI</p>
                  <code className="mt-1 block break-all text-xs text-slate-700">{callbackUrl}</code>
                </div>
                <a href={route('admin.contacts.google.connect')} className="admin-btn-primary mt-6">
                  <Link2 className="h-4 w-4" />
                  Sambung Akaun Google
                </a>
              </div>
              <div className="border-t border-slate-200 bg-slate-50 p-6 sm:p-8 lg:border-l lg:border-t-0">
                <p className="admin-mini-label">Perlindungan Pendua</p>
                <div className="mt-4 flex items-start gap-3">
                  <ShieldCheck className="h-5 w-5 shrink-0 text-emerald-600" />
                  <p className="text-sm leading-6 text-slate-600">
                    Sistem membandingkan nombor yang dinormalisasi dengan semua Google Contacts sebelum menyimpan.
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {connection && (
          <>
            <div className="grid gap-6 xl:grid-cols-2">
              <form
                onSubmit={(event) => {
                  event.preventDefault();
                  manualForm.post(route('admin.contacts.google.manual.store'), {
                    preserveScroll: true,
                    onSuccess: (page) => {
                      const flash = page.props.flash as PageProps['flash'] | undefined;
                      if (flash?.success) manualForm.reset();
                    },
                  });
                }}
                className="admin-flat-card p-5 sm:p-6"
              >
                <div className="flex items-start gap-3 border-b border-slate-100 pb-5">
                  <div className="admin-icon-badge">
                    <UserPlus className="h-5 w-5" />
                  </div>
                  <div>
                    <h3 className="font-bold text-slate-900">Tambah Manual</h3>
                    <p className="mt-0.5 text-sm text-slate-500">Masukkan maklumat contact baharu.</p>
                  </div>
                </div>

                <div className="mt-5 grid gap-4 sm:grid-cols-2">
                  <div className="sm:col-span-2">
                    <label htmlFor="contact-name">Nama contact</label>
                    <input
                      id="contact-name"
                      type="text"
                      value={manualForm.data.name}
                      onChange={(event) => manualForm.setData('name', event.target.value)}
                      className={fieldClass}
                      placeholder="Contoh: Nur Aisyah"
                      required
                    />
                    {manualForm.errors.name && <p className="mt-1 text-xs text-rose-600">{manualForm.errors.name}</p>}
                  </div>
                  <div>
                    <label htmlFor="contact-phone">Nombor telefon</label>
                    <input
                      id="contact-phone"
                      type="text"
                      inputMode="tel"
                      value={manualForm.data.phone}
                      onChange={(event) => manualForm.setData('phone', event.target.value)}
                      className={fieldClass}
                      placeholder="011-1234 5678"
                      required
                    />
                    {manualForm.errors.phone && <p className="mt-1 text-xs text-rose-600">{manualForm.errors.phone}</p>}
                  </div>
                  <div>
                    <label htmlFor="contact-email">Emel (pilihan)</label>
                    <input
                      id="contact-email"
                      type="email"
                      value={manualForm.data.email}
                      onChange={(event) => manualForm.setData('email', event.target.value)}
                      className={fieldClass}
                      placeholder="customer@email.com"
                    />
                    {manualForm.errors.email && <p className="mt-1 text-xs text-rose-600">{manualForm.errors.email}</p>}
                  </div>
                  <div className="sm:col-span-2">
                    <label htmlFor="contact-address">Alamat (pilihan)</label>
                    <textarea
                      id="contact-address"
                      rows={3}
                      value={manualForm.data.address}
                      onChange={(event) => manualForm.setData('address', event.target.value)}
                      className={fieldClass}
                      placeholder="Alamat penuh customer"
                    />
                    {manualForm.errors.address && <p className="mt-1 text-xs text-rose-600">{manualForm.errors.address}</p>}
                  </div>
                </div>

                <button type="submit" disabled={manualForm.processing} className="admin-btn-primary mt-5 w-full disabled:cursor-not-allowed disabled:opacity-60">
                  <Phone className="h-4 w-4" />
                  {manualForm.processing ? 'Menyemak nombor...' : 'Semak & Simpan Contact'}
                </button>
              </form>

              <form
                onSubmit={(event) => {
                  event.preventDefault();
                  customerForm.post(route('admin.contacts.google.customer.store'), { preserveScroll: true });
                }}
                className="admin-flat-card p-5 sm:p-6"
              >
                <div className="flex items-start gap-3 border-b border-slate-100 pb-5">
                  <div className="admin-icon-badge">
                    <Users className="h-5 w-5" />
                  </div>
                  <div>
                    <h3 className="font-bold text-slate-900">Daripada Data Customer</h3>
                    <p className="mt-0.5 text-sm text-slate-500">Pilih customer dan alamat yang hendak digunakan.</p>
                  </div>
                </div>

                <div className="mt-5">
                  <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Customer</p>
                  <div className="relative mt-1.5">
                    {selectedCustomer ? (
                      <div className="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                        <div className="min-w-0">
                          <p className="truncate text-sm font-semibold text-slate-900">{selectedCustomer.name}</p>
                          <p className="truncate text-xs text-slate-500">{selectedCustomer.no_tel ?? selectedCustomer.email ?? 'Tiada maklumat tambahan'}</p>
                        </div>
                        <button
                          type="button"
                          onClick={() => {
                            customerForm.reset();
                            setShowCustomerDropdown(true);
                          }}
                          className="text-xs font-semibold text-brand-600 hover:underline"
                        >
                          Tukar
                        </button>
                      </div>
                    ) : (
                      <div className="relative">
                        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                          id="customer-search"
                          type="search"
                          value={customerSearch}
                          onChange={(event) => {
                            setCustomerSearch(event.target.value);
                            setShowCustomerDropdown(true);
                          }}
                          onFocus={() => setShowCustomerDropdown(true)}
                          onBlur={() => window.setTimeout(() => setShowCustomerDropdown(false), 150)}
                          className="pl-10 pr-10"
                          placeholder="Cari nama, emel atau nombor..."
                          autoComplete="off"
                        />
                        <ChevronDown className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                      </div>
                    )}

                    {showCustomerDropdown && !selectedCustomer && (
                      <div className="absolute z-30 mt-1 max-h-72 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                        {filteredCustomers.length > 0 ? filteredCustomers.map((customer) => {
                          const defaultAddress = customer.addresses.find((address) => address.is_default) ?? customer.addresses[0];
                          const phone = defaultAddress?.no_hp ?? customer.no_tel;

                          return (
                            <button
                              key={customer.id}
                              type="button"
                              onMouseDown={(event) => event.preventDefault()}
                              onClick={() => selectCustomer(customer)}
                              className="flex w-full items-start gap-3 px-4 py-2.5 text-left transition hover:bg-brand-50"
                            >
                              <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                {customer.name.charAt(0).toUpperCase()}
                              </span>
                              <span className="min-w-0">
                                <span className="block truncate text-sm font-medium text-slate-900">{customer.name}</span>
                                <span className="block truncate text-xs text-slate-500">{phone ?? customer.email ?? 'Tiada nombor'}</span>
                              </span>
                            </button>
                          );
                        }) : (
                          <p className="px-4 py-6 text-center text-sm text-slate-500">Tiada customer dijumpai.</p>
                        )}
                      </div>
                    )}
                  </div>
                  {customerForm.errors.customer_id && <p className="mt-1 text-xs text-rose-600">{customerForm.errors.customer_id}</p>}
                </div>

                {selectedCustomer && selectedCustomer.addresses.length > 0 && (
                  <div className="mt-4">
                    <label htmlFor="customer-address">Alamat / penerima</label>
                    <select
                      id="customer-address"
                      value={customerForm.data.address_id}
                      onChange={(event) => customerForm.setData('address_id', event.target.value)}
                      className="mt-1.5"
                    >
                      {selectedCustomer.addresses.map((address) => (
                        <option key={address.id} value={address.id}>
                          {address.recipient_name ?? selectedCustomer.name} - {address.no_hp ?? selectedCustomer.no_tel ?? 'tiada nombor'}
                        </option>
                      ))}
                    </select>
                    {customerForm.errors.address_id && <p className="mt-1 text-xs text-rose-600">{customerForm.errors.address_id}</p>}
                  </div>
                )}

                {selectedCustomer && (
                  <div className="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p className="admin-mini-label">Contact akan disimpan sebagai</p>
                    <dl className="mt-3 space-y-2 text-sm">
                      <div className="flex gap-3">
                        <dt className="w-20 shrink-0 text-slate-500">Nama</dt>
                        <dd className="font-medium text-slate-900">{selectedAddress?.recipient_name ?? selectedCustomer.name}</dd>
                      </div>
                      <div className="flex gap-3">
                        <dt className="w-20 shrink-0 text-slate-500">Telefon</dt>
                        <dd className="font-medium text-slate-900">{selectedAddress?.no_hp ?? selectedCustomer.no_tel ?? '-'}</dd>
                      </div>
                      <div className="flex gap-3">
                        <dt className="w-20 shrink-0 text-slate-500">Emel</dt>
                        <dd className="min-w-0 truncate font-medium text-slate-900">{selectedCustomer.email ?? '-'}</dd>
                      </div>
                    </dl>
                  </div>
                )}

                <button
                  type="submit"
                  disabled={customerForm.processing || !selectedCustomer}
                  className="admin-btn-primary mt-5 w-full disabled:cursor-not-allowed disabled:opacity-60"
                >
                  <ShieldCheck className="h-4 w-4" />
                  {customerForm.processing ? 'Menyemak nombor...' : 'Semak & Tambah ke Google'}
                </button>
              </form>
            </div>
          </>
        )}
      </div>
    </AdminLayout>
  );
}
