import { Link, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import { Menu, X, LogOut, LayoutDashboard } from 'lucide-react';
import { type PageProps } from '@/types';
import { FlashToasts } from '@/Components/FlashToasts';

function isHashLink(href: string): boolean {
  return href.startsWith('/#');
}

function scrollToHash(e: React.MouseEvent, href: string) {
  e.preventDefault();
  const id = href.replace('/#', '');
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } else {
    router.visit(href);
  }
}

interface FrontendLayoutProps {
  children: React.ReactNode;
  hideNavbar?: boolean;
}

export default function FrontendLayout({ children, hideNavbar }: FrontendLayoutProps) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { flash, app, auth } = usePage<PageProps>().props;
  const isLoggedIn = !!auth.user;

  const navItems = [
    { label: 'Home', href: '/' },
    { label: 'Pilih Design', href: '/#pilih-design' },
    { label: 'Harga', href: '/harga' },
    { label: 'Testimoni', href: '/#testimoni' },
    { label: 'Hubungi Kami', href: '/#hubungi-kami' },
  ];

  return (
    <div className="min-h-full bg-white text-slate-900 antialiased">
      {/* Header */}
      {!hideNavbar && <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div className="mx-auto flex h-[72px] max-w-[1280px] items-center justify-between px-4 lg:px-8">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2 shrink-0">
            <img src={app.logo_url} alt="StickerTermurah" className="h-12 w-auto" />
          </Link>

          {/* Desktop Nav */}
          <nav className="hidden lg:flex items-center gap-6">
            {navItems.map((item) =>
              isHashLink(item.href) ? (
                <a
                  key={item.label}
                  href={item.href}
                  onClick={(e) => scrollToHash(e, item.href)}
                  className="text-sm font-medium text-slate-600 hover:text-brand-600 transition"
                >
                  {item.label}
                </a>
              ) : (
                <Link
                  key={item.label}
                  href={item.href}
                  className="text-sm font-medium text-slate-600 hover:text-brand-600 transition"
                >
                  {item.label}
                </Link>
              )
            )}
            {isLoggedIn ? (
              <>
                <Link href={route('member.dashboard')} className="text-sm font-medium text-brand-600 hover:text-brand-700 transition flex items-center gap-1.5">
                  <LayoutDashboard className="h-4 w-4" />
                  Dashboard
                </Link>
                <Link
                  href={route('member.logout')}
                  method="post"
                  as="button"
                  type="button"
                  className="text-sm font-medium text-slate-600 hover:text-rose-600 transition flex items-center gap-1.5"
                >
                  <LogOut className="h-4 w-4" />
                  Log Keluar
                </Link>
              </>
            ) : (
              <Link href={route('member.login')} className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">
                Log Masuk
              </Link>
            )}
          </nav>

          {/* CTA & Mobile Toggle */}
          <div className="flex items-center gap-3">
            <a
              href="https://wa.me/601169409606"
              target="_blank"
              rel="noreferrer"
              className="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition shadow-lg shadow-brand-600/20"
            >
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              011-69409606
            </a>
            <button
              type="button"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="lg:hidden rounded-xl border border-slate-200 p-2 text-slate-600"
            >
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <div className="border-t border-slate-100 bg-white px-4 py-4 lg:hidden">
            <div className="space-y-1">
              {navItems.map((item) =>
                isHashLink(item.href) ? (
                  <a
                    key={item.label}
                    href={item.href}
                    onClick={(e) => { scrollToHash(e, item.href); setMobileMenuOpen(false); }}
                    className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                  >
                    {item.label}
                  </a>
                ) : (
                  <Link
                    key={item.label}
                    href={item.href}
                    onClick={() => setMobileMenuOpen(false)}
                    className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                  >
                    {item.label}
                  </Link>
                )
              )}
              {isLoggedIn ? (
                <>
                  <Link
                    href={route('member.dashboard')}
                    onClick={() => setMobileMenuOpen(false)}
                    className="block rounded-xl px-4 py-3 text-sm font-semibold text-brand-600 bg-brand-50"
                  >
                    Dashboard
                  </Link>
                  <Link
                    href={route('member.logout')}
                    method="post"
                    as="button"
                    type="button"
                    onClick={() => setMobileMenuOpen(false)}
                    className="block w-full text-left rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                  >
                    Log Keluar
                  </Link>
                </>
              ) : (
                <Link
                  href={route('member.login')}
                  onClick={() => setMobileMenuOpen(false)}
                  className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                  Log Masuk
                </Link>
              )}
            </div>
          </div>
        )}
      </header>}

      {/* Flash Messages */}
      <main key={route().current() ?? 'unknown'} className="animate-page-enter">
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
      <footer className="bg-[#0a0f1c] text-white">
        <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
          <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {/* Brand */}
            <div>
              <div className="flex items-center gap-3">
                <img src={app.logo_url} alt="StickerTermurah" className="h-14 w-auto" />
              </div>
              <p className="mt-4 text-sm leading-relaxed text-slate-400">
                Kami hanya pakar dalam cetakan sticker mirrorcote berkualiti tinggi untuk jenama, produk & perniagaan anda.
              </p>
              <div className="mt-4 flex items-center gap-3">
                {/* Facebook */}
                <a href="#" className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-400 transition hover:bg-brand-600 hover:text-white">
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                  </svg>
                </a>
                {/* Instagram */}
                <a href="#" className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-400 transition hover:bg-brand-600 hover:text-white">
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                  </svg>
                </a>
                {/* TikTok */}
                <a href="#" className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-400 transition hover:bg-brand-600 hover:text-white">
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z" />
                  </svg>
                </a>
                {/* WhatsApp */}
                <a
                  href="https://wa.me/601169409606"
                  target="_blank"
                  rel="noreferrer"
                  className="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-400 transition hover:bg-emerald-500 hover:text-white"
                >
                  <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.004 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                  </svg>
                </a>
              </div>
            </div>

            {/* Pautan Pantas */}
            <div>
              <h3 className="mb-4 text-sm font-bold text-white">Pautan Pantas</h3>
              <ul className="space-y-3 text-sm text-slate-400">
                <li><Link href="/" className="hover:text-brand-400 transition">Home</Link></li>
                <li><a href="/#pilih-design" onClick={(e) => scrollToHash(e, '/#pilih-design')} className="hover:text-brand-400 transition">Pilih Design</a></li>
                <li><a href="/#pilih-design" onClick={(e) => scrollToHash(e, '/#pilih-design')} className="hover:text-brand-400 transition">Produk</a></li>
                <li><Link href="/harga" className="hover:text-brand-400 transition">Harga</Link></li>
              </ul>
            </div>

            {/* Maklumat */}
            <div>
              <h3 className="mb-4 text-sm font-bold text-white">Maklumat</h3>
              <ul className="space-y-3 text-sm text-slate-400">
                <li><a href="/#cara-tempah" onClick={(e) => scrollToHash(e, '/#cara-tempah')} className="hover:text-brand-400 transition">Tentang Kami</a></li>
                <li><a href="/#testimoni" onClick={(e) => scrollToHash(e, '/#testimoni')} className="hover:text-brand-400 transition">Testimoni</a></li>
                <li><a href="/#cara-tempah" onClick={(e) => scrollToHash(e, '/#cara-tempah')} className="hover:text-brand-400 transition">Cara Tempah</a></li>
                <li><Link href="/" className="hover:text-brand-400 transition">Soalan Lazim</Link></li>
              </ul>
            </div>

            {/* Hubungi Kami */}
            <div>
              <h3 className="mb-4 text-sm font-bold text-white">Hubungi Kami</h3>
              <ul className="space-y-3 text-sm text-slate-400">
                <li className="flex items-center gap-2">
                  <svg className="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  011-69409606
                </li>
                <li className="flex items-center gap-2">
                  <svg className="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  stickertermurah@gmail.com
                </li>
                <li className="flex items-center gap-2">
                  <svg className="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Seluruh Malaysia
                </li>
                <li className="flex items-center gap-2">
                  <svg className="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  </svg>
                  Pos laju / Courier
                </li>
              </ul>
            </div>
          </div>

          <div className="mt-10 border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
            &copy; {new Date().getFullYear()} StickerTermurah. All rights reserved.
          </div>
        </div>
      </footer>
    </div>
  );
}
