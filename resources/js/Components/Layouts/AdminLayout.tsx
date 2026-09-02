import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import {
  LayoutDashboard, Package, Users, Receipt, Settings, Star, CreditCard, BarChart3, Megaphone,
  LogOut, Menu, ChevronRight, ChevronDown, Contact, Truck, Palette, Ruler, Tag, DollarSign, BadgePercent, Bell, Image, ExternalLink, FolderKanban, Search, MapPin, FileText, Database, Check, Copy, UserRound, Phone, X
} from 'lucide-react';
import { type PageProps } from '@/types';
import { cn } from '@/lib/utils';
import { FlashToasts } from '@/Components/FlashToasts';
import SeoHead from '@/Components/SeoHead';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';

type NavGroup = { label: string; icon?: never; route?: never; children: { label: string; icon: React.ComponentType<{ className?: string }>; route: string }[] };
type NavItem = { label: string; icon: React.ComponentType<{ className?: string }>; route: string };

type AddressSearchResult = {
  id: number;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  user: {
    id: number;
    name: string;
    email: string | null;
    no_tel: string | null;
  } | null;
};

const JNT_NORMAL_ORDER_URL = 'https://jtvip.jtexpress.my/#/orders/normalOrder';

function phoneForCopy(phone: string): string {
  let digits = phone.replace(/\D/g, '');

  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.startsWith('60')) {
    return digits.slice(2);
  }

  return digits.startsWith('0') ? digits.slice(1) : digits;
}

function phoneForJntSmartAddress(phone: string): string {
  let digits = phone.replace(/\D/g, '');

  if (digits === '') {
    return '';
  }

  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.startsWith('60')) {
    return `0${digits.slice(2)}`;
  }

  return digits.startsWith('0') ? digits : `0${digits}`;
}

function jntSmartAddressText(result: AddressSearchResult): string {
  const name = result.recipient_name?.trim() || result.user?.name.trim() || '';
  const phone = result.no_hp?.trim() || result.user?.no_tel?.trim() || '';

  return [name, phone ? phoneForJntSmartAddress(phone) : '', result.address.trim()]
    .filter(Boolean)
    .join(' ');
}

interface CopyableSearchValueProps {
  label: string;
  value: string;
  copied: boolean;
  icon: React.ComponentType<{ className?: string }>;
  onCopy: () => void;
  className?: string;
}

function CopyableSearchValue({ label, value, copied, icon: Icon, onCopy, className = '' }: CopyableSearchValueProps) {
  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <button
          type="button"
          onClick={onCopy}
          className={cn('group inline-flex max-w-full items-start gap-1.5 text-left transition hover:text-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-200', className)}
          aria-label={`Salin ${label.toLowerCase()} dalam huruf besar`}
        >
          <Icon className="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400 transition group-hover:text-brand-600" />
          <span className="min-w-0 break-words">{value}</span>
          {copied ? (
            <Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" aria-label="Berjaya disalin" />
          ) : (
            <Copy className="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-300 transition group-hover:text-brand-600" aria-hidden="true" />
          )}
        </button>
      </TooltipTrigger>
      <TooltipContent>Klik untuk salin {label.toLowerCase()} dalam huruf besar</TooltipContent>
    </Tooltip>
  );
}

/** Semak sama ada route semasa match pattern (termasuk wildcard). */
function isActiveRoute(routeName: string): boolean {
    if (route().current(routeName)) return true;
    // Untuk route induk seperti *.index / *.create / *.edit, aktifkan juga sub-page.
    const base = routeName.replace(/\.(index|create|edit)$/, '');
    if (base !== routeName) {
        return route().current(`${base}.*`) ?? false;
    }
    return false;
}

