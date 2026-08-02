import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { PageProps } from '@/types';

export default function ProfileEdit() {
  const { auth } = usePage<PageProps>().props;
  const user = auth.user!;

  const { data, setData, post, processing, errors } = useForm({
    name: user.name,
    email: user.email ?? '',
    avatar: null as File | null,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.profile.update'), {
      forceFormData: true,
    });
  };

  return (
    <AdminLayout>
      <Head title="Profil Admin" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Profil Admin</h2>
            <p className="admin-page-copy">Kemaskini maklumat profil anda.</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-6">
            <div className="admin-flat-card p-6 space-y-4">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                Maklumat Peribadi
              </h3>

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
                {errors.name && (
                  <p className="mt-1 text-xs text-rose-600">{errors.name}</p>
                )}
              </div>

              <div>
                <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Emel
                </label>
                <input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
                {errors.email && (
                  <p className="mt-1 text-xs text-rose-600">{errors.email}</p>
                )}
              </div>

              <div>
                <label htmlFor="avatar" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                  Avatar
                </label>
                <input
                  id="avatar"
                  type="file"
                  accept="image/*"
                  onChange={(e) => {
                    if (e.target.files?.[0]) {
                      setData('avatar', e.target.files[0]);
                    }
                  }}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
                {errors.avatar && (
                  <p className="mt-1 text-xs text-rose-600">{errors.avatar}</p>
                )}
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
                  {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
