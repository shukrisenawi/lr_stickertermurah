import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import {
  Eye,
  EyeOff,
  Lock,
  LogIn,
  MapPin,
  MapPinOff,
  Phone,
  Sparkles,
  Star,
  Truck,
  UserPlus,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { type PageProps } from '@/types';

interface LookupAddress {
  id: number;
  recipient_name: string;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

interface RegisterLookup {
  phone: string | null;
  account_exists: boolean;
  addresses: LookupAddress[];
}

interface MemberRegisterProps extends PageProps {
  lookup?: RegisterLookup | null;
}

const PERKS = [
  { icon: Star, text: 'Pantasan untuk mengulang order kegemaran' },
  { icon: Truck, text: 'Semak status penghantaran secara langsung' },
  { icon: LogIn, text: 'Akses sejarah order & invoice anda' },
];

const MOBILE_STICKERS = [
  '/images/showcase/sticker-02.webp',
  '/images/showcase/sticker-06.webp',
  '/images/showcase/sticker-18.webp',
  '/images/showcase/sticker-29.webp',
  '/images/showcase/sticker-34.webp',
];

export default function MemberRegister() {
  const { lookup } = usePage<MemberRegisterProps>().props;
  const { data, setData, post, processing, errors } = useForm({
    no_tel: '',
    recipient_name: '',
    address: '',
    address_id: '',
    mode: 'new' as 'matched' | 'new',
    password: '',
    password_confirmation: '',
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [searching, setSearching] = useState(false);
  const [showAddressPicker, setShowAddressPicker] = useState(false);
  const [showRegistrationForm, setShowRegistrationForm] = useState(false);
  const [lookupComplete, setLookupComplete] = useState(false);

  useEffect(() => {
    if (!lookup) return;

    if (lookup.account_exists) {
      setShowAddressPicker(false);
      setShowRegistrationForm(false);
      return;
    }

    if (lookup.addresses.length > 0) {
      setShowAddressPicker(true);
      setShowRegistrationForm(false);
      return;
    }

    setShowAddressPicker(false);
    setShowRegistrationForm(true);
  }, [lookup]);

  const selectAddress = (address: LookupAddress) => {
    setData('address_id', String(address.id));
    setData('recipient_name', address.recipient_name);
    setData('address', address.address);
    setData('mode', 'matched');
    setShowRegistrationForm(false);
    setShowAddressPicker(false);
  };

  const resetToNewAddress = () => {
    setData('address_id', '');
    setData('mode', 'new');
    setData('recipient_name', '');
    setData('address', '');
    setShowRegistrationForm(true);
    setShowAddressPicker(false);
  };

  const handlePhoneChange = (value: string) => {
    setData('no_tel', value);

    if (!lookupComplete) return;

    setLookupComplete(false);
    setShowAddressPicker(false);
    setShowRegistrationForm(false);
    setData('address_id', '');
    setData('mode', 'new');
    setData('recipient_name', '');
    setData('address', '');
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearching(true);
    setData('address_id', '');
    setData('mode', 'new');
    setData('recipient_name', '');
    setData('address', '');
    setShowRegistrationForm(false);
    router.get(route('member.register'), { no_tel: data.no_tel }, {
      preserveState: true,
      replace: true,
      onSuccess: () => setLookupComplete(true),
      onFinish: () => setSearching(false),
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('member.register.store'));
  };

  const selectedAddress = lookup?.addresses.find((address) => String(address.id) === data.address_id) ?? null;
  const showNewAddressForm = Boolean(
    lookup && !lookup.account_exists && data.mode === 'new' && showRegistrationForm,
  );

  const passwordFields = (
    <>
      <div>
        <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
          Kata Laluan (Password)
        </label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            id="password"
            type={showPassword ? 'text' : 'password'}
            value={data.password}
            onChange={(e) => setData('password', e.target.value)}
            placeholder="Minimum 8 aksara"
            className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-11 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            required
          />
          <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-brand-600" aria-label={showPassword ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'}>
            {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
          </button>
        </div>
        {errors.password && <p className="mt-1 text-xs text-rose-600">{errors.password}</p>}
      </div>

      <div>
        <label htmlFor="password_confirmation" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
          Sahkan Kata Laluan
        </label>
        <div className="relative">
          <Lock className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <input
            id="password_confirmation"
            type={showConfirm ? 'text' : 'password'}
            value={data.password_confirmation}
            onChange={(e) => setData('password_confirmation', e.target.value)}
            placeholder="••••••••"
            className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-11 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            required
          />
          <button type="button" onClick={() => setShowConfirm(!showConfirm)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-brand-600" aria-label={showConfirm ? 'Sembunyikan kata laluan' : 'Tunjukkan kata laluan'}>
            {showConfirm ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
          </button>
        </div>
        {errors.password_confirmation && <p className="mt-1 text-xs text-rose-600">{errors.password_confirmation}</p>}
      </div>
    </>
  );

  return (
    <FrontendLayout hideNavbar>
      <Head title="Daftar Ahli" />

      <PublicHeader />

      <section className="relative overflow-hidden bg-gradient-to-b from-brand-50/70 via-white to-white pt-10 lg:pt-16">
        {/* Hiasan latar lembut */}
        <div aria-hidden="true" className="pointer-events-none absolute -left-24 top-16 h-72 w-72 rounded-full bg-brand-100/60 blur-3xl" />
        <div aria-hidden="true" className="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-amber-100/70 blur-3xl" />

        <div className="relative mx-auto grid max-w-[1280px] items-center gap-12 px-4 py-12 lg:grid-cols-2 lg:gap-10 lg:px-8 lg:py-20">
          {/* ========== Panel Jenama (desktop) ========== */}
          <div className="relative hidden lg:block">
            <div className="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-white px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700 shadow-sm">
              <Sparkles className="h-3 w-3" />
              Keahlian Percuma
            </div>
            <h1 className="mt-6 font-display text-5xl font-bold leading-[1.05] tracking-tight text-slate-900">
              Daftar & <span className="text-brand-600">Tempah</span> dengan Mudah.
            </h1>
            <p className="mt-4 max-w-md text-base leading-relaxed text-slate-500">
              Cipta akaun ahli untuk urus order, semak status penghantaran &amp; ulang tempahan design kegemaran anda.
            </p>

            {/* Kolaj sticker */}
            <div className="relative mx-auto mt-8 aspect-square w-full max-w-[360px]">
              <div className="absolute inset-[8%] rounded-full bg-gradient-to-br from-brand-100 via-brand-50 to-amber-50" />
              <div className="absolute inset-[18%] rounded-full border-2 border-dashed border-brand-200" />

              <img
                src="/images/showcase/sticker-06.webp"
                alt="Contoh sticker"
                loading="lazy"
                decoding="async"
                className="absolute left-1/2 top-1/2 w-[52%] -translate-x-1/2 -translate-y-1/2 rounded-full shadow-2xl shadow-brand-900/20 ring-8 ring-white"
              />
              <div className="animate-float absolute left-[2%] top-[6%] w-[30%]">
                <img
                  src="/images/showcase/sticker-02.webp"
                  alt=""
                  loading="lazy"
                  decoding="async"
                  className="w-full -rotate-[8deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                />
              </div>
              <div className="animate-float-slow absolute right-[0%] top-[14%] w-[27%]">
                <img
                  src="/images/showcase/sticker-18.webp"
                  alt=""
                  loading="lazy"
                  decoding="async"
                  className="w-full rotate-[7deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                />
              </div>
              <div className="animate-float absolute bottom-[8%] right-[8%] w-[29%]">
                <img
                  src="/images/showcase/sticker-29.webp"
                  alt=""
                  loading="lazy"
                  decoding="async"
                  className="w-full -rotate-[6deg] rounded-full shadow-xl shadow-brand-900/15 ring-4 ring-white"
                />
              </div>

              {/* Lencana kuning */}
              <div className="animate-wiggle absolute -right-1 top-[42%] flex h-20 w-20 items-center justify-center rounded-full bg-accent text-center shadow-xl shadow-amber-500/30 ring-4 ring-white">
                <span className="font-display text-[10px] font-bold leading-tight text-slate-900">
                  DAFTAR
                  <br />
                  PERCUMA
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

          {/* ========== Borang Pendaftaran ========== */}
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
                <h2 className="font-display text-3xl font-bold tracking-tight text-slate-900">Daftar Ahli</h2>
                <p className="mt-2 text-sm text-slate-500">Masukkan nombor telefon untuk mula mendaftar.</p>
              </div>

              <form onSubmit={handleSearch} className="mt-6 space-y-3">
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
                      onChange={(e) => handlePhoneChange(e.target.value.replace(/\D/g, ''))}
                      placeholder="Contoh: 0112222333"
                      className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                  </div>
                  {errors.no_tel && <p className="mt-1 text-xs text-rose-600">{errors.no_tel}</p>}
                </div>

                {(!lookupComplete || searching) && (
                  <button
                    type="submit"
                    disabled={searching || !data.no_tel.trim()}
                    className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    <UserPlus className="h-4 w-4" />
                    {searching ? 'Sedang Menyemak...' : 'Daftar Sekarang'}
                  </button>
                )}
              </form>

              {lookup?.account_exists && (
                <div className="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                  <p className="font-bold">Akaun untuk nombor ini sudah wujud.</p>
                  <p className="mt-1 text-xs leading-relaxed">Sila login menggunakan no. HP atau email anda.</p>
                  <Link href={route('member.login')} className="mt-3 inline-flex rounded-lg bg-amber-900 px-3 py-2 text-xs font-bold text-white">Ke Login</Link>
                </div>
              )}

              {showNewAddressForm && (
                <form onSubmit={handleSubmit} className="mt-5 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div>
                    <p className="text-sm font-bold text-slate-900">Masukkan alamat penghantaran</p>
                    <p className="mt-1 text-xs text-slate-500">Alamat ini akan disimpan sebagai alamat utama anda.</p>
                  </div>
                  <div>
                    <label htmlFor="recipient_name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Penerima</label>
                    <input id="recipient_name" type="text" value={data.recipient_name} onChange={(e) => setData('recipient_name', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                    {errors.recipient_name && <p className="mt-1 text-xs text-rose-600">{errors.recipient_name}</p>}
                  </div>
                  <div>
                    <label htmlFor="address" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat Penghantaran</label>
                    <textarea id="address" rows={4} value={data.address} onChange={(e) => setData('address', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" required />
                    {errors.address && <p className="mt-1 text-xs text-rose-600">{errors.address}</p>}
                  </div>
                  {passwordFields}
                  <button type="submit" disabled={processing} className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:opacity-60">
                    <UserPlus className="h-4 w-4" />
                    {processing ? 'Sedang Mendaftar...' : 'Daftar Sekarang'}
                  </button>
                </form>
              )}

              {lookup && !lookup.account_exists && selectedAddress && data.mode === 'matched' && (
                <form onSubmit={handleSubmit} className="mt-5 space-y-4 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                  <div>
                    <p className="text-sm font-bold text-slate-900">Alamat dipilih</p>
                    <p className="mt-2 text-sm font-semibold text-slate-800">{selectedAddress.recipient_name}</p>
                    <p className="mt-1 text-xs leading-relaxed text-slate-600">{selectedAddress.address}</p>
                    <p className="mt-3 text-xs text-slate-600">Tetapkan kata laluan baharu untuk mengaktifkan akaun anda.</p>
                  </div>
                  {passwordFields}
                  <button type="submit" disabled={processing} className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.98] disabled:opacity-60">
                    <UserPlus className="h-4 w-4" />
                    {processing ? 'Sedang Mendaftar...' : 'Daftar Sekarang'}
                  </button>
                </form>
              )}
            </div>

            <p className="mt-6 text-center text-sm text-slate-500">
              Sudah ada akaun?{' '}
              <Link href={route('member.login')} className="font-bold text-brand-600 hover:text-brand-700">
                Login
              </Link>
            </p>
          </div>
        </div>
      </section>

      {showAddressPicker && lookup && lookup.addresses.length > 0 && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="address-picker-title">
          <div className="max-h-[85vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-2xl sm:p-6">
            <div>
              <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Alamat ditemui</p>
              <h2 id="address-picker-title" className="mt-1 text-xl font-bold tracking-tight text-slate-900">Pilih alamat yang ingin digunakan</h2>
              <p className="mt-2 text-sm text-slate-500">Kami menemui alamat yang pernah digunakan dengan nombor ini.</p>
            </div>
            <div className="mt-5 space-y-3">
              {lookup.addresses.map((address) => (
                <button key={address.id} type="button" onClick={() => selectAddress(address)} className="w-full rounded-2xl border border-slate-200 p-4 text-left transition hover:border-brand-300 hover:bg-brand-50/50">
                  <span className="flex items-start gap-3">
                    <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                    <span className="min-w-0">
                      <span className="block text-sm font-bold text-slate-900">{address.recipient_name}</span>
                      <span className="mt-1 block text-xs leading-relaxed text-slate-600">{address.address}</span>
                    </span>
                  </span>
                </button>
              ))}
              <button type="button" onClick={resetToNewAddress} className="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                <MapPinOff className="h-4 w-4" />
                Bukan alamat saya
              </button>
            </div>
          </div>
        </div>
      )}
    </FrontendLayout>
  );
}
