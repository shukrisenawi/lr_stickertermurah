import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head } from '@inertiajs/react';

export default function Home() {
  return (
    <FrontendLayout>
      <Head title="Home" />
      <div className="frontend-shell">
        {/* Hero Section */}
        <section className="relative overflow-hidden pt-16 pb-24 lg:pt-24 lg:pb-32">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-center">
              <div className="max-w-xl">
                <div className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 shadow-sm">
                  <span className="h-2 w-2 rounded-full bg-brand-600 animate-pulse" />
                  Printing Studio Malaysia
                </div>
                <h1 className="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 lg:text-5xl">
                  Sticker Mirrorcote <span className="text-brand-600">Berkualiti</span>
                </h1>
                <p className="mt-6 text-lg leading-relaxed text-slate-600">
                  Cetakan sticker mirrorcote premium untuk jenama, produk & perniagaan anda. Harga termurah di Malaysia dengan kualiti terbaik.
                </p>
                <div className="mt-8 flex flex-wrap items-center gap-4">
                  <a
                    href={route('orders.create')}
                    className="frontend-btn-primary"
                  >
                    Tempah Sekarang
                  </a>
                  <a
                    href="https://wa.me/601169409606"
                    target="_blank"
                    rel="noreferrer"
                    className="frontend-btn-secondary"
                  >
                    WhatsApp Kami
                  </a>
                </div>
              </div>
              <div className="relative">
                <div className="relative rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5">
                  <img
                    src="/images/logo-baru.png"
                    alt="StickerTermurah Products"
                    className="w-full rounded-[2rem] object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Features / Cara Tempah */}
        <section id="cara-tempah" className="py-20">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="text-center">
              <div className="frontend-section-head justify-center">
                <div className="frontend-section-accent" />
                <h2 className="frontend-title">Cara Tempah</h2>
              </div>
              <p className="frontend-copy">Hanya 3 langkah mudah untuk tempah sticker anda</p>
            </div>
            <div className="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
              {[
                { step: '01', title: 'Pilih Design', desc: 'Pilih daripada pelbagai design sticker yang kami sediakan atau hantar design sendiri.' },
                { step: '02', title: 'Pilih Saiz & Kuantiti', desc: 'Pilih saiz sticker dan masukkan kuantiti yang anda perlukan.' },
                { step: '03', title: 'Bayar & Terima', desc: 'Buat pembayaran dan kami akan hantar sticker terus ke alamat anda.' },
              ].map((item) => (
                <div key={item.step} className="frontend-flat-card p-8 text-center">
                  <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 text-xl font-bold">
                    {item.step}
                  </div>
                  <h3 className="mt-5 text-lg font-bold text-slate-900">{item.title}</h3>
                  <p className="mt-2 text-sm leading-relaxed text-slate-500">{item.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="py-20">
          <div className="mx-auto max-w-[1280px] px-4 lg:px-8">
            <div className="relative overflow-hidden rounded-[2.5rem] bg-brand-600 px-8 py-16 text-center lg:px-16">
              <h2 className="text-3xl font-extrabold text-white lg:text-4xl">
                Sedia untuk Tempah?
              </h2>
              <p className="mt-4 text-brand-100">
                Hubungi kami sekarang untuk konsultasi percuma dan quotation.
              </p>
              <div className="mt-8 flex flex-wrap justify-center gap-4">
                <a href={route('orders.create')} className="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-600 transition hover:bg-brand-50">
                  Tempah Sekarang
                </a>
                <a href="https://wa.me/601169409606" target="_blank" rel="noreferrer" className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/20">
                  WhatsApp
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>
    </FrontendLayout>
  );
}
