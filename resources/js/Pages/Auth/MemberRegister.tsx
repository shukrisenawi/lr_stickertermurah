import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Mail, Lock, User } from 'lucide-react';

export default function MemberRegister() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    password_confirmation: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('member.register.store'));
  };

  return (
    <FrontendLayout>
      <Head title="Daftar Ahli" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="mx-auto max-w-md">
          <div className="text-center mb-8">
            <h1 className="text-2xl font-bold text-slate-900">Daftar Ahli</h1>
            <p className="mt-2 text-sm text-slate-500">Cipta akaun untuk membuat order dengan lebih mudah.</p>
          </div>

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
              {errors.email && (
                <p className="mt-1 text-xs text-rose-600">{errors.email}</p>
              )}
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
                  placeholder="Minimum 8 aksara"
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
              </div>
              {errors.password && (
                <p className="mt-1 text-xs text-rose-600">{errors.password}</p>
              )}
            </div>

            <div>
              <label htmlFor="password_confirmation" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                Sahkan Kata Laluan
              </label>
              <div className="relative">
                <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  id="password_confirmation"
                  type="password"
                  value={data.password_confirmation}
                  onChange={(e) => setData('password_confirmation', e.target.value)}
                  placeholder="••••••••"
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  required
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={processing}
              className="admin-btn-primary w-full text-sm"
            >
              <User className="h-4 w-4" />
              {processing ? 'Sedang Mendaftar...' : 'Daftar Akaun'}
            </button>
          </form>

          <p className="mt-6 text-center text-sm text-slate-500">
            Sudah ada akaun?{' '}
            <Link href={route('member.login')} className="font-medium text-brand-600 hover:text-brand-700">
              Log masuk
            </Link>
          </p>
        </div>
      </div>
    </FrontendLayout>
  );
}
