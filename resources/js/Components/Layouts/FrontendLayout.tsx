import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Menu, X } from 'lucide-react';
import { type PageProps } from '@/types';
import { FlashToasts } from '@/Components/FlashToasts';

export default function FrontendLayout({ children }: { children: React.ReactNode }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { flash, app } = usePage<PageProps>().props;

  return (
    <div className="min-h-full bg-white text-slate-900 antialiased">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div className="mx-auto flex h-[72px] max-w-[1280px] items-center justify-between px-4 lg:px-8">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2 shrink-0">
            <img src={app.logo_url} alt="StickerTermurah" className="h-12 w-auto" />
          </Link>

          {/* Desktop Nav */}
          <nav className="hidden lg:flex items-center gap-8">
            <Link href="/" className="text-sm font-semibold text-brand-600">Home</Link>
            <Link href="/#pilih-design" className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Pilih Design</Link>
            <Link href="/#harga" className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Harga</Link>
            <Link href="/#testimoni" className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Testimoni</Link>
            <Link href="/#cara-tempah" className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Tentang Kami</Link>
            <Link href="/#hubungi-kami" className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Hubungi Kami</Link>
            <Link href={route('member.login')} className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Log Masuk</Link>
          </nav>

          {/* CTA & Mobile Toggle */}
          <div className="flex items-center gap-3">
            <a href="https://wa.me/601169409606" target="_blank" rel="noreferrer" className="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition shadow-lg shadow-brand-600/20">
              011-69409606
            </a>
            <button onClick={() => setMobileMenuOpen(!mobileMenuOpen)} type="button" className="lg:hidden rounded-xl border border-slate-200 p-2 text-slate-600">
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <div className="border-t border-slate-100 bg-white px-4 py-4 lg:hidden">
            <div className="space-y-1">
              <Link href="/" className="block rounded-xl px-4 py-3 text-sm font-semibold text-brand-600 bg-brand-50">Home</Link>
              <Link href="/#pilih-design" onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Pilih Design</Link>
              <Link href="/#harga" onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Harga</Link>
              <Link href="/#testimoni" onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Testimoni</Link>
              <Link href="/#cara-tempah" onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Tentang Kami</Link>
              <Link href="/#hubungi-kami" onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Hubungi Kami</Link>
              <Link href={route('member.login')} onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Log Masuk</Link>
            </div>
          </div>
        )}
      </header>

      {/* Flash Messages */}
      <main>
        <FlashToasts />
        {flash.success && (
          <div className="mx-auto max-w-[1280px] px-4 pt-6 lg:px-8">
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
              <p className="text-sm font-medium text-emerald-800">{flash.success}</p>
            </div>
          </div>
        )}
        {flash.error && (
          <div className="mx-auto max-w-[1280px] px-4 pt-6 lg:px-8">
            <div className="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
              <p className="text-sm font-medium text-rose-800">{flash.error}</p>
            </div>
          </div>
        )}
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-[#0f172a] text-white">
        <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
          <div className="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div>
              <div className="flex items-center gap-3">
                <img src={app.logo_url} alt="StickerTermurah" className="h-14 w-auto" />
                <div>
                  <p className="text-lg font-bold text-white">StickerTermurah</p>
                  <p className="text-xs text-slate-400">Printing Studio</p>
                </div>
              </div>
              <p className="mt-4 text-sm leading-relaxed text-slate-400">
                Kami hanya pakar dalam cetakan sticker mirrorcote berkualiti tinggi untuk jenama, produk & perniagaan anda.
              </p>
            </div>
            <div>
              <h2 className="mb-4 text-sm font-bold text-white">Pautan Pantas</h2>
              <ul className="space-y-3 text-sm text-slate-400">
                <li><Link href="/" className="hover:text-brand-400 transition">Home</Link></li>
                <li><Link href="/#pilih-design" className="hover:text-brand-400 transition">Pilih Design</Link></li>
                <li><Link href="/#harga" className="hover:text-brand-400 transition">Harga</Link></li>
              </ul>
            </div>
            <div>
              <h2 className="mb-4 text-sm font-bold text-white">Maklumat</h2>
              <ul className="space-y-3 text-sm text-slate-400">
                <li><Link href="/#cara-tempah" className="hover:text-brand-400 transition">Tentang Kami</Link></li>
                <li><Link href="/#cara-tempah" className="hover:text-brand-400 transition">Cara Tempah</Link></li>
              </ul>
            </div>
            <div>
              <h2 className="mb-4 text-sm font-bold text-white">Hubungi Kami</h2>
              <ul className="space-y-3 text-sm text-slate-400">
                <li>011-69409606</li>
                <li>stickertermurah@gmail.com</li>
                <li>Seluruh Malaysia</li>
              </ul>
            </div>
          </div>
          <div className="mt-10 border-t border-slate-800 pt-6 text-center text-xs text-slate-400">
            &copy; {new Date().getFullYear()} StickerTermurah. All rights reserved.
          </div>
        </div>
      </footer>
    </div>
  );
}
