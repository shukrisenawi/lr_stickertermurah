import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Menu, X, LogOut, LayoutDashboard, Package, Star, Home } from 'lucide-react';
import { type PageProps } from '@/types';
import { FlashToasts } from '@/Components/FlashToasts';

export default function MemberLayout({ children }: { children: React.ReactNode }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { auth, flash, app } = usePage<PageProps>().props;

  const navItems = [
    { label: 'Home', href: route('home'), icon: Home, active: route().current('home') },
    { label: 'Dashboard', href: route('member.dashboard'), icon: LayoutDashboard, active: route().current('member.dashboard') },
    { label: 'Order Saya', href: route('member.orders.index'), icon: Package, active: route().current('member.orders.*') },
    { label: 'Testimoni', href: route('member.testimonials.index'), icon: Star, active: route().current('member.testimonials.*') },
  ];

  return (
    <div className="min-h-full bg-slate-50 text-slate-900 antialiased">
      {/* Header */}
      <header className="sticky top-0 z-50 border-b border-slate-100 bg-white/90 backdrop-blur-md">
        <div className="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-4 lg:px-8">
          <Link href="/" className="flex shrink-0 items-center gap-2.5">
            <img src={app.logo_url} alt="StickerTermurah" className="h-10 w-10 rounded-full object-contain" />
            <span className="hidden font-display text-lg font-bold tracking-tight text-slate-900 sm:block">
              Sticker<span className="text-brand-600">Termurah</span>
            </span>
          </Link>

          {/* Desktop Nav */}
          <nav className="hidden items-center gap-1 rounded-full border border-slate-100 bg-slate-50 p-1 lg:flex">
            {navItems.map((item) => (
              <Link
                key={item.label}
                href={item.href}
                className={`cursor-pointer rounded-full px-4 py-1.5 text-[13px] font-semibold transition ${
                  item.active
                    ? 'bg-white text-brand-600 shadow-sm'
                    : 'text-slate-600 hover:text-brand-600'
                }`}
              >
                {item.label}
              </Link>
            ))}
          </nav>

          <div className="flex items-center gap-2.5">
            <div className="hidden items-center gap-2 md:flex">
              <div className="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">
                {auth.user?.name?.charAt(0).toUpperCase() ?? 'U'}
              </div>
              <span className="max-w-[140px] truncate text-[13px] font-semibold text-slate-700">
                {auth.user?.name}
              </span>
            </div>
            <Link
              href={route('member.logout')}
              method="post"
              as="button"
              type="button"
              className="hidden items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:border-rose-200 hover:text-rose-600 sm:inline-flex"
            >
              <LogOut className="h-3.5 w-3.5" />
              Log Keluar
            </Link>
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              type="button"
              className="rounded-lg border border-slate-200 p-2 text-slate-600 lg:hidden"
              aria-label="Menu"
            >
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {mobileMenuOpen && (
          <div className="border-t border-slate-100 bg-white px-4 py-3 lg:hidden">
            <div className="space-y-1">
              {navItems.map((item) => (
                <Link
                  key={item.label}
                  href={item.href}
                  onClick={() => setMobileMenuOpen(false)}
                  className={`flex cursor-pointer items-center gap-2.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition ${
                    item.active ? 'bg-brand-50 text-brand-600' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-600'
                  }`}
                >
                  <item.icon className="h-4 w-4" />
                  {item.label}
                </Link>
              ))}
              <Link
                href={route('member.logout')}
                method="post"
                as="button"
                type="button"
                onClick={() => setMobileMenuOpen(false)}
                className="mt-1 flex w-full items-center gap-2.5 rounded-xl px-4 py-2.5 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50"
              >
                <LogOut className="h-4 w-4" />
                Log Keluar
              </Link>
            </div>
          </div>
        )}
      </header>

      <main key={route().current() ?? 'unknown'} className="animate-page-enter">
        <FlashToasts />
          <div className="mx-auto max-w-[1200px] px-4 py-5 lg:px-8 lg:py-6">
          {flash.success && (
            <div className="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
              <p className="text-sm font-medium text-emerald-800">{flash.success}</p>
            </div>
          )}
          {flash.error && (
            <div className="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
              <p className="text-sm font-medium text-rose-800">{flash.error}</p>
            </div>
          )}
          {children}
        </div>
      </main>
    </div>
  );
}