const navGroups: (NavGroup | NavItem)[] = [
  { label: 'Dashboard', icon: LayoutDashboard, route: 'admin.dashboard' },
  { label: 'Google Analytics', icon: BarChart3, route: 'admin.google-analytics.index' },
  { label: 'User Login', icon: UserRound, route: 'admin.user-login.index' },
  { label: 'Iklan Meta', icon: Megaphone, route: 'admin.meta-ads.index' },
  {
    label: 'Jualan', children: [
      { label: 'Orders', icon: Package, route: 'admin.orders.index' },
      { label: 'Customers', icon: Users, route: 'admin.customers.index' },
      { label: 'Customer Address', icon: MapPin, route: 'admin.customer-addresses.index' },
      { label: 'Invoices', icon: Receipt, route: 'admin.invoices.index' },
    ]
  },
  {
    label: 'Produk', children: [
      { label: 'Categories', icon: Tag, route: 'admin.categories.index' },
      { label: 'Designs', icon: Palette, route: 'admin.designs.index' },
      { label: 'Sizes', icon: Ruler, route: 'admin.sizes.index' },
      { label: 'Harga', icon: DollarSign, route: 'admin.price-settings.index' },
      { label: 'Diskaun', icon: BadgePercent, route: 'admin.discounts.index' },
      { label: 'Watermark', icon: Image, route: 'admin.watermark.index' },
      { label: 'Projects Customer', icon: FolderKanban, route: 'admin.projects.index' },
    ]
  },
  {
    label: 'Pengurusan', children: [
      { label: 'Testimoni', icon: Star, route: 'admin.testimonials.index' },
      { label: 'Dokumen Syarikat', icon: FileText, route: 'admin.company-documents.index' },
      { label: 'Contact', icon: Contact, route: 'admin.contacts.google.index' },
      { label: 'Ekstrak Contact', icon: Contact, route: 'admin.contacts.extract' },
    ]
  },
  {
    label: 'Settings', children: [
      { label: 'Bayaran', icon: CreditCard, route: 'admin.payment-settings.index' },
      { label: 'Profile', icon: Settings, route: 'admin.profile.edit' },
      { label: 'Password', icon: Settings, route: 'admin.password.edit' },
      { label: 'J&T Express', icon: Truck, route: 'admin.jnt.index' },
      { label: 'N8n Webhook', icon: Bell, route: 'admin.settings.n8n.edit' },
      { label: 'Under Construction', icon: Image, route: 'admin.settings.under-construction.edit' },
    ]
  },
];

