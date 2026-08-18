import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, KeyRound, Lock } from 'lucide-react';

interface MemberResetPasswordProps {
  token: string;
  email: string;
}

export default function MemberResetPassword({ token, email }: MemberResetPasswordProps) {
  const { data, setData, post, processing, errors } = useForm({
    token,
    email,
    password: '',
    password_confirmation: '',
  });

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    post(route('password.update'));
  };

  return (
    <FrontendLayout hideNavbar>
      <Head title="Tetapkan Semula Kata Laluan" />
      <PublicHeader />

      <section className="relative min-h-[calc(100vh-4rem)] overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white px-4 pb-16 pt-10">
        <div aria-hidden="true" className="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-brand-100/60 blur-3xl" />
        <div className="relative mx-auto max-w-md rounded-[2rem] border border-slate-100 bg-white p-6 shadow-xl shadow-brand-900/5 sm:p-8">
          <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
            <KeyRound className="h-6 w-6" />
          </div>
          <div className="mt-5 text-center">
            <h1 className="font-display text-3xl font-bold tracking-tight text-slate-900">Kata Laluan Baharu</h1>
            <p className="mt-2 text-sm text-slate-500">Tetapkan kata laluan baharu minimum 6 aksara.</p>
          </div>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <label htmlFor="email" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Email Akaun</label>
              <input id="email" type="email" value={data.email} readOnly className="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 outline-none" />
              {errors.email && <p className="mt-1 text-xs text-rose-600">{errors.email}</p>}
            </div>

            <div>
              <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kata Laluan Baharu</label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input id="password" type="password" minLength={6} value={data.password} onChange={(event) => setData('password', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
              </div>
              {errors.password && <p className="mt-1 text-xs text-rose-600">{errors.password}</p>}
            </div>

            <div>
              <label htmlFor="password_confirmation" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Sahkan Kata Laluan</label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input id="password_confirmation" type="password" minLength={6} value={data.password_confirmation} onChange={(event) => setData('password_confirmation', event.target.value)} className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
              </div>
            </div>

            <button type="submit" disabled={processing} className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:opacity-60">
              <KeyRound className="h-4 w-4" />
              {processing ? 'Sedang Menyimpan...' : 'Tetapkan Kata Laluan'}
            </button>
          </form>

          <Link href={route('member.login')} className="mt-6 inline-flex w-full items-center justify-center gap-2 text-sm font-bold text-brand-600 hover:text-brand-700">
            <ArrowLeft className="h-4 w-4" />
            Kembali ke Login
          </Link>
        </div>
      </section>
    </FrontendLayout>
  );
}
