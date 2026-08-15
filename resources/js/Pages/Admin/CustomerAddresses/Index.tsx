import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Mail, MapPin, Pencil, Phone, Search, UserRound } from 'lucide-react';
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

interface CustomerAddressesIndexProps {
  addresses: {
    data: CustomerAddress[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  search: string;
}

function paginationLabel(label: string): string {
  return label.replace(/&laquo;|&raquo;/g, '').trim();
}

export default function CustomerAddressesIndex({ addresses, search }: CustomerAddressesIndexProps) {
  const { data, setData, get } = useForm({ q: search });
  const [searching, setSearching] = useState(false);

  const handleSearch = (event: React.FormEvent) => {
    event.preventDefault();
    setSearching(true);
    get(route('admin.customer-addresses.index'), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  return (
    <AdminLayout>
      <Head title="Customer Address" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Customer Address</h2>
            <p className="admin-page-copy">Senarai alamat penghantaran pelanggan.</p>
          </div>
        </div>

        <div className="admin-toolbar-card">
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
        </div>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Penerima</th>
                  <th>Telefon</th>
                  <th>Alamat</th>
                  <th>Status</th>
                  <th>Dikemaskini</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {addresses.data.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <MapPin className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada alamat customer</p>
                        <p className="admin-table-empty-copy">Belum ada alamat yang sepadan dengan carian.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  addresses.data.map((address) => (
                    <tr key={address.id}>
                      <td>
                        {address.user ? (
                          <div>
                            <p className="font-medium text-slate-900">{address.user.name}</p>
                            {address.user.email && (
                              <p className="mt-1 flex items-center gap-1 text-xs text-slate-500">
                                <Mail className="h-3 w-3" />
                                {address.user.email}
                              </p>
                            )}
                          </div>
                        ) : (
                          <span className="text-slate-400">Tidak dipautkan</span>
                        )}
                      </td>
                      <td className="font-medium text-slate-900">
                        <div className="flex items-center gap-1.5">
                          <UserRound className="h-3.5 w-3.5 text-slate-400" />
                          {address.recipient_name || '-'}
                        </div>
                      </td>
                      <td>
                        {address.no_hp ? (
                          <span className="flex items-center gap-1.5 whitespace-nowrap">
                            <Phone className="h-3.5 w-3.5 text-slate-400" />
                            {address.no_hp}
                          </span>
                        ) : (
                          <span className="text-slate-400">-</span>
                        )}
                      </td>
                      <td className="max-w-[280px] text-slate-600">
                        <div className="flex items-start gap-1.5">
                          <MapPin className="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" />
                          <span className="line-clamp-2">{address.address}</span>
                        </div>
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
                        {address.user && (
                          <Link
                            href={route('admin.customers.edit', address.user.id)}
                            className="admin-btn-secondary text-xs"
                          >
                            <Pencil className="h-3 w-3" />
                            Edit Customer
                          </Link>
                        )}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {addresses.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {addresses.links.map((link) => (
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
      </div>
    </AdminLayout>
  );
}