/** Cari label halaman semasa berdasarkan route */
function useCurrentPageLabel(): string {
  for (const item of navGroups) {
    if ('children' in item) {
      const match = item.children.find((c) => isActiveRoute(c.route));
      if (match) return match.label;
    } else if (item.route && isActiveRoute(item.route)) {
      return item.label;
    }
  }
  return 'Panel Admin';
}

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [customerSearch, setCustomerSearch] = useState('');
  const [addressResults, setAddressResults] = useState<AddressSearchResult[]>([]);
  const [searchingAddresses, setSearchingAddresses] = useState(false);
  const [copiedSearchField, setCopiedSearchField] = useState<string | null>(null);
  const [searchOpen, setSearchOpen] = useState(false);
  const { auth, app, invoiceCounts, orderCounts, adminNotifications, testimonialCounts } = usePage<PageProps>().props;
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const customerSearchRef = useRef<HTMLDivElement>(null);
  const notificationRef = useRef<HTMLDivElement>(null);
  const pageLabel = useCurrentPageLabel();
  const notificationTotal = adminNotifications.reduce((total, notification) => total + notification.count, 0);
  const currentRouteName = route().current() ?? '';
  const isAdminFormRoute = currentRouteName.startsWith('admin.')
    && (currentRouteName.endsWith('.create') || currentRouteName.endsWith('.edit'));
  const formModalCloseRef = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (!isAdminFormRoute) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    formModalCloseRef.current?.focus();

    const handleEscape = (event: KeyboardEvent) => {
      if (event.key !== 'Escape') return;

      if (window.history.length > 1) {
        window.history.back();
      } else {
        router.visit(route('admin.dashboard'));
      }
    };

    window.addEventListener('keydown', handleEscape);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleEscape);
    };
  }, [isAdminFormRoute]);

  const initialOpenGroup = navGroups
    .filter((item): item is NavGroup => 'children' in item)
    .find((item) => item.children.some((c) => isActiveRoute(c.route)))?.label ?? null;
  const [openGroup, setOpenGroup] = useState<string | null>(initialOpenGroup);

  const toggleGroup = (label: string) => {
    setOpenGroup((prev) => prev === label ? null : label);
  };

  const handleCustomerSearch = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    router.get(route('admin.customers.index'), { q: customerSearch.trim() }, {
      preserveState: false,
      preserveScroll: false,
    });
  };

  const copySearchText = async (value: string, key: string) => {
    const uppercaseValue = value.toLocaleUpperCase('ms-MY');

    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(uppercaseValue);
      } else {
        throw new Error('Clipboard API tidak tersedia.');
      }
    } catch {
      const fallback = document.createElement('textarea');
      fallback.value = uppercaseValue;
      fallback.setAttribute('readonly', '');
      fallback.style.position = 'fixed';
      fallback.style.opacity = '0';
      document.body.appendChild(fallback);
      fallback.select();
      document.execCommand('copy');
      fallback.remove();
    }

    setCopiedSearchField(key);
    window.setTimeout(() => {
      setCopiedSearchField((current) => current === key ? null : current);
    }, 1400);
  };

  useEffect(() => {
    const query = customerSearch.trim();
    if (query.length < 2) {
      setAddressResults([]);
      setSearchingAddresses(false);
      return;
    }

    const controller = new AbortController();
    const timeout = window.setTimeout(async () => {
      setSearchingAddresses(true);

      try {
        const response = await fetch(`${route('admin.customers.search')}?q=${encodeURIComponent(query)}`, {
          headers: { Accept: 'application/json' },
          signal: controller.signal,
        });

        if (response.ok) {
          const payload = await response.json() as { results: AddressSearchResult[] };
          setAddressResults(payload.results);
        }
      } catch (error) {
        if ((error as DOMException).name !== 'AbortError') {
          setAddressResults([]);
        }
      } finally {
        if (!controller.signal.aborted) {
          setSearchingAddresses(false);
        }
      }
    }, 250);

    return () => {
      window.clearTimeout(timeout);
      controller.abort();
    };
  }, [customerSearch]);

  useEffect(() => {
    if (!searchOpen) return;

    const handlePointerDown = (event: PointerEvent) => {
      if (!customerSearchRef.current?.contains(event.target as Node)) {
        setSearchOpen(false);
      }
    };

    document.addEventListener('pointerdown', handlePointerDown);

    return () => document.removeEventListener('pointerdown', handlePointerDown);
  }, [searchOpen]);

  useEffect(() => {
    if (!notificationsOpen) return;

    const handlePointerDown = (event: PointerEvent) => {
      if (!notificationRef.current?.contains(event.target as Node)) {
        setNotificationsOpen(false);
      }
    };

    document.addEventListener('pointerdown', handlePointerDown);

    return () => document.removeEventListener('pointerdown', handlePointerDown);
  }, [notificationsOpen]);

  return (
    <div className="backstage-radial min-h-screen">
      <SeoHead />
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <button
          className="fixed inset-0 z-40 bg-black/50 lg:hidden"
          onClick={() => setSidebarOpen(false)}
          type="button"
          aria-label="Tutup sidebar"
        />
      )}

      <div className="admin-shell mx-auto flex min-h-screen max-w-[1600px] bg-slate-50">
        {/* Sidebar */}
        <aside
          className={cn(
            'fixed top-0 left-0 z-50 h-full w-60 bg-white border-r border-slate-200 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:z-30 lg:shrink-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
          )}
        >
        <div className="flex h-full flex-col">
          {/* Sidebar Header */}
          <Link href={route('home')} className="flex shrink-0 items-center gap-2.5 border-b border-slate-200 px-4 py-3.5 transition hover:opacity-80">
            <img src={app.logo_url} alt="StickerTermurah" className="h-9 w-9 rounded-full object-contain" />
            <div className="leading-tight">
              <p className="font-display text-sm font-bold text-slate-900">
                Sticker<span className="text-brand-600">Termurah</span>
              </p>
              <p className="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Panel Admin</p>
            </div>
          </Link>

          {/* Nav Links */}
          <nav className="flex-1 overflow-y-auto px-2.5 py-3 space-y-0.5">
            {navGroups.map((item) => {
              if ('children' in item) {
                const isOpen = openGroup === item.label;
                const hasActiveChild = item.children.some((c) => isActiveRoute(c.route));
                const hasBadge = item.children.some((child) => (
                  (child.route === 'admin.orders.index' && orderCounts.adminPending > 0)
                  || (child.route === 'admin.invoices.index' && invoiceCounts.adminPending > 0)
                  || (child.route === 'admin.testimonials.index' && testimonialCounts.adminPending > 0)
                ));
                return (
                  <div key={item.label} className="pt-3 first:pt-0">
                    <button
                      type="button"
                      onClick={() => toggleGroup(item.label)}
                      className={cn(
                        'flex w-full cursor-pointer items-center gap-2 rounded-lg px-3 py-1.5 text-left text-[11.5px] font-bold uppercase tracking-[0.16em] transition',
                        hasActiveChild ? 'text-brand-600' : 'text-slate-400 hover:text-brand-600'
                      )}
                    >
                      <span className="flex-1">{item.label}</span>
                      {hasBadge && (
                        <span role="img" aria-label="Ada kemas kini baharu" className="relative flex h-2 w-2 shrink-0">
                          <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75" />
                          <span className="relative inline-flex h-2 w-2 rounded-full bg-rose-500 shadow-[0_0_7px_rgba(244,63,94,0.8)]" />
                        </span>
                      )}
                      {isOpen ? <ChevronDown className="h-3 w-3" /> : <ChevronRight className="h-3 w-3" />}
                    </button>
                    <div className={cn('grid transition-all duration-300', isOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]')}>
                      <div className="overflow-hidden">
                        <div className="mt-0.5 space-y-0.5">
                          {item.children.map((child) => {
                            const active = isActiveRoute(child.route);
                            return (
                              <Link
                                key={child.route}
                                href={route(child.route)}
                                className={cn(
                                  'flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 pl-9 text-[13px] font-medium transition',
                                  active
                                    ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/25'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600'
                                )}
                              >
                                <child.icon className="h-3.5 w-3.5 shrink-0" />
                                <span className="min-w-0 flex-1">{child.label}</span>
                                {child.route === 'admin.orders.index' && orderCounts.adminPending > 0 && (
                                  <span className={cn(
                                    'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none',
                                    active ? 'bg-white text-brand-600' : 'bg-rose-100 text-rose-600'
                                  )}>
                                    {orderCounts.adminPending > 99 ? '99+' : orderCounts.adminPending}
                                  </span>
                                )}
                                {child.route === 'admin.invoices.index' && invoiceCounts.adminPending > 0 && (
                                  <span className={cn(
                                    'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none',
                                    active ? 'bg-white text-brand-600' : 'bg-rose-100 text-rose-600'
                                  )}>
                                    {invoiceCounts.adminPending > 99 ? '99+' : invoiceCounts.adminPending}
                                  </span>
                                )}
                                {child.route === 'admin.testimonials.index' && testimonialCounts.adminPending > 0 && (
                                  <span className={cn(
                                    'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none',
                                    active ? 'bg-white text-brand-600' : 'bg-rose-100 text-rose-600'
                                  )}>
                                    {testimonialCounts.adminPending > 99 ? '99+' : testimonialCounts.adminPending}
                                  </span>
                                )}
                              </Link>
                            );
                          })}
                        </div>
                      </div>
                    </div>
                  </div>
                );
              }

              const active = isActiveRoute(item.route);
              return (
                <Link
                  key={item.route}
                  href={route(item.route!)}
                  className={cn(
                    'flex cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium transition',
                    active
                      ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/25'
                      : 'text-slate-600 hover:bg-slate-50 hover:text-brand-600'
                  )}
                >
                  <item.icon className="h-4 w-4 shrink-0" />
                  <span>{item.label}</span>
                </Link>
              );
            })}
          </nav>

          {/* Sidebar Footer */}
          <div className="shrink-0 border-t border-slate-200 p-3">
            <div className="flex items-center gap-2.5 rounded-xl bg-slate-50 p-2.5">
              <div className="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-600 text-xs font-bold text-white">
                {auth.user?.avatar_url ? (
                  <img src={auth.user.avatar_url} alt={auth.user.name} className="h-full w-full object-cover" />
                ) : (
                  auth.user?.name?.charAt(0).toUpperCase() ?? 'A'
                )}
              </div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-[13px] font-semibold text-slate-900">{auth.user?.name}</p>
                <p className="truncate text-[11px] text-slate-500">{auth.user?.email}</p>
              </div>
            </div>
            <Link
              href={route('admin.logout')}
              method="post"
              as="button"
              type="button"
              className="mt-2 flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium text-slate-600 transition hover:bg-rose-50 hover:text-rose-600"
            >
              <LogOut className="h-4 w-4" />
              <span>Log Keluar</span>
            </Link>
          </div>
        </div>
      </aside>

      {/* Main Content */}
      <div className="flex min-w-0 flex-1 flex-col">
        {/* Admin Header */}
        <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-2.5 backdrop-blur-md lg:px-6">
          <div className="mx-auto flex max-w-[1400px] items-center justify-between gap-3">
            <div className="flex items-center gap-3">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                type="button"
                aria-label={sidebarOpen ? 'Tutup menu admin' : 'Buka menu admin'}
                className="cursor-pointer rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-50 hover:text-brand-600 lg:hidden"
              >
                <Menu className="h-5 w-5" />
              </button>
              <h1 className="hidden font-display text-base font-bold text-slate-900 sm:block">{pageLabel}</h1>
            </div>
            <form onSubmit={handleCustomerSearch} className="flex min-w-0 flex-1 max-w-xl items-center">
              <div ref={customerSearchRef} className="relative w-full">
                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  type="search"
                  value={customerSearch}
                  onChange={(event) => {
                    const value = event.target.value;
                    setCustomerSearch(value);
                    setSearchOpen(value.trim().length >= 2);
                  }}
                  onFocus={() => {
                    if (customerSearch.trim().length >= 2) {
                      setSearchOpen(true);
                    }
                  }}
                  placeholder="Cari nama atau no. HP customer..."
                  aria-label="Cari customer"
                  className="w-full rounded-full border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100"
                />
                {searchOpen && customerSearch.trim().length >= 2 && (
                  <div className="absolute left-0 right-0 top-full z-50 mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                    {searchingAddresses ? (
                      <p className="px-4 py-3 text-xs text-slate-500">Mencari alamat...</p>
                    ) : addressResults.length > 0 ? (
                      <div className="divide-y divide-slate-100">
                        {addressResults.map((result) => {
                          const resultKey = `search-address-${result.id}`;
                          const recipientName = result.recipient_name?.trim() ?? '';
                          const phone = result.no_hp?.trim() ?? '';
                          const userName = result.user?.name.trim() ?? '';
                          const userPhone = result.user?.no_tel?.trim() ?? '';

                          return (
                            <div key={result.id} className="px-4 py-3 transition hover:bg-slate-50">
                              <div className="flex items-start justify-between gap-3">
                                {recipientName ? (
                                  <CopyableSearchValue
                                    label="nama penerima"
                                    value={recipientName}
                                    icon={UserRound}
                                    copied={copiedSearchField === `${resultKey}-recipient`}
                                    onCopy={() => copySearchText(recipientName, `${resultKey}-recipient`)}
                                    className="text-sm font-semibold text-slate-900"
                                  />
                                ) : (
                                  <p className="text-sm font-semibold text-slate-900">Alamat tanpa nama</p>
                                )}
                                <span className={result.user ? 'shrink-0 text-[10px] font-semibold text-emerald-600' : 'shrink-0 text-[10px] font-semibold text-slate-400'}>
                                  {result.user ? 'Ada akaun' : 'Belum link'}
                                </span>
                              </div>

                              <div className="mt-1 flex min-w-0 flex-wrap items-start gap-x-1.5 gap-y-1 text-xs text-slate-500">
                                {phone ? (
                                  <CopyableSearchValue
                                    label="no. telefon"
                                    value={phone}
                                    icon={Phone}
                                    copied={copiedSearchField === `${resultKey}-phone`}
                                    onCopy={() => copySearchText(phoneForCopy(phone), `${resultKey}-phone`)}
                                    className="whitespace-nowrap text-slate-500"
                                  />
                                ) : (
                                  <span>Tiada no. HP</span>
                                )}
                                <span className="select-none">·</span>
                                <CopyableSearchValue
                                  label="alamat"
                                  value={result.address}
                                  icon={MapPin}
                                  copied={copiedSearchField === `${resultKey}-address`}
                                  onCopy={() => copySearchText(result.address, `${resultKey}-address`)}
                                  className="min-w-0 text-slate-500"
                                />
                              </div>

                              {result.user && (
                                <div className="mt-1 flex flex-wrap items-start gap-x-1.5 gap-y-1 text-xs font-medium text-brand-600">
                                  <span className="mt-0.5 shrink-0 text-slate-400">User:</span>
                                  {userName && (
                                    <CopyableSearchValue
                                      label="nama customer"
                                      value={userName}
                                      icon={UserRound}
                                      copied={copiedSearchField === `${resultKey}-user-name`}
                                      onCopy={() => copySearchText(userName, `${resultKey}-user-name`)}
                                      className="text-brand-600"
                                    />
                                  )}
                                  {userPhone && (
                                    <>
                                      <span className="mt-0.5 select-none text-slate-400">·</span>
                                      <CopyableSearchValue
                                        label="no. telefon customer"
                                        value={userPhone}
                                        icon={Phone}
                                        copied={copiedSearchField === `${resultKey}-user-phone`}
                                        onCopy={() => copySearchText(phoneForCopy(userPhone), `${resultKey}-user-phone`)}
                                        className="text-brand-600"
                                      />
                                    </>
                                  )}
                                </div>
                              )}

                              <div className="mt-2 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-2">
                                <button
                                  type="button"
                                  onClick={() => copySearchText(jntSmartAddressText(result), `${resultKey}-jnt`)}
                                  aria-label={`Salin maklumat ${userName || recipientName || 'penerima'} untuk borang J&T`}
                                  className="inline-flex items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-2 py-1 text-[11px] font-bold text-orange-700 transition hover:border-orange-300 hover:bg-orange-100"
                                >
                                  {copiedSearchField === `${resultKey}-jnt` ? (
                                    <Check className="h-3 w-3 text-emerald-500" aria-hidden="true" />
                                  ) : (
                                    <Truck className="h-3 w-3" aria-hidden="true" />
                                  )}
                                  {copiedSearchField === `${resultKey}-jnt` ? 'Disalin untuk J&T' : 'Salin J&T'}
                                </button>
                                {result.user && (
                                  <>
                                    <Link
                                      href={route('admin.orders.create', { user_id: result.user.id, address_id: result.id })}
                                      aria-label={`Cipta order untuk ${userName || recipientName}`}
                                      className="inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-brand-50 px-2 py-1 text-[11px] font-bold text-brand-700 transition hover:border-brand-300 hover:bg-brand-100"
                                    >
                                      <Package className="h-3 w-3" />
                                      Cipta Order
                                    </Link>
                                    <Link
                                      href={route('admin.invoices.manual.create', { user_id: result.user.id, address_id: result.id })}
                                      aria-label={`Cipta invoice untuk ${userName || recipientName}`}
                                      className="inline-flex items-center gap-1 rounded-lg border border-violet-200 bg-violet-50 px-2 py-1 text-[11px] font-bold text-violet-700 transition hover:border-violet-300 hover:bg-violet-100"
                                    >
                                      <Receipt className="h-3 w-3" />
                                      Cipta Invoice
                                    </Link>
                                  </>
                                )}
                              </div>
                              {copiedSearchField === `${resultKey}-jnt` && (
                                <p className="mt-1 text-[10px] leading-relaxed text-orange-700">
                                  Tampal pada Smart Address Filling J&T dan klik ikon geocoding. Goods Name: STICKER, Actual Weight: 0.5 kg, Service Type: DROP OFF.
                                </p>
                              )}
                            </div>
                          );
                        })}
                      </div>
                    ) : (
                      <p className="px-4 py-3 text-xs text-slate-500">Alamat customer tidak ditemui.</p>
                    )}
                  </div>
                )}
              </div>
            </form>
            <div className="flex shrink-0 items-center gap-2">
              <div ref={notificationRef} className="relative">
                <Tooltip>
                  <TooltipTrigger asChild>
                    <button
                      type="button"
                      aria-label={`Notifikasi admin${notificationTotal > 0 ? `, ${notificationTotal > 99 ? '99+' : notificationTotal} tindakan` : ''}`}
                      aria-haspopup="menu"
                      aria-expanded={notificationsOpen}
                      onClick={() => setNotificationsOpen((open) => !open)}
                      className={cn(
                        'relative inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border transition',
                        notificationsOpen
                          ? 'border-brand-200 bg-brand-50 text-brand-700'
                          : 'border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                      )}
                    >
                      <Bell className="h-4 w-4" />
                      {notificationTotal > 0 && (
                        <span className="absolute -right-1 -top-1 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 py-0.5 text-[9px] font-bold leading-none text-white">
                          {notificationTotal > 99 ? '99+' : notificationTotal}
                        </span>
                      )}
                    </button>
                  </TooltipTrigger>
                  <TooltipContent>Notifikasi admin</TooltipContent>
                </Tooltip>

                {notificationsOpen && (
                  <div className="absolute right-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10" role="menu">
                    <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                      <p className="text-sm font-bold text-slate-900">Notifikasi</p>
                      {notificationTotal > 0 && <span className="text-[11px] font-semibold text-slate-400">{notificationTotal} tindakan</span>}
                    </div>
                    {adminNotifications.length > 0 ? (
                      <div className="divide-y divide-slate-100">
                        {adminNotifications.map((notification) => (
                          <Link
                            key={notification.key}
                            href={notification.href}
                            role="menuitem"
                            onClick={() => setNotificationsOpen(false)}
                            className="flex items-center gap-3 px-4 py-3 transition hover:bg-slate-50"
                          >
                            <span className="inline-flex min-w-7 items-center justify-center rounded-full bg-rose-100 px-1.5 py-1 text-[10px] font-bold text-rose-600">
                              {notification.count > 99 ? '99+' : notification.count}
                            </span>
                            <span className="min-w-0 flex-1 text-sm font-semibold text-slate-700">{notification.label}</span>
                            <ChevronRight className="h-4 w-4 shrink-0 text-slate-300" />
                          </Link>
                        ))}
                      </div>
                    ) : (
                      <p className="px-4 py-6 text-center text-sm text-slate-500">Tiada tindakan diperlukan buat masa ini.</p>
                    )}
                  </div>
                )}
              </div>
              <a
                href={route('admin.database.backup')}
                aria-label="Backup database"
                className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-3.5 py-1.5 text-xs font-semibold text-violet-700 transition hover:border-violet-300 hover:bg-violet-100"
              >
                <Database className="h-3.5 w-3.5" />
                <span className="hidden sm:inline">Backup DB</span>
              </a>
              <a
                href={route('admin.contacts.extract')}
                aria-label="Extract Contact"
                className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 px-3.5 py-1.5 text-xs font-semibold text-brand-700 transition hover:border-brand-300 hover:bg-brand-100"
              >
                <Contact className="h-3.5 w-3.5" />
                <span className="hidden sm:inline">Extract Contact</span>
              </a>
              <a
                href={route('home')}
                target="_blank"
                rel="noreferrer"
                aria-label="Lihat laman utama"
                className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:text-brand-600"
              >
                <ExternalLink className="h-3.5 w-3.5" />
                <span className="hidden sm:inline">Lihat Laman</span>
              </a>
              <a
                href={JNT_NORMAL_ORDER_URL}
                target="_blank"
                rel="noreferrer"
                aria-label="Buka borang order J&T"
                className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-orange-200 bg-orange-50 px-3.5 py-1.5 text-xs font-semibold text-orange-700 transition hover:border-orange-300 hover:bg-orange-100"
              >
                <Truck className="h-3.5 w-3.5" />
                <span className="hidden sm:inline">Web J&T</span>
              </a>
            </div>
          </div>
        </header>

        <main key={route().current() ?? 'unknown'} className={cn('animate-page-enter min-w-0 flex-1', isAdminFormRoute ? 'p-0' : 'p-4 lg:p-6')}>
          <FlashToasts />
          {isAdminFormRoute ? (
            <div className="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto bg-slate-950/65 p-3 backdrop-blur-sm sm:p-6" role="presentation">
              <div className="relative my-auto w-full max-w-[1500px] overflow-hidden rounded-[2rem] border border-white/70 bg-slate-50 shadow-2xl shadow-slate-950/25" role="dialog" aria-modal="true" aria-label="Borang admin">
                <button
                  ref={formModalCloseRef}
                  type="button"
                  onClick={() => {
                    if (window.history.length > 1) {
                      window.history.back();
                    } else {
                      router.visit(route('admin.dashboard'));
                    }
                  }}
                  aria-label="Tutup borang"
                  className="absolute right-4 top-4 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700"
                >
                  <X className="h-4 w-4" />
                </button>
                <div className="max-h-[calc(100dvh-1.5rem)] overflow-y-auto sm:max-h-[calc(100dvh-3rem)]">
                  <div className="mx-auto max-w-[1400px] p-4 lg:p-6">{children}</div>
                </div>
              </div>
            </div>
          ) : (
            <div className="mx-auto max-w-[1400px]">{children}</div>
          )}
        </main>
      </div>
    </div>
    </div>
  );
}
