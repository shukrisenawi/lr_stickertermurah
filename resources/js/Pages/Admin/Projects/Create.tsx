import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, UploadCloud, X } from 'lucide-react';
import { useState } from 'react';

interface Customer { id: number; name: string; email: string | null }
interface Order { id: number; user_id: number | null; order_no: string; customer_name: string }
interface ProjectCreateProps { customers: Customer[]; orders: Order[]; initialUserId: number | null }

export default function ProjectCreate({ customers, orders, initialUserId }: ProjectCreateProps) {
  const initialCustomer = customers.find((customer) => customer.id === initialUserId) ?? null;
  const { data, setData, post, processing, errors } = useForm<{
    user_id: string; order_id: string; title: string; notes: string; files: File[];
  }>({ user_id: initialCustomer ? String(initialCustomer.id) : '', order_id: '', title: '', notes: '', files: [] });

  const customerOrders = orders.filter((order) => String(order.user_id) === data.user_id);
  const [customerSearch, setCustomerSearch] = useState(initialCustomer ? `${initialCustomer.name} (${initialCustomer.email ?? 'Tiada email'})` : '');
  const [customerOpen, setCustomerOpen] = useState(false);
  const filteredCustomers = customers.filter((customer) => `${customer.name} ${customer.email ?? ''}`.toLowerCase().includes(customerSearch.toLowerCase()));

  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    post(route('admin.projects.store'), { forceFormData: true });
  };

  return (
    <AdminLayout>
      <Head title="Tambah Project Customer" />
      <div className="mx-auto max-w-3xl space-y-6">
        <Link href={route('admin.projects.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600">
          <ArrowLeft className="h-4 w-4" /> Kembali ke Projects
        </Link>
        <div className="admin-page-head">
          <div><h2 className="text-2xl font-bold text-slate-900">Tambah Project Customer</h2><p className="admin-page-copy">Simpan preview untuk customer dan source kerja untuk kegunaan admin.</p></div>
        </div>
        <form onSubmit={submit} className="admin-flat-card space-y-5 p-6" encType="multipart/form-data">
          <div className="grid gap-5 sm:grid-cols-2">
            <div>
              <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="user_id">Customer</label>
              <div className="relative">
                <input
                  id="user_id"
                  type="search"
                  value={customerSearch}
                  onFocus={() => setCustomerOpen(true)}
                  onBlur={() => setCustomerOpen(false)}
                  onChange={(e) => {
                    setCustomerSearch(e.target.value);
                    setCustomerOpen(true);
                    setData('user_id', '');
                    setData('order_id', '');
                  }}
                  placeholder="Cari nama atau email customer..."
                  autoComplete="off"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
                {customerOpen && (
                  <div className="absolute left-0 right-0 top-full z-20 mt-1 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                    {filteredCustomers.length > 0 ? filteredCustomers.map((customer) => (
                      <button
                        key={customer.id}
                        type="button"
                        onMouseDown={(event) => event.preventDefault()}
                        onClick={() => {
                          setData('user_id', String(customer.id));
                          setData('order_id', '');
                           setCustomerSearch(`${customer.name} (${customer.email ?? 'Tiada email'})`);
                          setCustomerOpen(false);
                        }}
                        className="flex w-full cursor-pointer flex-col px-4 py-2 text-left transition hover:bg-brand-50"
                      >
                        <span className="text-sm font-medium text-slate-900">{customer.name}</span>
                        <span className="text-xs text-slate-500">{customer.email ?? 'Tiada email'}</span>
                      </button>
                    )) : <p className="px-4 py-3 text-sm text-slate-500">Customer tidak dijumpai.</p>}
                  </div>
                )}
              </div>
              {errors.user_id && <p className="mt-1 text-xs text-rose-600">{errors.user_id}</p>}
            </div>
            <div>
              <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="order_id">Order asal (pilihan)</label>
              <select id="order_id" value={data.order_id} onChange={(e) => setData('order_id', e.target.value)} disabled={!data.user_id} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 disabled:bg-slate-50">
                <option value="">Tiada order dipilih</option>
                {customerOrders.map((order) => <option key={order.id} value={order.id}>{order.order_no} - {order.customer_name}</option>)}
              </select>
              {errors.order_id && <p className="mt-1 text-xs text-rose-600">{errors.order_id}</p>}
            </div>
          </div>
          <div><label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="title">Nama design / project</label><input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" placeholder="Contoh: Logo Kedai Ali" />{errors.title && <p className="mt-1 text-xs text-rose-600">{errors.title}</p>}</div>
          <div><label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="notes">Nota (pilihan)</label><textarea id="notes" value={data.notes} onChange={(e) => setData('notes', e.target.value)} className="w-full min-h-24 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" placeholder="Catatan saiz, material atau kemasan..." /></div>
          <div className="grid gap-5 sm:grid-cols-2">
          <div>
            <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="files">Fail Project</label>
            <input id="files" name="files[]" type="file" multiple accept="image/jpeg,image/png,image/webp,.zip,.rar,.7z,.ai,.psd,.eps,.pdf,.svg" onChange={(e) => setData('files', [...data.files, ...Array.from(e.target.files ?? [])].slice(0, 20))} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900" />
            <p className="mt-1 text-xs text-slate-500">Boleh pilih sehingga 20 gambar atau fail lain, setiap satu maksimum 50MB. Gambar akan dijana preview watermark untuk ahli.</p>
            {data.files.length > 0 && <div className="mt-2 space-y-1">{data.files.map((file, index) => <div key={`${file.name}-${file.size}-${file.lastModified}`} className="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-1.5 text-xs text-slate-600"><span className="truncate">{file.name}</span><button type="button" onClick={() => setData('files', data.files.filter((_, fileIndex) => fileIndex !== index))} className="shrink-0 cursor-pointer text-slate-400 hover:text-rose-600" aria-label={`Buang ${file.name}`}><X className="h-3.5 w-3.5" /></button></div>)}</div>}
            {errors.files && <p className="mt-1 text-xs text-rose-600">{errors.files}</p>}
          </div>
          </div>
          <div className="flex justify-end border-t border-slate-100 pt-5"><button type="submit" disabled={processing} className="admin-btn-primary"><UploadCloud className="h-4 w-4" />{processing ? 'Menyimpan...' : 'Simpan Project'}</button></div>
        </form>
      </div>
    </AdminLayout>
  );
}
