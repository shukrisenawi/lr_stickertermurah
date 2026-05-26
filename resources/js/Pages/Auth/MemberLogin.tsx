import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { LogIn, Mail, Lock, AlertCircle } from 'lucide-react';

interface MemberLoginProps {
  errors: {
    email?: string;
  };
}

export default function MemberLogin({ errors: pageErrors }: MemberLoginProps) {
  const { data, setData, post, processing } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('member.login.attempt'));
  };

  return (
    <FrontendLayout>
      <Head title="Log Masuk Ahli" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="mx-auto max-w-md">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-slate-900">Log Masuk Ahli</h1>
            <p className="mt-2 text-sm text-slate-500">Log masuk untuk mengurus order anda.</p>
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
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  placeholder="nama@email.com"
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

          <div className="mt-6 space-y-3">
            <Link
              href={route('member.google.redirect')}
              className="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
              <svg className="h-4 w-4" viewBox="0 0 24 24" role="img" aria-label="Google">
                <title>Google</title>
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57a10.73 10.73 0 0 0 3.27-8.1z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Log Masuk dengan Google
            </Link>
          </div>

          <p className="mt-6 text-center text-sm text-slate-500">
            Tiada akaun?{' '}
            <Link href={route('member.register')} className="font-medium text-brand-600 hover:text-brand-700">
              Daftar sekarang
            </Link>
          </p>
        </div>
      </div>
    </FrontendLayout>
  );
}
