import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

export default function PasswordEdit() {
  const { data, setData, put, processing, errors } = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.password.update'));
  };

  return (
    <AdminLayout>
      <Head title="Tukar Kata Laluan" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Tukar Kata Laluan</h2>
            <p className="admin-page-copy">Kemaskini kata laluan akaun admin anda.</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-6">
            <div className="admin-flat-card p-6 space-y-4">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                Kata Laluan
              </h3>

              <div>
                <label htmlFor="current_password" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Kata Laluan Semasa
                </label>
                <input
                  id="current_password"
                  type="password"
                  value={data.current_password}
                  onChange={(e) => setData('current_password', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.current_password && (
                  <p className="mt-1 text-xs text-rose-600">{errors.current_password}</p>
                )}
              </div>

              <div>
                <label htmlFor="password" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Kata Laluan Baharu
                </label>
                <input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.password && (
                  <p className="mt-1 text-xs text-rose-600">{errors.password}</p>
                )}
              </div>

              <div>
                <label htmlFor="password_confirmation" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Sahkan Kata Laluan
                </label>
                <input
                  id="password_confirmation"
                  type="password"
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
              </div>
            </div>
          </div>

          <div className="space-y-6">
            <div className="admin-flat-card p-6">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
                Tindakan
              </h3>
              <div className="space-y-3">
                <button
                  type="submit"
                  disabled={processing}
                  className="admin-btn-primary w-full text-sm"
                >
                  <Save className="h-4 w-4" />
                  {processing ? 'Menyimpan...' : 'Kemaskini Kata Laluan'}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
