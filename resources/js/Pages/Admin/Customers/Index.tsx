import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ContactRound, Search, Users, ShoppingBag, MapPin, Pencil, LogIn, Receipt, Trash2, Plus, KeyRound, MessageCircle } from 'lucide-react';
import { useState } from 'react';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';

interface Customer {
  id: number;
  name: string;
  no_tel: string | null;
  email: string;
  orders_count: number;
  orders_sum_total: number | null;
  default_customer_address: { address: string; no_hp: string } | null;
  latest_order: { order_no: string } | null;
}

interface CustomersIndexProps {
  customers: {
    data: Customer[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  search: string;
  totalCustomers: number;
  customersWithOrders: number;
  customersWithAddresses: number;
  createdCustomer: { id: number; name: string } | null;
}

export default function CustomersIndex({ customers, search, totalCustomers, customersWithOrders, customersWithAddresses, createdCustomer }: CustomersIndexProps) {
  const { data, setData, get, delete: destroy } = useForm({ q: search });
  const addContactForm = useForm({ customer_id: createdCustomer?.id ?? 0, address_id: '' });
  const [searching, setSearching] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearching(true);
    get(route('admin.customers.index'), {
      preserveState: true,
      onFinish: () => setSearching(false),
    });
  };

  const handleDelete = (id: number, name: string) => {
    if (confirm(`Adakah anda pasti mahu memadam pelanggan ${name}?`)) {
      destroy(route('admin.customers.destroy', id));
    }
  };

  const handleAddContact = () => {
    if (!createdCustomer) return;

    addContactForm.post(route('admin.contacts.google.customer.store'), {
      preserveScroll: true,
    });
  };

  const formatCurrency = (amount: number | null) => {
    if (!amount) return 'RM 0.00';
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  return (
    <AdminLayout>
      <Head title="Senarai Pelanggan" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Pelanggan</h2>
            <p className="admin-page-copy">Urus maklumat pelanggan berdaftar.</p>
          </div>
          <Link href={route('admin.customers.create')} className="admin-btn-primary text-sm">
            <Plus className="h-4 w-4" />
            Tambah Customer
          </Link>
        </div>

        {createdCustomer && (
          <div className="flex flex-col gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-start gap-3">
              <ContactRound className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
              <div>
                <p className="font-bold">Customer berjaya dicipta</p>
                <p className="mt-1 text-sm">{createdCustomer.name} boleh terus ditambah ke Google Contacts.</p>
              </div>
            </div>
            <button type="button" onClick={handleAddContact} disabled={addContactForm.processing} className="admin-btn-primary shrink-0 text-sm disabled:cursor-not-allowed disabled:opacity-60">
              <ContactRound className="h-4 w-4" />
              {addContactForm.processing ? 'Menambah...' : 'Add Contact'}
            </button>
          </div>
        )}

        {/* KPI Cards */}
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="admin-kpi-label">Jumlah Pelanggan</p>
                <p className="admin-kpi-value">{totalCustomers}</p>
              </div>
              <div className="admin-kpi-icon bg-blue-500">
                <Users className="h-6 w-6" />
              </div>
            </div>
          </div>
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="admin-kpi-label">Dengan Order</p>
                <p className="admin-kpi-value">{customersWithOrders}</p>
              </div>
              <div className="admin-kpi-icon bg-emerald-500">
                <ShoppingBag className="h-6 w-6" />
              </div>
            </div>
          </div>
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between">
              <div>
                <p className="admin-kpi-label">Dengan Alamat</p>
                <p className="admin-kpi-value">{customersWithAddresses}</p>
              </div>
              <div className="admin-kpi-icon bg-amber-500">
                <MapPin className="h-6 w-6" />
              </div>
            </div>
          </div>
        </div>

        {/* Search */}
        <div className="admin-toolbar-card">
          <form onSubmit={handleSearch} className="flex flex-1 items-center gap-3">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={data.q}
                onChange={(e) => setData('q', e.target.value)}
                placeholder="Cari nama, email, telefon..."
                className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
            </div>
            <button type="submit" disabled={searching} className="admin-btn-primary text-sm">
              {searching ? 'Mencari...' : 'Cari'}
            </button>
          </form>
        </div>

        {/* Table */}
        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>Order</th>
                  <th>Jumlah Belanja</th>
                  <th>Alamat</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {customers.data.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Users className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Pelanggan</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                   customers.data.map((customer) => {
                     const customerPhone = customer.no_tel ?? customer.default_customer_address?.no_hp ?? '';
                     const whatsappLink = customerPhone
                       ? whatsappWebUrl(customerPhone, `Assalamualaikum ${customer.name}, saya dari StickerTermurah. Ada apa-apa yang boleh kami bantu?`)
                       : null;

                     return (
                     <tr key={customer.id}>
                      <td className="font-medium text-slate-900">{customer.name}</td>
                      <td>{customer.email}</td>
                      <td>{customer.orders_count}</td>
                      <td className="font-medium">{formatCurrency(customer.orders_sum_total)}</td>
                      <td className="text-slate-500 max-w-[200px] truncate">
                        {customer.default_customer_address?.address || '-'}
                      </td>
                      <td>
                         <div className="flex items-center justify-end gap-1.5">
                           {whatsappLink && (
                             <a
                               href={whatsappLink}
                               target={WHATSAPP_TARGET}
                               aria-label={`WhatsApp ${customer.name}`}
                               title={`WhatsApp ${customer.name}`}
                               className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                             >
                               <MessageCircle className="h-4 w-4" />
                             </a>
                           )}
                           <Link
                             href={route('admin.invoices.manual.create', { user_id: customer.id })}
                             aria-label={`Buat invoice untuk ${customer.name}`}
                             title={`Buat invoice untuk ${customer.name}`}
                             className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-brand-600"
                           >
                             <Receipt className="h-4 w-4" />
                           </Link>
                           <Link
                             href={route('admin.customers.edit', customer.id)}
                             aria-label={`Edit ${customer.name}`}
                             title={`Edit ${customer.name}`}
                             className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-brand-600"
                           >
                             <Pencil className="h-4 w-4" />
                           </Link>
                           <Link
                             href={route('admin.customers.login-as', customer.id)}
                             method="post"
                             as="button"
                             type="button"
                             aria-label={`Login sebagai ${customer.name}`}
                             title={`Login sebagai ${customer.name}`}
                             className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-brand-600 bg-brand-600 text-white transition hover:bg-brand-700"
                           >
                             <LogIn className="h-4 w-4" />
                           </Link>
                           <Link
                             href={route('admin.customers.reset-password', customer.id)}
                             method="post"
                             as="button"
                             type="button"
                             onBefore={() => confirm(`Tetapkan semula kata laluan ${customer.name} kepada 123? Customer wajib menukar kata laluan selepas login.`)}
                             preserveScroll
                             aria-label={`Reset kata laluan ${customer.name}`}
                             title={`Reset kata laluan ${customer.name}`}
                             className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-brand-600"
                           >
                             <KeyRound className="h-4 w-4" />
                           </Link>
                           <button
                             type="button"
                             onClick={() => handleDelete(customer.id, customer.name)}
                             aria-label={`Padam pelanggan ${customer.name}`}
                             title={`Padam pelanggan ${customer.name}`}
                             className="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-rose-600 transition hover:bg-rose-50"
                           >
                             <Trash2 className="h-4 w-4" />
                           </button>
                         </div>
                       </td>
                     </tr>
                     );
                   })
                )}
              </tbody>
            </table>
          </div>

          {/* Pagination */}
          {customers.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {customers.links.map((link, i) => (
                  link.url ? (
                    <Link
                      key={i}
                      href={link.url}
                      className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}
                      dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                  ) : (
                    <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
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
