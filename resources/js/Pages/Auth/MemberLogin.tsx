import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
  AlertCircle,
  BadgePercent,
  Eye,
  EyeOff,
  Lock,
  LogIn,
  Mail,
  MessageCircle,
  Sparkles,
  Truck,
} from 'lucide-react';
import { useState } from 'react';

interface MemberLoginProps {
  errors: {
    email?: string;
  };
}

const PERKS = [
  { icon: BadgePercent, text: 'Ulang tempahan design kegemaran dengan pantas' },
  { icon: Truck, text: 'Semak status order & tracking J&T sendiri' },
  { icon: MessageCircle, text: 'Mudah berhubung dengan kami bila-bila masa' },
];

const MOBILE_STICKERS = [
  '/images/showcase/sticker-01.webp',
  '/images/showcase/sticker-05.webp',
  '/images/showcase/sticker-23.webp',
  '/images/showcase/sticker-26.webp',
  '/images/showcase/sticker-33.webp',
];

export default function MemberLogin({ errors: pageErrors }: MemberLoginProps) {
  const { data, setData, post, processing } = useForm({
    email: '',
    password: '',
    remember: false,
  });
  const [showPassword, setShowPassword] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('member.login.attempt'));
  };

  return (
    <FrontendLayout>
      <Head title="Log Masuk Ahli" />

      <section className="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white">
        {/* Hiasan latar lembut */}
        <div aria-hidden="true" className="pointer-events-none absolute -left-24 top-16 h-72 w-72 rounded-full bg-brand-100/60 blur-3xl" />
        <div aria-hidden="true" className="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-amber-100/70 blur-3xl" />

        <div className="relative mx-auto grid max-w-[1280px] items-center gap-12 px-4 py-12 lg:grid-cols-2 lg:gap-10 lg:px-8 lg:py-20">
          {/* ========== Panel Jenama (desktop) ========== */}
          <div className="relative hidden lg:block">
            <div className="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-white px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700 shadow-sm">
              <Sparkles className="h-3 w-3" />
              Portal Ahli
            </div>
            <h1 className="mt-6 font-display text-5xl font-bold leading-[1.05] tracking-tight text-slate-900">
              Selamat Kembali, <span className="text-brand-600">Kawan!</span>
            </h1>
            <p className="mt-4 max-w-md text-base leading-relaxed text-slate-500">
              Log masuk untuk urus order, semak status penghantaran &amp; ulang tempahan design kegemaran anda.
            </p>

            {/* Kolaj sticker */}
            <div className="relative mx-auto mt-8 aspect-square w-full max-w-[360px]">
              <div className="absolute inset-[8%] rounded-full bg-gradient-to-br from-brand-100 via-brand-50 to-amber-50" />
              <div className="absolute inset-[18%] rounded-full border-2 border-dashed border-brand-200" />

              <img
                src="/images/showcase/sticker-05.webp"
                alt="Contoh sticker"
                className="absolute left-1/2 top-1/2 w-[52%] -translate-x-1/2 -translate-y-1/2 rounded-full shadow-2xl shadow-brand-900/20 ring-8 ring-white"
              />
              <div className="animate-float absolute left-[2%] top-[6%] w-[30%]">
                <img
                  src="/images/showcase/sticker-01.webp"
                  alt=""
                  className="w-full -rotate-[8deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                />
              </div>
              <div className="animate-float-slow absolute right-[0%] top-[14%] w-[27%]">
                <img
                  src="/images/showcase/sticker-23.webp"
                  alt=""
                  className="w-full rotate-[7deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                />
              </div>
              <div className="animate-float absolute bottom-[8%] right-[8%] w-[29%]">
                <img
                  src="/images/showcase/sticker-26.webp"
                  alt=""
                  className="w-full -rotate-[6deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                />
              </div>

              {/* Lencana kuning */}
              <div className="animate-wiggle absolute -right-1 top-[42%] flex h-20 w-20 items-center justify-center rounded-full bg-accent text-center shadow-xl shadow-amber-500/30 ring-4 ring-white">
                <span className="font-display text-[10px] font-bold leading-tight text-slate-900">
                  AHLI
                  <br />
                  JIMAT
                  <br />
                  LEBIH
                </span>
              </div>
            </div>

            {/* Kelebihan ahli */}
            <ul className="mt-8 space-y-4">
              {PERKS.map(({ icon: Icon, text }) => (
                <li key={text} className="flex items-center gap-3">
                  <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-md shadow-brand-600/10 ring-1 ring-brand-100">
                    <Icon className="h-5 w-5" />
                  </span>
                  <span className="text-sm font-semibold text-slate-700">{text}</span>
                </li>
              ))}
            </ul>
          </div>

          {/* ========== Borang Log Masuk ========== */}
          <div className="mx-auto w-full max-w-md">
            {/* Jalur sticker (mobile sahaja) */}
            <div className="mb-6 flex justify-center lg:hidden" aria-hidden="true">
              <div className="flex -space-x-4">
                {MOBILE_STICKERS.map((src) => (
                  <img
                    key={src}
                    src={src}
                    alt=""
                    className="h-14 w-14 rounded-full object-cover shadow-lg ring-4 ring-white"
                  />
                ))}
              </div>
            </div>

            <div className="rounded-[2rem] border border-slate-100 bg-white p-6 shadow-xl shadow-brand-900/5 sm:p-8">
              <div className="text-center">
                <h2 className="font-display text-3xl font-bold tracking-tight text-slate-900">Log Masuk Ahli</h2>
                <p className="mt-2 text-sm text-slate-500">Teruskan tempahan sticker anda di sini.</p>
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
                      placeholder="nama@email.com"
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

            <p className="mt-6 text-center text-sm text-slate-500">
              Tiada akaun?{' '}
              <Link href={route('member.register')} className="font-bold text-brand-600 hover:text-brand-700">
                Daftar sekarang
              </Link>
            </p>
            <p className="mt-2 text-center text-xs text-slate-400">
              Lupa kata laluan?{' '}
              <a
                href="https://wa.me/601169409606"
                target="_blank"
                rel="noreferrer"
                className="font-semibold text-emerald-600 hover:text-emerald-700"
              >
                WhatsApp kami
              </a>
            </p>
          </div>
        </div>
      </section>
    </FrontendLayout>
  );
}
