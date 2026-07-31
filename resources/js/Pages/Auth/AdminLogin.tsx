import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { AlertCircle, Eye, EyeOff, Lock, LogIn, Mail, ShieldCheck } from 'lucide-react';
import { useState } from 'react';

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
  const [showPassword, setShowPassword] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.login.attempt'));
  };

  return (
    <FrontendLayout hideNavbar>
      <Head title="Login Admin" />

      <section className="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-white to-brand-50/40 px-4 py-12">
        {/* Hiasan latar lembut */}
        <div aria-hidden="true" className="pointer-events-none absolute -left-1/4 top-0 h-[600px] w-[600px] rounded-full bg-brand-200/40 blur-[120px]" />
        <div aria-hidden="true" className="pointer-events-none absolute -bottom-1/4 -right-1/4 h-[500px] w-[500px] rounded-full bg-amber-100/60 blur-[100px]" />

        <div className="relative w-full max-w-md">
          {/* Kad utama */}
          <div className="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-2xl shadow-slate-900/10">
            {/* Bar aksen brand di atas */}
            <div className="h-1.5 w-full bg-gradient-to-r from-brand-700 via-brand-500 to-brand-700" />

            <div className="p-6 sm:p-8">
              <div className="text-center">
                <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white shadow-lg shadow-brand-600/25">
                  <ShieldCheck className="h-8 w-8" />
                </div>
                <h1 className="font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Login Admin</h1>
                <p className="mt-2 text-sm text-slate-500">Akses terhad kepada pentadbir yang telah dibenarkan.</p>
              </div>

              {pageErrors.email && (
                <div className="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                  <div className="flex items-start gap-2">
                    <AlertCircle className="h-5 w-5 shrink-0 text-rose-600" />
                    <p>{pageErrors.email}</p>
                  </div>
                </div>
              )}

              <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                <div>
                  <label htmlFor="email" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
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
                      className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Kata Laluan
                  </label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                      id="password"
                      type={showPassword ? 'text' : 'password'}
                      value={data.password}
                      onChange={(e) => setData('password', e.target.value)}
                      placeholder="••••••••"
                      className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-11 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-brand-600"
                      aria-label={showPassword ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'}
                    >
                      {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                    </button>
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
                  className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:opacity-60"
                >
                  <LogIn className="h-4 w-4" />
                  {processing ? 'Sedang Login...' : 'Login'}
                </button>
              </form>

              <div className="mt-6 text-center">
                <Link
                  href="/"
                  className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-brand-600"
                >
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                  </svg>
                  Kembali ke StickerTermurah
                </Link>
              </div>
            </div>
          </div>

          {/* Nota keselamatan */}
          <p className="mt-6 text-center text-xs text-slate-500">
            <span className="inline-flex items-center gap-1.5">
              <ShieldCheck className="h-3 w-3" />
              Sesi login dipantau untuk keselamatan akaun.
            </span>
          </p>
        </div>
      </section>
    </FrontendLayout>
  );
}
