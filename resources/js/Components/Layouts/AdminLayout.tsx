import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
  LayoutDashboard, Package, Users, Receipt, Settings, Star, CreditCard,
  LogOut, Menu, ChevronRight, ChevronDown, Contact, Truck, Palette, Ruler, Tag, DollarSign, BadgePercent, Bell
} from 'lucide-react';
import { type PageProps } from '@/types';
import { cn } from '@/lib/utils';
import { FlashToasts } from '@/Components/FlashToasts';

type NavGroup = { label: string; icon?: never; route?: never; children: { label: string; icon: React.ComponentType<{ className?: string }>; route: string }[] };
type NavItem = { label: string; icon: React.ComponentType<{ className?: string }>; route: string };

const navGroups: (NavGroup | NavItem)[] = [
  { label: 'Dashboard', icon: LayoutDashboard, route: 'admin.dashboard' },
  {
    label: 'Jualan', children: [
      { label: 'Orders', icon: Package, route: 'admin.orders.index' },
      { label: 'Customers', icon: Users, route: 'admin.customers.index' },
      { label: 'Invoices', icon: Receipt, route: 'admin.invoices.create' },
    ]
  },
  {
    label: 'Produk', children: [
      { label: 'Categories', icon: Tag, route: 'admin.categories.index' },
      { label: 'Designs', icon: Palette, route: 'admin.designs.index' },
      { label: 'Sizes', icon: Ruler, route: 'admin.sizes.index' },
      { label: 'Harga', icon: DollarSign, route: 'admin.price-settings.index' },
      { label: 'Diskaun', icon: BadgePercent, route: 'admin.discounts.index' },
    ]
  },
  {
    label: 'Pengurusan', children: [
      { label: 'Testimoni', icon: Star, route: 'admin.testimonials.index' },
      { label: 'Bayaran', icon: CreditCard, route: 'admin.payment-settings.index' },
      { label: 'J&T Express', icon: Truck, route: 'admin.jnt.index' },
      { label: 'Contacts', icon: Contact, route: 'admin.contacts.extract' },
      { label: 'Profile', icon: Settings, route: 'admin.profile.edit' },
      { label: 'N8n Webhook', icon: Bell, route: 'admin.settings.n8n.edit' },
    ]
  },
];

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { auth, app } = usePage<PageProps>().props;

  const initialOpenGroups: Record<string, boolean> = {};
  for (const item of navGroups) {
    if ('children' in item && item.children.some((c) => route().current(c.route + '*'))) {
      initialOpenGroups[item.label] = true;
    }
  }
  const [openGroups, setOpenGroups] = useState<Record<string, boolean>>(initialOpenGroups);

  const toggleGroup = (label: string) => {
    setOpenGroups((prev) => ({ ...prev, [label]: !prev[label] }));
  };

  return (
    <div className="admin-shell min-h-screen bg-slate-50">
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
          'fixed top-0 left-0 z-50 h-full w-64 bg-white border-r border-slate-200 transition-transform duration-300 lg:z-30',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        )}
      >
        <div className="flex h-full flex-col">
          {/* Sidebar Header */}
          <div className="flex shrink-0 items-center gap-3 border-b border-slate-200 px-6 py-4">
            <img src={app.logo_url} alt="StickerTermurah" className="h-10 w-auto" />
            <span className="text-lg font-bold text-slate-900">Admin</span>
          </div>

          {/* Nav Links */}
          <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            {navGroups.map((item) => {
              if ('children' in item) {
                const isOpen = openGroups[item.label] ?? false;
                const hasActiveChild = item.children.some((c) => route().current(c.route + '*'));
                return (
                  <div key={item.label} className="pt-4 first:pt-0">
                    <button
                      type="button"
                      onClick={() => toggleGroup(item.label)}
                      className={cn(
                        'flex w-full items-center gap-2 rounded-xl px-4 py-2 text-left text-xs font-semibold uppercase tracking-widest transition',
                        hasActiveChild ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600'
                      )}
                    >
                      <span className="flex-1">{item.label}</span>
                      {isOpen ? <ChevronDown className="h-3.5 w-3.5" /> : <ChevronRight className="h-3.5 w-3.5" />}
                    </button>
                    {isOpen && (
                      <div className="mt-0.5 space-y-0.5">
                        {item.children.map((child) => {
                          const active = route().current(child.route + '*');
                          return (
                            <Link
                              key={child.route}
                              href={route(child.route)}
                              className={cn(
                                'flex items-center gap-3 rounded-xl px-4 py-2 text-sm font-medium transition pl-10',
                                active
                                  ? 'bg-brand-50 text-brand-700'
                                  : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                              )}
                            >
                              <child.icon className="h-4 w-4 shrink-0" />
                              <span>{child.label}</span>
                            </Link>
                          );
                        })}
                      </div>
                    )}
                  </div>
                );
              }

              const active = route().current(item.route + '*');
              return (
                <Link
                  key={item.route}
                  href={route(item.route!)}
                  className={cn(
                    'flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition',
                    active
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
          <div className="shrink-0 border-t border-slate-200 p-4">
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
      <div className="lg:pl-64">
        {/* Admin Header */}
        <header className="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 py-3 lg:px-8">
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

        <main className="p-4 lg:p-8">
          <FlashToasts />
          {children}
        </main>
      </div>
    </div>
  );
}
