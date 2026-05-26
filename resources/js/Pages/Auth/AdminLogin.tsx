import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, useForm } from '@inertiajs/react';
import { LogIn, Mail, Lock, AlertCircle } from 'lucide-react';

interface AdminLoginProps {
  defaultEmail: string;
  defaultPassword: string;
  errors: {
    email?: string;
  };
}

export default function AdminLogin({ defaultEmail, defaultPassword, errors: pageErrors }: AdminLoginProps) {
  const { data, setData, post, processing } = useForm({
    email: defaultEmail,
    password: defaultPassword,
    remember: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.login.attempt'));
  };

  return (
    <FrontendLayout>
      <Head title="Log Masuk Admin" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="mx-auto max-w-md">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-slate-900">Log Masuk Admin</h1>
            <p className="mt-2 text-sm text-slate-500">Akses panel pentadbiran StickerTermurah.</p>
          </div>

          {pageErrors.email && (
            <div className="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
              <div className="flex items-start gap-2">
                <AlertCircle className="h-5 w-5 shrink-0" />
                <p>{pageErrors.email}</p>
              </div>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="email" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Emel
              </label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  id="email"
                  type="text"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  placeholder="admin@sticker.com"
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
              </div>
            </div>

            <div>
              <label htmlFor="password" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Kata Laluan
              </label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  id="password"
                  type="password"
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  placeholder="••••••••"
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
              </div>
            </div>

            <div className="flex items-center justify-between">
              <label className="flex items-center gap-2 text-sm text-slate-600">
                <input
                  type="checkbox"
                  checked={data.remember}
                  onChange={(e) => setData('remember', e.target.checked)}
                  className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                />
                Ingat saya
              </label>
            </div>

            <button
              type="submit"
              disabled={processing}
              className="admin-btn-primary w-full text-sm"
            >
              <LogIn className="h-4 w-4" />
              {processing ? 'Sedang Log Masuk...' : 'Log Masuk'}
            </button>
          </form>
        </div>
      </div>
    </FrontendLayout>
  );
}
