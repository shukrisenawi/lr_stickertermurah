import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { BarChart3, Check, Copy, Link2, Mail, MapPin, Pencil, Phone, PhoneCall, Plus, Search, Trash2, UserRound, Wrench } from 'lucide-react';
import { useState } from 'react';
import { formatDate } from '@/lib/utils';

interface CustomerAddress {
  id: number;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  is_default: boolean;
  updated_at: string;
  user: {
    id: number;
    name: string;
    email: string | null;
    no_tel: string | null;
  } | null;
}

interface StateStatistic {
  state: string;
  count: number;
}

interface AddressStatistics {
  states: StateStatistic[];
  total_default_addresses: number;
  classified_addresses: number;
  unclassified_addresses: number;
}

interface AddressPage {
  data: CustomerAddress[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface CustomerAddressesIndexProps {
  addresses: AddressPage | null;
  search: string;
  tab: AddressTab;
  statistics: AddressStatistics;
}

type AddressTab = 'members' | 'non-members' | 'statistics';

function paginationLabel(label: string): string {
  return label.replace(/&laquo;|&raquo;/g, '').trim();
}

function phoneForCopy(phone: string): string {
  let digits = phone.replace(/\D/g, '');

  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.startsWith('60')) {
    return digits.slice(2);
  }

  return digits.startsWith('0') ? digits.slice(1) : digits;
}

interface CopyableValueProps {
  label: string;
  value: string;
  copied: boolean;
  icon: React.ComponentType<{ className?: string }>;
  onCopy: () => void;
  className?: string;
}

function CopyableValue({ label, value, copied, icon: Icon, onCopy, className = '' }: CopyableValueProps) {
  return (
    <button
      type="button"
      onClick={onCopy}
      className={`group inline-flex max-w-full items-start gap-1.5 text-left transition hover:text-brand-700 ${className}`}
      title={`Klik untuk salin ${label.toLowerCase()} dalam huruf besar`}
      aria-label={`Salin ${label.toLowerCase()} dalam huruf besar`}
    >
      <Icon className="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:text-brand-600" />
      <span className="min-w-0 break-words">{value}</span>
      {copied ? (
        <Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" aria-label="Berjaya disalin" />
      ) : (
        <Copy className="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-300 transition group-hover:text-brand-600" aria-hidden="true" />
      )}
    </button>
  );
}

function StateStatisticsChart({ statistics }: { statistics: AddressStatistics }) {
  const maxCount = Math.max(...statistics.states.map((item) => item.count), 1);
  const summary = [
    { label: 'Alamat Default', value: statistics.total_default_addresses, copy: 'Jumlah alamat yang ditetapkan sebagai default' },
    { label: 'Alamat Ada Negeri', value: statistics.classified_addresses, copy: 'Alamat default dengan negeri yang sah' },
    { label: 'Tidak Dikenalpasti', value: statistics.unclassified_addresses, copy: 'Alamat default tanpa negeri yang dapat dikesan' },
  ];

  return (
    <div className="space-y-5">
      <div className="grid gap-3 sm:grid-cols-3">
        {summary.map((item) => (
          <div key={item.label} className="admin-kpi-card">
            <p className="admin-kpi-value">{item.value}</p>
            <p className="text-xs font-semibold text-slate-700">{item.label}</p>
            <p className="mt-1 text-[11px] leading-4 text-slate-500">{item.copy}</p>
          </div>
        ))}
      </div>

      <div className="admin-flat-card overflow-hidden">
        <div className="admin-card-header">
          <div className="flex items-center gap-2.5">
            <div className="admin-icon-badge">
              <BarChart3 className="h-4 w-4" />
            </div>
            <div>
              <h3 className="text-base font-bold text-slate-900">Alamat Default Mengikut Negeri</h3>
              <p className="text-xs text-slate-500">Bilangan alamat default pelanggan yang dikelompokkan berdasarkan negeri</p>
            </div>
          </div>
          <span className="hidden rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-brand-700 sm:inline-flex">
            Default Sahaja
          </span>
        </div>

        {statistics.states.length === 0 ? (
          <div className="px-5 py-16 text-center sm:px-6">
            <BarChart3 className="mx-auto h-12 w-12 text-slate-300" />
            <p className="mt-3 text-sm font-semibold text-slate-700">Belum ada data negeri</p>
            <p className="mt-1 text-xs text-slate-500">Tiada alamat default dengan negeri yang dapat dikenalpasti.</p>
          </div>
        ) : (
          <div className="space-y-4 p-5 sm:p-6" role="img" aria-label="Graf bilangan alamat default mengikut negeri">
            <div className="grid grid-cols-[minmax(7rem,0.55fr)_minmax(0,1fr)_3rem] items-center gap-3 border-b border-slate-100 pb-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
              <span>Negeri</span>
              <span>Graf</span>
              <span className="text-right">Jumlah</span>
            </div>
            {statistics.states.map((item) => (
              <div key={item.state} className="grid grid-cols-[minmax(7rem,0.55fr)_minmax(0,1fr)_3rem] items-center gap-3">
                <span className="min-w-0 break-words text-sm font-semibold text-slate-700">{item.state}</span>
                <div
                  className="h-3 overflow-hidden rounded-full bg-slate-100"
                  role="progressbar"
                  aria-label={`${item.state}: ${item.count} alamat default`}
                  aria-valuemin={0}
                  aria-valuemax={maxCount}
                  aria-valuenow={item.count}
                >
                  <div
                    className="h-full rounded-full bg-gradient-to-r from-brand-700 to-brand-400 transition-all"
                    style={{ width: `${Math.max((item.count / maxCount) * 100, 4)}%` }}
                  />
                </div>
                <span className="text-right text-sm font-bold text-slate-900">{item.count}</span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export default function CustomerAddressesIndex({ addresses, search, tab, statistics }: CustomerAddressesIndexProps) {
  const addressPage: AddressPage = addresses ?? { data: [], links: [] };
  const { data, setData, get, delete: destroy } = useForm({ q: search });
  const [searching, setSearching] = useState(false);
  const [copiedField, setCopiedField] = useState<string | null>(null);
  const [repairingAddresses, setRepairingAddresses] = useState(false);
  const [repairingPhones, setRepairingPhones] = useState(false);
  const [linkingPhones, setLinkingPhones] = useState(false);
  const tabs: Array<{ key: AddressTab; label: string }> = [
    { key: 'members', label: 'Ahli' },
    { key: 'non-members', label: 'Bukan Ahli' },
    { key: 'statistics', label: 'Statistik' },
  ];
  const tabLabels: Record<AddressTab, string> = {
    members: 'Ahli',
    'non-members': 'Bukan Ahli',
    statistics: 'Statistik',
  };

  const copyText = async (value: string, key: string) => {
    const uppercaseValue = value.toLocaleUpperCase('ms-MY');

    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(uppercaseValue);
      } else {
        throw new Error('Clipboard API tidak tersedia.');
      }
    } catch {
      const fallback = document.createElement('textarea');
      fallback.value = uppercaseValue;
      fallback.setAttribute('readonly', '');
      fallback.style.position = 'fixed';
      fallback.style.opacity = '0';
      document.body.appendChild(fallback);
      fallback.select();
      document.execCommand('copy');
      fallback.remove();
    }

    setCopiedField(key);
    window.setTimeout(() => {
      setCopiedField((current) => current === key ? null : current);
    }, 1400);
  };

  const handleSearch = (event: React.FormEvent) => {
    event.preventDefault();
    setSearching(true);
    get(route('admin.customer-addresses.index', { tab }), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  const repairAddresses = () => {
    if (!window.confirm('Tukar semua alamat customer kepada format Ucwords dan buang maklumat selepas negeri yang diikuti poskod?')) return;

    setRepairingAddresses(true);
    router.post(route('admin.customer-addresses.repair-addresses'), {}, {
      preserveScroll: true,
      onFinish: () => setRepairingAddresses(false),
    });
  };

  const repairPhones = () => {
    if (!window.confirm('Format semua no. telefon customer kepada format tempatan?')) return;

    setRepairingPhones(true);
    router.post(route('admin.customer-addresses.repair-phones'), {}, {
      preserveScroll: true,
      onFinish: () => setRepairingPhones(false),
    });
  };

  const linkPhones = () => {
    if (!window.confirm('Pautkan semua address yang belum link berdasarkan no. telefon dalam Google Contacts? User baharu akan menggunakan password sementara 123.')) return;

    setLinkingPhones(true);
    router.post(route('admin.customer-addresses.link-by-phone'), {}, {
      preserveScroll: true,
      onFinish: () => setLinkingPhones(false),
    });
  };

  return (
    <AdminLayout>
      <Head title={`Customer Address - ${tabLabels[tab]}`} />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Customer Address</h2>
            <p className="admin-page-copy">
              {tab === 'statistics'
                ? 'Ringkasan alamat default pelanggan mengikut negeri.'
                : 'Senarai alamat penghantaran pelanggan. Klik data untuk salin dalam HURUF BESAR.'}
            </p>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            <button
              type="button"
              onClick={linkPhones}
              disabled={repairingAddresses || repairingPhones || linkingPhones}
              className="admin-btn-secondary text-sm disabled:cursor-not-allowed disabled:opacity-60"
            >
              <Link2 className="h-4 w-4" />
              {linkingPhones ? 'Linking...' : 'Link No HP'}
            </button>
            <button
              type="button"
              onClick={repairAddresses}
              disabled={repairingAddresses || repairingPhones || linkingPhones}
              className="admin-btn-secondary text-sm disabled:cursor-not-allowed disabled:opacity-60"
            >
              <Wrench className="h-4 w-4" />
              {repairingAddresses ? 'Membaiki...' : 'Repair Alamat'}
            </button>
            <button
              type="button"
              onClick={repairPhones}
              disabled={repairingAddresses || repairingPhones || linkingPhones}
              className="admin-btn-secondary text-sm disabled:cursor-not-allowed disabled:opacity-60"
            >
              <PhoneCall className="h-4 w-4" />
              {repairingPhones ? 'Membaiki...' : 'Repair No Telefon'}
            </button>
            <Link href={route('admin.customer-addresses.create', { tab: tab === 'statistics' ? 'members' : tab })} className="admin-btn-primary text-sm">
              <Plus className="h-4 w-4" />
              Tambah Address
            </Link>
          </div>
        </div>

        <div className="admin-toolbar-card">
          <div className="flex flex-wrap items-center gap-1 rounded-xl bg-slate-100 p-1">
            {tabs.map((item) => (
              <Link
                key={item.key}
                href={route('admin.customer-addresses.index', { tab: item.key, q: item.key === 'statistics' ? undefined : data.q || undefined })}
                preserveState
                preserveScroll
                className={`rounded-lg px-4 py-2 text-sm font-semibold transition ${tab === item.key ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
              >
                {item.label}
              </Link>
            ))}
          </div>

          {tab !== 'statistics' && (
            <form onSubmit={handleSearch} className="flex flex-1 items-center gap-3">
              <div className="relative flex-1 max-w-md">
                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  type="search"
                  value={data.q}
                  onChange={(event) => setData('q', event.target.value)}
                  placeholder="Cari nama, telefon, atau alamat..."
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>
              <button type="submit" disabled={searching} className="admin-btn-primary text-sm">
                {searching ? 'Mencari...' : 'Cari'}
              </button>
            </form>
          )}
        </div>

        {tab === 'statistics' ? (
          <StateStatisticsChart statistics={statistics} />
        ) : (
          <div className="admin-table-card">
            <div className="admin-table-wrap">
              <table className="admin-table">
              <thead>
                <tr>
                  {tab === 'members' && <th>Customer</th>}
                  <th>Penerima</th>
                  <th>Telefon</th>
                  <th>Alamat</th>
                  <th>Status</th>
                  <th>Dikemaskini</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {addressPage.data.length === 0 ? (
                  <tr>
                    <td colSpan={tab === 'members' ? 7 : 6} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <MapPin className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">
                          Tiada alamat {tab === 'members' ? 'ahli' : 'bukan ahli'}
                        </p>
                        <p className="admin-table-empty-copy">Belum ada alamat yang sepadan dengan carian.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  addressPage.data.map((address) => (
                    <tr key={address.id}>
                      {tab === 'members' && (
                        <td>
                          {address.user ? (
                            <div>
                              <CopyableValue
                                label="nama customer"
                                value={address.user.name}
                                icon={UserRound}
                                copied={copiedField === `address-${address.id}-customer-name`}
                                onCopy={() => copyText(address.user?.name ?? '', `address-${address.id}-customer-name`)}
                                className="font-medium text-slate-900"
                              />
                              {address.user.email && (
                                <div className="mt-1">
                                  <CopyableValue
                                    label="email customer"
                                    value={address.user.email}
                                    icon={Mail}
                                    copied={copiedField === `address-${address.id}-customer-email`}
                                    onCopy={() => copyText(address.user?.email ?? '', `address-${address.id}-customer-email`)}
                                    className="text-xs text-slate-500"
                                  />
                                </div>
                              )}
                            </div>
                          ) : (
                            <span className="text-slate-400">Tidak dipautkan</span>
                          )}
                        </td>
                      )}
                      <td className="font-medium text-slate-900">
                        <CopyableValue
                          label="penerima"
                          value={address.recipient_name || '-'}
                          icon={UserRound}
                          copied={copiedField === `address-${address.id}-recipient`}
                          onCopy={() => copyText(address.recipient_name || '-', `address-${address.id}-recipient`)}
                        />
                      </td>
                      <td>
                        {address.no_hp ? (
                          <CopyableValue
                            label="telefon tanpa awalan 0/60"
                            value={address.no_hp}
                            icon={Phone}
                            copied={copiedField === `address-${address.id}-phone`}
                            onCopy={() => copyText(phoneForCopy(address.no_hp ?? ''), `address-${address.id}-phone`)}
                            className="whitespace-nowrap"
                          />
                        ) : (
                          <span className="text-slate-400">-</span>
                        )}
                      </td>
                      <td className="max-w-[280px] text-slate-600">
                        <CopyableValue
                          label="alamat"
                          value={address.address}
                          icon={MapPin}
                          copied={copiedField === `address-${address.id}-address`}
                          onCopy={() => copyText(address.address, `address-${address.id}-address`)}
                          className="line-clamp-2"
                        />
                      </td>
                      <td>
                        {address.is_default ? (
                          <span className="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                            Default
                          </span>
                        ) : (
                          <span className="text-slate-400">-</span>
                        )}
                      </td>
                      <td className="whitespace-nowrap text-slate-500">{formatDate(address.updated_at)}</td>
                      <td>
                        <div className="flex items-center justify-end gap-2">
                          <Link
                            href={route('admin.customer-addresses.edit', { customerAddress: address.id, tab })}
                            className="admin-btn-secondary text-xs"
                          >
                            <Pencil className="h-3 w-3" />
                            Edit
                          </Link>
                          <button
                            type="button"
                            onClick={() => {
                              if (confirm('Adakah anda pasti mahu memadam customer address ini?')) {
                                destroy(route('admin.customer-addresses.destroy', address.id), { preserveScroll: true });
                              }
                            }}
                            className="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white p-2.5 text-rose-600 transition hover:bg-rose-50"
                            aria-label="Padam customer address"
                            title="Padam"
                          >
                            <Trash2 className="h-3.5 w-3.5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
              </table>
            </div>

            {addressPage.links.length > 3 && (
              <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                <div className="flex items-center gap-2">
                  {addressPage.links.map((link) => (
                    link.url ? (
                      <Link
                        key={`${link.label}-${link.url}`}
                        href={link.url}
                        className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}
                      >
                        {paginationLabel(link.label)}
                      </Link>
                    ) : (
                      <span
                        key={`${link.label}-disabled`}
                        className="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                      >
                        {paginationLabel(link.label)}
                      </span>
                    )
                  ))}
                </div>
              </div>
            )}
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
