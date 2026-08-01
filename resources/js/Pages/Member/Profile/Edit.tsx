import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, useForm, usePage, Link } from '@inertiajs/react';
import { Save, User, Mail, MapPin, Camera } from 'lucide-react';
import { type PageProps } from '@/types';
import { useState } from 'react';

interface Address {
  id: number;
  address: string;
  no_hp: string | null;
}

interface ProfileEditProps extends PageProps {
  addresses: Address[];
}

export default function ProfileEdit() {
  const { auth, addresses } = usePage<ProfileEditProps>().props;
  const user = auth.user!;

  const { data, setData, post, processing, errors } = useForm({
    name: user.name,
    email: user.email,
    avatar: null as File | null,
  });

  const { data: addrData, setData: setAddrData, post: postAddr, processing: addrProcessing, errors: addrErrors } = useForm({
    address: addresses[0]?.address ?? '',
    no_hp: addresses[0]?.no_hp ?? '',
  });

  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('member.profile.update'), {
      forceFormData: true,
      onSuccess: () => setAvatarPreview(null),
    });
  };

  const handleAddressSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    postAddr(route('member.profile.address'));
  };

  const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    if (!file) return;
    setData('avatar', file);
    setAvatarPreview(URL.createObjectURL(file));
    // Auto submit bila pilih avatar
    setTimeout(() => {
      post(route('member.profile.update'), {
        forceFormData: true,
        onSuccess: () => setAvatarPreview(null),
      });
    }, 100);
  };

  const displayAvatar = avatarPreview ?? user.avatar_url;

  return (
    <MemberLayout>
      <Head title="Profil Saya" />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8 space-y-6">
        <div>
          <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">Profil Saya</h1>
          <p className="mt-1 text-sm text-slate-500">Kemaskini maklumat peribadi, alamat & kata laluan anda.</p>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          {/* Avatar Card */}
          <div className="frontend-flat-card p-6 text-center">
            <div className="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-brand-100 text-3xl font-bold text-brand-600 overflow-hidden">
              {displayAvatar ? (
                <img src={displayAvatar} alt={user.name} className="h-full w-full object-cover" />
              ) : (
                user.name?.charAt(0).toUpperCase() ?? 'U'
              )}
            </div>
            <p className="mt-3 text-sm font-bold text-slate-900">{user.name}</p>
            <p className="text-xs text-slate-500">{user.email}</p>
            <label htmlFor="avatar-upload" className="mt-4 inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
              <Camera className="h-3.5 w-3.5" />
              Tukar Avatar
            </label>
            <input id="avatar-upload" type="file" accept="image/*" onChange={handleAvatarChange} className="hidden" />
            {errors.avatar && <p className="mt-2 text-xs text-rose-600">{errors.avatar}</p>}
          </div>

          {/* Profile Form */}
          <div className="lg:col-span-2 space-y-6">
            <form onSubmit={handleSubmit} className="frontend-flat-card p-6 space-y-4">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <User className="h-4 w-4" />
                Maklumat Peribadi
              </h3>

              <div>
                <label htmlFor="name" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</label>
                <input id="name" type="text" value={data.name} onChange={(e) => setData('name', e.target.value)}
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
              </div>

              <div>
                <label htmlFor="email" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Emel</label>
                <input id="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                {errors.email && <p className="mt-1 text-xs text-rose-600">{errors.email}</p>}
              </div>

              <button type="submit" disabled={processing}
                className="frontend-btn-primary text-sm">
                <Save className="h-4 w-4" />
                {processing ? 'Menyimpan...' : 'Simpan Profil'}
              </button>
            </form>

            {/* Address Form */}
            <form onSubmit={handleAddressSubmit} className="frontend-flat-card p-6 space-y-4">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <MapPin className="h-4 w-4" />
                Alamat Penghantaran
              </h3>

              <div>
                <label htmlFor="no_hp" className="text-xs font-semibold uppercase tracking-wider text-slate-500">No. Telefon</label>
                <input id="no_hp" type="text" value={addrData.no_hp} onChange={(e) => setAddrData('no_hp', e.target.value)}
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                {addrErrors.no_hp && <p className="mt-1 text-xs text-rose-600">{addrErrors.no_hp}</p>}
              </div>

              <div>
                <label htmlFor="address" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat</label>
                <textarea id="address" rows={3} value={addrData.address} onChange={(e) => setAddrData('address', e.target.value)}
                  className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                {addrErrors.address && <p className="mt-1 text-xs text-rose-600">{addrErrors.address}</p>}
              </div>

              <button type="submit" disabled={addrProcessing}
                className="frontend-btn-primary text-sm">
                <Save className="h-4 w-4" />
                {addrProcessing ? 'Menyimpan...' : 'Simpan Alamat'}
              </button>
            </form>

            {/* Password Link */}
            <div className="frontend-flat-card p-6">
              <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <Mail className="h-4 w-4" />
                Kata Laluan
              </h3>
              <p className="mt-2 text-sm text-slate-500">Tukar kata laluan akaun anda.</p>
              <Link href={route('member.profile.password')} className="mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Tukar Kata Laluan
              </Link>
            </div>
          </div>
        </div>
      </div>
    </MemberLayout>
  );
}