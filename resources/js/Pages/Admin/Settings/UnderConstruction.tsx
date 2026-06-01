import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Eye } from 'lucide-react';

interface UnderConstructionProps {
  isEnabled: boolean;
}

export default function UnderConstructionSettings({ isEnabled }: UnderConstructionProps) {
  const { data, setData, put, processing } = useForm({ is_enabled: isEnabled });

  const handleToggle = () => {
    setData('is_enabled', !data.is_enabled);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.settings.under-construction.update'));
  };

  return (
    <AdminLayout>
      <Head title="Under Construction" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.dashboard')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Dashboard
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Mod Under Construction</h2>
          <p className="admin-page-copy">
            Aktifkan mod ini untuk menyembunyikan laman utama daripada pengguna awam.
            Hanya pentadbir yang telah login dapat mengakses laman web.
          </p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-6">
          <div className="flex items-center justify-between">
            <div className="flex items-start gap-4">
              <div className={`rounded-xl p-3 transition ${data.is_enabled ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'}`}>
                <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
              </div>
              <div>
                <p className="text-sm font-semibold text-slate-900">Under Construction</p>
                <p className="text-xs text-slate-500 mt-0.5">
                  {data.is_enabled
                    ? 'Laman web kini dalam mod pembinaan. Pengguna awam tidak boleh access.'
                    : 'Laman web berfungsi seperti biasa untuk semua pengguna.'}
                </p>
              </div>
            </div>
            <button
              type="button"
              role="switch"
              aria-checked={data.is_enabled}
              onClick={handleToggle}
              className={`relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 ${
                data.is_enabled ? 'bg-brand-600' : 'bg-slate-300'
              }`}
            >
              <span
                className={`pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ${
                  data.is_enabled ? 'translate-x-5' : 'translate-x-0'
                }`}
              />
            </button>
          </div>

          <div className="flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-3 text-xs text-slate-600">
            {data.is_enabled ? (
              <>
                <Eye className="h-3.5 w-3.5 shrink-0 text-amber-500" />
                <span>Pengguna awam akan melihat halaman "Under Construction". Login admin diperlukan untuk akses penuh.</span>
              </>
            ) : (
              <>
                <Eye className="h-3.5 w-3.5 shrink-0 text-emerald-500" />
                <span>Semua pengguna boleh mengakses laman web seperti biasa.</span>
              </>
            )}
          </div>

          <div className="pt-2">
            <button type="submit" disabled={processing} className="admin-btn-primary text-sm">
              {processing ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
