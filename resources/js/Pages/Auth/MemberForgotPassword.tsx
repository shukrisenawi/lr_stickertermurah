import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { type PageProps } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, KeyRound, Phone, Send } from 'lucide-react';

export default function MemberForgotPassword() {
  const { flash } = usePage<PageProps>().props;
  const { data, setData, post, processing, errors } = useForm({ no_tel: '' });

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    post(route('password.email'));
  };

  return (
    <FrontendLayout hideNavbar>
      <Head title="Lupa Kata Laluan" />
      <PublicHeader />

      <section className="relative min-h-[calc(100vh-4rem)] overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white px-4 pb-16 pt-10">
        <div aria-hidden="true" className="pointer-events-none absolute -left-24 top-16 h-72 w-72 rounded-full bg-brand-100/60 blur-3xl" />
        <div className="relative mx-auto max-w-md rounded-[2rem] border border-slate-100 bg-white p-6 shadow-xl shadow-brand-900/5 sm:p-8">
          <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
            <KeyRound className="h-6 w-6" />
          </div>
          <div className="mt-5 text-center">
            <h1 className="font-display text-3xl font-bold tracking-tight text-slate-900">Lupa Kata Laluan</h1>
            <p className="mt-2 text-sm leading-relaxed text-slate-500">
              Masukkan nombor telefon akaun ahli. Jika akaun mempunyai email, pautan reset akan dihantar ke email tersebut.
            </p>
          </div>

          {flash.success && (
            <div className="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
              {flash.success}
            </div>
          )}

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <label htmlFor="no_tel" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                No. Telefon
              </label>
              <div className="relative">
                <Phone className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  id="no_tel"
                  type="tel"
                  inputMode="numeric"
                  pattern="[0-9]*"
                  maxLength={13}
                  value={data.no_tel}
                  onChange={(event) => setData('no_tel', event.target.value.replace(/\D/g, ''))}
                  placeholder="Contoh: 0112222333"
                  className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
              </div>
              {errors.no_tel && <p className="mt-1 text-xs text-rose-600">{errors.no_tel}</p>}
            </div>

            <button type="submit" disabled={processing} className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:opacity-60">
              <Send className="h-4 w-4" />
              {processing ? 'Sedang Menyemak...' : 'Teruskan'}
            </button>
          </form>

          <p className="mt-4 text-center text-xs leading-relaxed text-slate-400">
            Jika akaun tiada email, anda akan dibawa terus ke WhatsApp syarikat untuk mendapatkan bantuan.
          </p>
          <Link href={route('member.login')} className="mt-6 inline-flex w-full items-center justify-center gap-2 text-sm font-bold text-brand-600 hover:text-brand-700">
            <ArrowLeft className="h-4 w-4" />
            Kembali ke Login
          </Link>
        </div>
      </section>
    </FrontendLayout>
  );
}
