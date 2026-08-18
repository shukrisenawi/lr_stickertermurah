import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, UserPlus } from 'lucide-react';

export default function Create() {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    email: '',
    no_tel: '',
    address: '',
    password: '',
    password_confirmation: '',
  });

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    post(route('admin.customers.store'));
  };

  return (
    <AdminLayout>
      <Head title="Tambah Customer" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Tambah Customer</h2>
            <p className="admin-page-copy">Daftarkan akaun customer baharu seperti borang pendaftaran ahli.</p>
          </div>
          <Link href={route('admin.customers.index')} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="max-w-3xl space-y-6">
          <div className="admin-flat-card space-y-5 p-6">
            <div className="flex items-center gap-3 border-b border-slate-100 pb-4">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <UserPlus className="h-5 w-5" />
              </div>
              <div>
                <h3 className="font-bold text-slate-900">Maklumat Akaun</h3>
                <p className="text-xs text-slate-500">Customer boleh login menggunakan telefon atau email.</p>
              </div>
            </div>

            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
              <div>
                <label htmlFor="name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Customer</label>
                <input
                  id="name"
                  type="text"
                  value={data.name}
                  onChange={(event) => setData('name', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
              </div>

              <div>
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
            </div>

            <div>
              <label htmlFor="email" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Email <span className="font-normal normal-case tracking-normal text-slate-400">(pilihan)</span></label>
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
          </div>

          <div className="admin-flat-card space-y-5 p-6">
            <div>
              <h3 className="font-bold text-slate-900">Kata Laluan</h3>
              <p className="mt-1 text-xs text-slate-500">Minimum 8 aksara. Berikan kata laluan ini kepada customer untuk login.</p>
            </div>

            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
              <div>
                <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kata Laluan</label>
                <input
                  id="password"
                  type="password"
                  autoComplete="new-password"
                  minLength={8}
                  value={data.password}
                  onChange={(event) => setData('password', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.password && <p className="mt-1 text-xs text-rose-600">{errors.password}</p>}
              </div>

              <div>
                <label htmlFor="password_confirmation" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Sahkan Kata Laluan</label>
                <input
                  id="password_confirmation"
                  type="password"
                  autoComplete="new-password"
                  value={data.password_confirmation}
                  onChange={(event) => setData('password_confirmation', event.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.password_confirmation && <p className="mt-1 text-xs text-rose-600">{errors.password_confirmation}</p>}
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <button type="submit" disabled={processing} className="admin-btn-primary text-sm">
              <Save className="h-4 w-4" />
              {processing ? 'Mendaftarkan...' : 'Daftar Customer'}
            </button>
            <Link href={route('admin.customers.index')} className="admin-btn-secondary text-sm">Batal</Link>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
