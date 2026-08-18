import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { AlertCircle, ArrowLeft, MapPin, Save, Search, UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';
import { type PageProps } from '@/types';

interface LookupAddress {
  id: number;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

interface RegisterLookup {
  phone: string | null;
  account_exists: boolean;
  addresses: LookupAddress[];
}

interface CreateProps extends PageProps {
  lookup: RegisterLookup | null;
  initialPhone: string;
}

export default function Create() {
  const { lookup, initialPhone } = usePage<CreateProps>().props;
  const { data, setData, post, processing, errors } = useForm({
    no_tel: initialPhone,
    email: '',
    recipient_name: '',
    address: '',
    address_id: '',
    mode: 'new' as 'matched' | 'new',
  });
  const [searching, setSearching] = useState(false);
  const [confirmedAddress, setConfirmedAddress] = useState(false);

  useEffect(() => {
    if (!lookup || lookup.account_exists) return;

    const address = lookup.addresses[0];
    if (!address) {
      setData('address_id', '');
      setData('mode', 'new');
      setConfirmedAddress(true);
      return;
    }

    setData('address_id', String(address.id));
    setData('recipient_name', address.recipient_name ?? '');
    setData('address', address.address);
    setData('mode', 'matched');
    setConfirmedAddress(false);
  }, [lookup, setData]);

  const selectedAddress = lookup?.addresses.find((address) => String(address.id) === data.address_id) ?? null;
  const showNewAddressForm = Boolean(
    lookup && !lookup.account_exists && (lookup.addresses.length === 0 || (data.mode === 'new' && confirmedAddress)),
  );

  const handleSearch = (event: React.FormEvent) => {
    event.preventDefault();
    setSearching(true);
    setConfirmedAddress(false);
    setData('address_id', '');
    setData('mode', 'new');
    setData('recipient_name', '');
    setData('address', '');
    router.get(route('admin.customers.create'), { no_tel: data.no_tel }, {
      preserveState: true,
      replace: true,
      onFinish: () => setSearching(false),
    });
  };

  const selectAddress = (address: LookupAddress) => {
    setData('address_id', String(address.id));
    setData('recipient_name', address.recipient_name ?? '');
    setData('address', address.address);
    setData('mode', 'matched');
    setConfirmedAddress(false);
  };

  const resetToNewAddress = () => {
    setData('address_id', '');
    setData('mode', 'new');
    setData('recipient_name', '');
    setData('address', '');
    setConfirmedAddress(true);
  };

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    post(route('admin.customers.store'));
  };

  const emailField = (
    <div>
      <label htmlFor="email" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
        Email <span className="font-normal normal-case tracking-normal text-slate-400">(pilihan)</span>
      </label>
      <input
        id="email"
        type="email"
        value={data.email}
        onChange={(event) => setData('email', event.target.value)}
        placeholder="customer@example.com"
        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
      />
      {errors.email && <p className="mt-1 text-xs text-rose-600">{errors.email}</p>}
    </div>
  );

  const defaultPasswordNotice = (
    <div className="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
      Password default customer: <strong>123</strong>
    </div>
  );

  return (
    <AdminLayout>
      <Head title="Tambah Customer" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Tambah Customer</h2>
            <p className="admin-page-copy">Cari nombor telefon dahulu seperti borang daftar customer di laman depan.</p>
          </div>
          <Link href={route('admin.customers.index')} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <div className="max-w-3xl space-y-6">
          <div className="admin-flat-card p-6">
            <div className="flex items-center gap-3 border-b border-slate-100 pb-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <Search className="h-5 w-5" />
              </div>
              <div>
                <h3 className="font-bold text-slate-900">Cari Alamat Customer</h3>
                <p className="text-xs text-slate-500">Masukkan nombor telefon untuk semak alamat yang pernah disimpan.</p>
              </div>
            </div>

            <form onSubmit={handleSearch} className="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
              <div className="flex-1">
                <label htmlFor="no_tel" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">No. Telefon</label>
                <input
                  id="no_tel"
                  type="tel"
                  inputMode="numeric"
                  value={data.no_tel}
                  onChange={(event) => setData('no_tel', event.target.value.replace(/\D/g, ''))}
                  placeholder="Contoh: 0112222333"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.no_tel && <p className="mt-1 text-xs text-rose-600">{errors.no_tel}</p>}
              </div>
              <button type="submit" disabled={searching} className="admin-btn-primary text-sm">
                <Search className="h-4 w-4" />
                {searching ? 'Sedang mencari...' : 'Cari Alamat'}
              </button>
            </form>
          </div>

          {lookup?.account_exists && (
            <div className="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
              <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
              <div>
                <p className="font-bold">Akaun untuk nombor ini sudah wujud.</p>
                <p className="mt-1 text-sm">Customer ini sudah ada dalam senarai. Sila gunakan akaun sedia ada.</p>
              </div>
            </div>
          )}

          {lookup && !lookup.account_exists && lookup.addresses.length === 0 && (
            <div className="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
              <AlertCircle className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
              <div>
                <p className="font-bold">Alamat customer belum ada dalam sistem.</p>
                <p className="mt-1 text-sm">Sila isi maklumat di bawah untuk daftar customer baharu.</p>
              </div>
            </div>
          )}

          {lookup && !lookup.account_exists && selectedAddress && !confirmedAddress && (
            <div className="admin-flat-card space-y-5 border-brand-200 p-6">
              <div className="flex items-start gap-3">
                <MapPin className="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                <div className="min-w-0 flex-1">
                  <p className="font-bold text-slate-900">Adakah ini alamat customer?</p>
                  {lookup.addresses.length > 1 && (
                    <select
                      value={data.address_id}
                      onChange={(event) => {
                        const address = lookup.addresses.find((item) => String(item.id) === event.target.value);
                        if (address) selectAddress(address);
                      }}
                      className="mt-3 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      {lookup.addresses.map((address) => (
                        <option key={address.id} value={address.id}>
                          {address.recipient_name ?? 'Penerima'} - {address.address}
                        </option>
                      ))}
                    </select>
                  )}
                  <p className="mt-3 font-semibold text-slate-900">{selectedAddress.recipient_name ?? 'Penerima'}</p>
                  <p className="mt-1 text-sm leading-relaxed text-slate-600">{selectedAddress.address}</p>
                </div>
              </div>
              {errors.address_id && <p className="text-xs text-rose-600">{errors.address_id}</p>}
              <div className="flex flex-wrap gap-3">
                <button type="button" onClick={() => setConfirmedAddress(true)} className="admin-btn-primary text-sm">Ya, ini alamat</button>
                <button type="button" onClick={resetToNewAddress} className="admin-btn-secondary text-sm">Tidak, masukkan alamat baharu</button>
              </div>
            </div>
          )}

          {showNewAddressForm && (
            <form onSubmit={handleSubmit} className="admin-flat-card space-y-5 p-6">
              <div className="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                  <UserPlus className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="font-bold text-slate-900">Daftar Customer Baharu</h3>
                  <p className="text-xs text-slate-500">Isi maklumat seperti borang daftar di laman depan.</p>
                </div>
              </div>

              <div>
                <label htmlFor="recipient_name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Penerima</label>
                <input
                  id="recipient_name"
                  type="text"
                  value={data.recipient_name}
                  onChange={(event) => setData('recipient_name', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.recipient_name && <p className="mt-1 text-xs text-rose-600">{errors.recipient_name}</p>}
              </div>

              {emailField}

              <div>
                <label htmlFor="address" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat Penghantaran</label>
                <textarea
                  id="address"
                  rows={4}
                  value={data.address}
                  onChange={(event) => setData('address', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.address && <p className="mt-1 text-xs text-rose-600">{errors.address}</p>}
              </div>

              {defaultPasswordNotice}
              <button type="submit" disabled={processing} className="admin-btn-primary text-sm">
                <Save className="h-4 w-4" />
                {processing ? 'Mendaftarkan...' : 'Daftar Customer'}
              </button>
            </form>
          )}

          {lookup && !lookup.account_exists && selectedAddress && confirmedAddress && data.mode === 'matched' && (
            <form onSubmit={handleSubmit} className="admin-flat-card space-y-5 border-emerald-200 p-6">
              <div className="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <MapPin className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                <div>
                  <p className="font-bold text-slate-900">Alamat disahkan</p>
                  <p className="mt-1 text-sm text-slate-600">{data.recipient_name} - {data.address}</p>
                </div>
              </div>
              {emailField}
              {defaultPasswordNotice}
              <button type="submit" disabled={processing} className="admin-btn-primary text-sm">
                <Save className="h-4 w-4" />
                {processing ? 'Mendaftarkan...' : 'Daftar Customer'}
              </button>
            </form>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
