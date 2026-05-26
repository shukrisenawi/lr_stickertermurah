import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Menu, X, LogOut, User } from 'lucide-react';
import { type PageProps } from '@/types';

export default function MemberLayout({ children }: { children: React.ReactNode }) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { auth, flash } = usePage<PageProps>().props;

  return (
    <div className="min-h-full bg-white text-slate-900 antialiased">
      {/* Header */}
      <header className="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100">
        <div className="mx-auto flex h-[72px] max-w-[1280px] items-center justify-between px-4 lg:px-8">
          <Link href="/" className="flex items-center gap-2 shrink-0">
            <img src="/images/logo-baru.png" alt="StickerTermurah" className="h-12 w-auto" />
          </Link>

          <nav className="hidden lg:flex items-center gap-8">
            <Link href={route('home')} className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Home</Link>
            <Link href={route('member.dashboard')} className="text-sm font-semibold text-brand-600">Dashboard</Link>
            <Link href={route('member.orders.index')} className="text-sm font-medium text-slate-600 hover:text-brand-600 transition">Order Saya</Link>
          </nav>

          <div className="flex items-center gap-3">
            <div className="hidden md:flex items-center gap-2 text-sm text-slate-600">
              <User className="h-4 w-4" />
              <span className="font-medium">{auth.user?.name}</span>
            </div>
            <Link
              href={route('member.logout')}
              method="post"
              as="button"
              type="button"
              className="hidden sm:inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition"
            >
              <LogOut className="h-4 w-4" />
              Log Keluar
            </Link>
            <button onClick={() => setMobileMenuOpen(!mobileMenuOpen)} type="button" className="lg:hidden rounded-xl border border-slate-200 p-2 text-slate-600">
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </button>
          </div>
        </div>

        {mobileMenuOpen && (
          <div className="border-t border-slate-100 bg-white px-4 py-4 lg:hidden">
            <div className="space-y-1">
              <Link href={route('home')} onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Home</Link>
              <Link href={route('member.dashboard')} onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-semibold text-brand-600 bg-brand-50">Dashboard</Link>
              <Link href={route('member.orders.index')} onClick={() => setMobileMenuOpen(false)} className="block rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">Order Saya</Link>
              <Link
                href={route('member.logout')}
                method="post"
                as="button"
                type="button"
                onClick={() => setMobileMenuOpen(false)}
                className="mt-2 block w-full text-left rounded-xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50"
              >
                Log Keluar
              </Link>
            </div>
          </div>
        )}
      </header>

      <main>
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
    </div>
  );
}
