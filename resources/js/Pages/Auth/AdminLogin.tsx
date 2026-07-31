import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, useForm } from '@inertiajs/react';
import {
  AlertCircle,
  Eye,
  EyeOff,
  Lock,
  LogIn,
  Mail,
  Package,
  ShieldCheck,
  Truck,
} from 'lucide-react';
import { useState } from 'react';

interface AdminLoginProps {
  defaultEmail: string;
  defaultPassword: string;
  errors: {
    email?: string;
  };
}

const FEATURES = [
  { icon: Package, text: 'Urus design, kategori & harga sticker' },
  { icon: Truck, text: 'Jana waybill J&T & tracking penghantaran' },
  { icon: ShieldCheck, text: 'Akses terhad kepada pentadbir sahaja' },
];

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
    <FrontendLayout>
      <Head title="Log Masuk Admin" />

      <section className="relative overflow-hidden bg-gradient-to-b from-slate-100 via-white to-white">
        <div className="relative mx-auto grid max-w-[1280px] items-center gap-10 px-4 py-12 lg:grid-cols-[1.1fr_1fr] lg:gap-12 lg:px-8 lg:py-16">
          {/* ========== Panel Pentadbir (desktop) ========== */}
          <div className="backstage-radial relative hidden min-h-[540px] overflow-hidden rounded-[2.5rem] p-10 text-white shadow-2xl shadow-slate-900/20 lg:flex lg:flex-col">
            {/* Stiker hiasan */}
            <div className="animate-float absolute -right-10 -top-10 w-40 rotate-12 opacity-90">
              <img
                src="/images/showcase/sticker-20.webp"
                alt=""
                className="w-full rounded-full shadow-2xl ring-4 ring-white/20"
              />
            </div>
            <div className="animate-float-slow absolute -bottom-12 -left-8 w-36 -rotate-12 opacity-80">
              <img
                src="/images/showcase/sticker-28.webp"
                alt=""
                className="w-full rounded-full shadow-2xl ring-4 ring-white/20"
              />
            </div>

            <div className="relative">
              <div className="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white backdrop-blur-sm">
                <ShieldCheck className="h-3 w-3" />
                Portal Pentadbir
              </div>
              <h1 className="mt-6 font-display text-4xl font-bold leading-[1.1] tracking-tight">
                Panel Kawalan <span className="text-brand-300">StickerTermurah</span>
              </h1>
              <p className="mt-4 max-w-sm text-sm leading-relaxed text-slate-300">
                Urus operasi kedai — dari design &amp; harga hingga ke order, invoice dan penghantaran.
              </p>
            </div>

            <ul className="relative mt-auto space-y-4 pt-10">
              {FEATURES.map(({ icon: Icon, text }) => (
                <li key={text} className="flex items-center gap-3">
                  <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-brand-300 backdrop-blur-sm">
                    <Icon className="h-5 w-5" />
                  </span>
                  <span className="text-sm font-semibold text-slate-200">{text}</span>
                </li>
              ))}
            </ul>
          </div>

          {/* ========== Borang Log Masuk Admin ========== */}
          <div className="mx-auto w-full max-w-md">
            <div className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-8">
              <div className="text-center">
                <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg">
                  <ShieldCheck className="h-7 w-7" />
                </div>
                <h2 className="font-display text-3xl font-bold tracking-tight text-slate-900">Log Masuk Admin</h2>
                <p className="mt-2 text-sm text-slate-500">Akses panel pentadbiran StickerTermurah.</p>
              </div>

              {pageErrors.email && (
                <div className="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                  <div className="flex items-start gap-2">
                    <AlertCircle className="h-5 w-5 shrink-0" />
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
                      className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
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
                      className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-11 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
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

                <label className="flex items-center gap-2 text-sm text-slate-600">
                  <input
                    type="checkbox"
                    checked={data.remember}
                    onChange={(e) => setData('remember', e.target.checked)}
                    className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                  />
                  Ingat saya
                </label>

                <button
                  type="submit"
                  disabled={processing}
                  className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:opacity-60"
                >
                  <LogIn className="h-4 w-4" />
                  {processing ? 'Sedang Log Masuk...' : 'Log Masuk'}
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>
    </FrontendLayout>
  );
}
