import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, useForm, Link, usePage } from '@inertiajs/react';
import { Save, ArrowLeft, Lock } from 'lucide-react';
import { type PageProps } from '@/types';

export default function PasswordEdit() {
  const { auth } = usePage<PageProps>().props;
  const mustChangePassword = auth.user?.must_change_password ?? false;
  const { data, setData, put, processing, errors } = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('member.profile.password.update'));
  };

  return (
    <MemberLayout>
      <Head title="Tukar Kata Laluan" />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8 space-y-6">
        {!mustChangePassword && <Link
          href={route('member.profile.edit')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          Kembali ke Profil
        </Link>}

        <div>
          <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">Tukar Kata Laluan</h1>
          <p className="mt-1 text-sm text-slate-500">
            {mustChangePassword ? 'Anda perlu menukar kata laluan sementara sebelum meneruskan.' : 'Kemaskini kata laluan akaun anda.'}
          </p>
        </div>

        {mustChangePassword && (
          <div className="max-w-2xl rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Masukkan <strong>123</strong> sebagai kata laluan semasa, kemudian cipta kata laluan baharu minimum 6 aksara.
          </div>
        )}

        <form onSubmit={handleSubmit} className="max-w-2xl frontend-flat-card p-6 space-y-4">
          <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
            <Lock className="h-4 w-4" />
            Kata Laluan
          </h3>

          <div>
            <label htmlFor="current_password" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Kata Laluan Semasa</label>
            <input id="current_password" type="password" value={data.current_password} onChange={(e) => setData('current_password', e.target.value)} placeholder={mustChangePassword ? '123' : undefined}
              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
            {errors.current_password && <p className="mt-1 text-xs text-rose-600">{errors.current_password}</p>}
          </div>

          <div>
            <label htmlFor="password" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Kata Laluan Baharu</label>
            <input id="password" type="password" minLength={6} value={data.password} onChange={(e) => setData('password', e.target.value)}
              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
            {errors.password && <p className="mt-1 text-xs text-rose-600">{errors.password}</p>}
          </div>

          <div>
            <label htmlFor="password_confirmation" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Sahkan Kata Laluan</label>
            <input id="password_confirmation" type="password" minLength={6} value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)}
              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
          </div>

          <button type="submit" disabled={processing}
            className="frontend-btn-primary text-sm">
            <Save className="h-4 w-4" />
            {processing ? 'Menyimpan...' : 'Kemaskini Kata Laluan'}
          </button>
        </form>
      </div>
    </MemberLayout>
  );
}
