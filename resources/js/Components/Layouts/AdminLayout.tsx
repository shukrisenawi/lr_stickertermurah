import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
  LayoutDashboard, Package, Users, Receipt, Settings,
  LogOut, Menu, ChevronRight, Contact, Truck, Palette, Ruler, Tag
} from 'lucide-react';
import { type PageProps } from '@/types';
import { cn } from '@/lib/utils';
import { FlashToasts } from '@/Components/FlashToasts';

const navItems = [
  { label: 'Dashboard', icon: LayoutDashboard, route: 'admin.dashboard' },
  { label: 'Orders', icon: Package, route: 'admin.orders.index' },
  { label: 'Customers', icon: Users, route: 'admin.customers.index' },
  { label: 'Invoices', icon: Receipt, route: 'admin.invoices.create' },
  { label: 'Categories', icon: Tag, route: 'admin.categories.index' },
  { label: 'Designs', icon: Palette, route: 'admin.designs.index' },
  { label: 'Sizes', icon: Ruler, route: 'admin.sizes.index' },
  { label: 'J&T Express', icon: Truck, route: 'admin.jnt.index' },
  { label: 'Contacts', icon: Contact, route: 'admin.contacts.extract.index' },
  { label: 'Profile', icon: Settings, route: 'admin.profile.edit' },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { auth } = usePage<PageProps>().props;

  return (
    <div className="admin-shell flex min-h-screen bg-slate-50">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <button
          className="fixed inset-0 z-40 bg-black/30 lg:hidden"
          onClick={() => setSidebarOpen(false)}
          type="button"
          aria-label="Tutup sidebar"
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          'fixed top-0 left-0 z-50 h-full w-64 bg-white border-r border-slate-200 transition-transform duration-300 lg:translate-x-0 lg:static lg:z-auto',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        <div className="flex h-full flex-col">
          {/* Sidebar Header */}
          <div className="flex items-center gap-3 border-b border-slate-200 px-6 py-4">
            <img src="/images/logo-baru.png" alt="StickerTermurah" className="h-10 w-auto" />
            <span className="text-lg font-bold text-slate-900">Admin</span>
          </div>

          {/* Nav Links */}
          <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            {navItems.map((item) => {
              const isActive = (route as any).current(item.route + '*');
              return (
                <Link
                  key={item.route}
                  href={route(item.route)}
                  className={cn(
                    'flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition',
                    isActive
                      ? 'bg-brand-50 text-brand-700'
                      : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                  )}
                >
                  <item.icon className="h-5 w-5 shrink-0" />
                  <span>{item.label}</span>
                  <ChevronRight className="ml-auto h-4 w-4 opacity-0" />
                </Link>
              );
            })}
          </nav>

          {/* Sidebar Footer */}
          <div className="border-t border-slate-200 p-4">
            <div className="flex items-center gap-3 rounded-xl bg-slate-50 p-3">
              <div className="h-9 w-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 text-sm font-bold">
                {auth.user?.name?.charAt(0).toUpperCase() ?? 'A'}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-slate-900 truncate">{auth.user?.name}</p>
                <p className="text-xs text-slate-500 truncate">{auth.user?.email}</p>
              </div>
            </div>
            <Link
              href={route('admin.logout')}
              method="post"
              as="button"
              type="button"
              className="mt-3 flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600"
            >
              <LogOut className="h-5 w-5" />
              <span>Log Keluar</span>
            </Link>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Admin Header */}
        <header className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 py-3 lg:px-8">
          <div className="flex items-center justify-between">
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              type="button"
              className="rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden hover:bg-slate-50"
            >
              <Menu className="h-5 w-5" />
            </button>
            <h1 className="text-lg font-bold text-slate-900">{document.title.split(' | ')[0]}</h1>
            <div className="w-10" />
          </div>
        </header>

        <main className="flex-1 p-4 lg:p-8">
          <FlashToasts />
          {children}
        </main>
      </div>
    </div>
  );
}
