import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { Save, User, Mail, MapPin, Camera, Plus, Trash2, Star, X } from 'lucide-react';
import { type PageProps } from '@/types';
import { useState } from 'react';

interface Address {
  id: number;
  address: string;
  no_hp: string | null;
  is_default: boolean;
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

  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const [showAddrForm, setShowAddrForm] = useState(false);
  const [editingAddrId, setEditingAddrId] = useState<number | null>(null);

  const { data: addrData, setData: setAddrData, post: postAddr, put: putAddr, processing: addrProcessing, errors: addrErrors, reset: resetAddr } = useForm({
    address: '',
    no_hp: '',
    is_default: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('member.profile.update'), {
      forceFormData: true,
      onSuccess: () => setAvatarPreview(null),
    });
  };

  const handleAvatarChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    if (!file) return;
    setData('avatar', file);
    setAvatarPreview(URL.createObjectURL(file));
    setTimeout(() => {
      post(route('member.profile.update'), {
        forceFormData: true,
        onSuccess: () => setAvatarPreview(null),
      });
    }, 100);
  };

  const displayAvatar = avatarPreview ?? user.avatar_url;

  // Address handlers
  const startAddAddress = () => {
    resetAddr();
    setEditingAddrId(null);
    setShowAddrForm(true);
  };

  const startEditAddress = (addr: Address) => {
    setAddrData('address', addr.address);
    setAddrData('no_hp', addr.no_hp ?? '');
    setAddrData('is_default', addr.is_default);
    setEditingAddrId(addr.id);
    setShowAddrForm(true);
  };

  const handleAddrSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingAddrId) {
      putAddr(route('member.profile.address.update', editingAddrId), {
        onSuccess: () => {
          resetAddr();
          setShowAddrForm(false);
          setEditingAddrId(null);
        },
      });
    } else {
      postAddr(route('member.profile.address.store'), {
        onSuccess: () => {
          resetAddr();
          setShowAddrForm(false);
        },
      });
    }
  };

  const handleSetDefault = (addr: Address) => {
    if (addr.is_default) return;
    router.post(route('member.profile.address.default', addr.id), {}, { preserveScroll: true });
  };

  const handleDeleteAddr = (addr: Address) => {
    if (!confirm('Padam alamat ini?')) return;
    router.delete(route('member.profile.address.destroy', addr.id), { preserveScroll: true });
  };

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

          {/* Right Column */}
          <div className="lg:col-span-2 space-y-6">
            {/* Profile Form */}
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

            {/* Addresses List */}
            <div className="frontend-flat-card p-6 space-y-4">
              <div className="flex items-center justify-between">
                <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                  <MapPin className="h-4 w-4" />
                  Alamat Penghantaran
                </h3>
                {!showAddrForm && (
                  <button
                    type="button"
                    onClick={startAddAddress}
                    className="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-700"
                  >
                    <Plus className="h-3.5 w-3.5" />
                    Tambah Alamat
                  </button>
                )}
              </div>

              {/* Address Form (Add/Edit) */}
              {showAddrForm && (
                <form onSubmit={handleAddrSubmit} className="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div className="flex items-center justify-between">
                    <p className="text-sm font-bold text-slate-900">{editingAddrId ? 'Edit Alamat' : 'Tambah Alamat Baru'}</p>
                    <button
                      type="button"
                      onClick={() => { setShowAddrForm(false); setEditingAddrId(null); resetAddr(); }}
                      className="text-slate-400 hover:text-slate-600"
                    >
                      <X className="h-4 w-4" />
                    </button>
                  </div>

                  <div>
                    <label htmlFor="addr-no_hp" className="text-xs font-semibold uppercase tracking-wider text-slate-500">No. Telefon</label>
                    <input id="addr-no_hp" type="text" value={addrData.no_hp} onChange={(e) => setAddrData('no_hp', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                    {addrErrors.no_hp && <p className="mt-1 text-xs text-rose-600">{addrErrors.no_hp}</p>}
                  </div>

                  <div>
                    <label htmlFor="addr-address" className="text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat</label>
                    <textarea id="addr-address" rows={3} value={addrData.address} onChange={(e) => setAddrData('address', e.target.value)}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                    {addrErrors.address && <p className="mt-1 text-xs text-rose-600">{addrErrors.address}</p>}
                  </div>

                  <div className="flex items-center gap-3">
                    <label className="flex cursor-pointer items-center gap-2">
                      <input
                        type="checkbox"
                        checked={addrData.is_default}
                        onChange={(e) => setAddrData('is_default', e.target.checked)}
                        className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                      />
                      <span className="text-xs font-medium text-slate-700">Tetapkan sebagai alamat utama</span>
                    </label>
                  </div>

                  <div className="flex gap-3">
                    <button type="submit" disabled={addrProcessing}
                      className="frontend-btn-primary text-xs">
                      <Save className="h-3.5 w-3.5" />
                      {addrProcessing ? 'Menyimpan...' : 'Simpan'}
                    </button>
                    <button type="button" onClick={() => { setShowAddrForm(false); setEditingAddrId(null); resetAddr(); }}
                      className="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                      Batal
                    </button>
                  </div>
                </form>
              )}

              {/* Address Cards */}
              {addresses.length === 0 && !showAddrForm ? (
                <div className="rounded-2xl border border-dashed border-slate-200 py-10 text-center">
                  <MapPin className="mx-auto h-10 w-10 text-slate-300" />
                  <p className="mt-2 text-sm text-slate-500">Belum ada alamat. Klik "Tambah Alamat".</p>
                </div>
              ) : (
                <div className="space-y-3">
                  {addresses.map((addr) => (
                    <div key={addr.id} className={`rounded-2xl border p-4 transition ${addr.is_default ? 'border-brand-200 bg-brand-50' : 'border-slate-200 bg-white'}`}>
                      <div className="flex items-start justify-between gap-3">
                        <div className="flex-1 space-y-1">
                          <div className="flex items-center gap-2">
                            {addr.is_default && (
                              <span className="inline-flex items-center gap-1 rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                <Star className="h-2.5 w-2.5 fill-current" />
                                Utama
                              </span>
                            )}
                            <span className="text-xs text-slate-500">{addr.no_hp}</span>
                          </div>
                          <p className="text-sm text-slate-700">{addr.address}</p>
                        </div>
                        <div className="flex shrink-0 items-center gap-1">
                          {!addr.is_default && (
                            <button
                              type="button"
                              onClick={() => handleSetDefault(addr)}
                              title="Tetapkan sebagai utama"
                              className="rounded-lg p-1.5 text-slate-400 transition hover:bg-brand-50 hover:text-brand-600"
                            >
                              <Star className="h-4 w-4" />
                            </button>
                          )}
                          <button
                            type="button"
                            onClick={() => startEditAddress(addr)}
                            title="Edit"
                            className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
                          >
                            <Save className="h-4 w-4" />
                          </button>
                          <button
                            type="button"
                            onClick={() => handleDeleteAddr(addr)}
                            title="Padam"
                            className="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

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