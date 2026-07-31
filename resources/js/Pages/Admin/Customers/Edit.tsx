import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

interface CustomerAddress {
  id: number;
  address: string;
  no_hp: string;
}

interface Customer {
  id: number;
  name: string;
  email: string;
  customer_addresses: CustomerAddress[];
}

interface EditProps {
  customer: Customer;
}

export default function Edit({ customer }: EditProps) {
  const { data, setData, put, processing, errors } = useForm({
    name: customer.name,
    email: customer.email,
    phone: customer.customer_addresses[0]?.no_hp ?? '',
    address: customer.customer_addresses[0]?.address ?? '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.customers.update', customer.id));
  };

  return (
    <AdminLayout>
      <Head title="Kemaskini Pelanggan" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Kemaskini Pelanggan</h2>
            <p className="admin-page-copy">Ubah maklumat asas pelanggan.</p>
          </div>
          <Link href={route('admin.customers.index')} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="max-w-2xl space-y-6">
          <div className="admin-flat-card p-6 space-y-4">
            <div>
              <label htmlFor="name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Nama
              </label>
              <input
                id="name"
                type="text"
                value={data.name}
                onChange={(e) => setData('name', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                required
              />
              {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
            </div>

            <div>
              <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Email
              </label>
              <input
                id="email"
                type="email"
                value={data.email}
                onChange={(e) => setData('email', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                required
              />
              {errors.email && <p className="mt-1 text-xs text-rose-600">{errors.email}</p>}
            </div>

            <div>
              <label htmlFor="phone" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                No. Telefon
              </label>
              <input
                id="phone"
                type="text"
                value={data.phone}
                onChange={(e) => setData('phone', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
              {errors.phone && <p className="mt-1 text-xs text-rose-600">{errors.phone}</p>}
            </div>

            <div>
              <label htmlFor="address" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Alamat
              </label>
              <textarea
                id="address"
                value={data.address}
                onChange={(e) => setData('address', e.target.value)}
                rows={3}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              />
              {errors.address && <p className="mt-1 text-xs text-rose-600">{errors.address}</p>}
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button
              type="submit"
              disabled={processing}
              className="admin-btn-primary text-sm"
            >
              <Save className="h-4 w-4" />
              {processing ? 'Menyimpan...' : 'Simpan'}
            </button>
            <Link
              href={route('admin.customers.index')}
              className="admin-btn-secondary text-sm"
            >
              Batal
            </Link>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
