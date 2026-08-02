import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, UploadCloud } from 'lucide-react';
import { useState } from 'react';

interface Customer { id: number; name: string; email: string }
interface Order { id: number; user_id: number | null; order_no: string; customer_name: string }

export default function ProjectCreate({ customers, orders }: { customers: Customer[]; orders: Order[] }) {
  const { data, setData, post, processing, errors } = useForm<{
    user_id: string; order_id: string; title: string; notes: string; preview: File | null; source: File[];
  }>({ user_id: '', order_id: '', title: '', notes: '', preview: null, source: [] });

  const customerOrders = orders.filter((order) => String(order.user_id) === data.user_id);
  const [customerSearch, setCustomerSearch] = useState('');
  const filteredCustomers = customers.filter((customer) => `${customer.name} ${customer.email}`.toLowerCase().includes(customerSearch.toLowerCase()));

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
              <input type="search" value={customerSearch} onChange={(e) => setCustomerSearch(e.target.value)} placeholder="Cari nama atau email customer..." className="mb-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
              <select id="user_id" value={data.user_id} onChange={(e) => { setData('user_id', e.target.value); setData('order_id', ''); }} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                <option value="">Pilih customer</option>
                {filteredCustomers.map((customer) => <option key={customer.id} value={customer.id}>{customer.name} ({customer.email})</option>)}
              </select>
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
            <div><label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="preview">Gambar preview</label><input id="preview" type="file" accept="image/jpeg,image/png,image/webp" onChange={(e) => setData('preview', e.target.files?.[0] ?? null)} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900" /><p className="mt-1 text-xs text-slate-500">Akan dikecilkan kepada maksimum 250px dan watermark.</p>{errors.preview && <p className="mt-1 text-xs text-rose-600">{errors.preview}</p>}</div>
            <div><label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500" htmlFor="source">File source</label><input id="source" type="file" multiple accept=".zip,.rar,.7z,.ai,.psd,.eps,.pdf,.svg" onChange={(e) => setData('source', Array.from(e.target.files ?? []))} className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900" /><p className="mt-1 text-xs text-slate-500">Boleh pilih sehingga 10 fail, setiap satu maksimum 50MB.</p>{data.source.length > 0 && <p className="mt-1 text-xs font-medium text-brand-600">{data.source.length} fail dipilih</p>}{errors.source && <p className="mt-1 text-xs text-rose-600">{errors.source}</p>}</div>
          </div>
          <div className="flex justify-end border-t border-slate-100 pt-5"><button type="submit" disabled={processing} className="admin-btn-primary"><UploadCloud className="h-4 w-4" />{processing ? 'Menyimpan...' : 'Simpan Project'}</button></div>
        </form>
      </div>
    </AdminLayout>
  );
}
