import { Link, router, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { LayoutDashboard, ShoppingCart } from 'lucide-react';
import { useEffect, useState } from 'react';

interface PublicHeaderProps {
    active?: 'design' | 'cara-tempah' | 'harga' | 'testimoni';
    showTestimoni?: boolean;
}

function useAutoActive(): PublicHeaderProps['active'] {
    const page = usePage<PageProps>();
    const currentRoute = route().current();
    const [hash, setHash] = useState(() => typeof window !== 'undefined' ? window.location.hash : '');

    useEffect(() => {
        const onHashChange = () => setHash(window.location.hash);
        window.addEventListener('hashchange', onHashChange);
        return () => window.removeEventListener('hashchange', onHashChange);
    }, []);

    if (currentRoute === 'price.checker') return 'harga';
    if (currentRoute === 'testimonials.index') return 'testimoni';
    if (currentRoute === 'home' || page.url === '/') {
        if (hash === '#cara-tempah') return 'cara-tempah';
        if (hash === '#testimoni') return 'testimoni';
        return 'design';
    }

    return undefined;
}

export default function PublicHeader({ active: activeProp, showTestimoni = false }: PublicHeaderProps) {
    const autoActive = useAutoActive();
    const { app, auth, testimonialCounts } = usePage<PageProps>().props;
    const isLoggedIn = !!auth.user;
    const isAdmin = auth.user?.is_admin ?? false;
    const dashboardRoute = isAdmin ? 'admin.dashboard' : 'member.dashboard';
    const active = activeProp ?? autoActive;
    const shouldShowTestimoni = showTestimoni || testimonialCounts.approved > 0;

    const goAnchor = (e: React.MouseEvent, id: string) => {
        e.preventDefault();
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            router.visit(`/#${id}`);
        }
    };

    const navItems = [
        { key: 'design', label: 'Design', onClick: (e: React.MouseEvent) => goAnchor(e, 'pilih-design') },
        { key: 'cara-tempah', label: 'Cara Tempah', onClick: (e: React.MouseEvent) => goAnchor(e, 'cara-tempah') },
        { key: 'harga', label: 'Harga', href: '/harga' },
        ...(shouldShowTestimoni
            ? [{ key: 'testimoni', label: 'Testimoni', onClick: (e: React.MouseEvent) => goAnchor(e, 'testimoni') }]
            : []),
    ];

    return (
        <header className="sticky top-0 z-50 border-b border-slate-100 bg-white/90 backdrop-blur-md">
            <div className="mx-auto flex h-16 max-w-[1400px] items-center justify-between px-4 lg:px-8">
                <Link href="/" className="flex shrink-0 items-center gap-2.5">
                    <img
                        src={app.logo_url}
                        alt="Logo StickerTermurah"
                        className="h-11 w-11 rounded-full object-contain"
                    />
                    <span className="hidden font-display text-lg font-bold tracking-tight text-slate-900 sm:block">
                        Sticker<span className="text-brand-600">Termurah</span>
                    </span>
                </Link>
                <nav className="hidden items-center gap-6 md:flex">
                    {navItems.map((item) =>
                        item.href ? (
                            <Link
                                key={item.key}
                                href={item.href}
                                className={`cursor-pointer text-sm font-semibold transition hover:text-brand-600 ${
                                    active === item.key ? 'text-brand-600' : 'text-slate-600'
                                }`}
                            >
                                {item.label}
                            </Link>
                        ) : (
                            <a
                                key={item.key}
                                href={`/#${item.key === 'design' ? 'pilih-design' : item.key}`}
                                onClick={item.onClick}
                                className={`cursor-pointer text-sm font-semibold transition hover:text-brand-600 ${
                                    active === item.key ? 'text-brand-600' : 'text-slate-600'
                                }`}
                            >
                                {item.label}
                            </a>
                        ),
                    )}
                </nav>
                <div className="flex items-center gap-2">
                    {isLoggedIn ? (
                        <Link
                            href={route(dashboardRoute)}
                            className="hidden cursor-pointer items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 px-4 py-2.5 text-xs font-bold text-brand-700 shadow-sm transition hover:bg-brand-100 sm:inline-flex sm:px-5 sm:text-sm"
                        >
                            <LayoutDashboard className="h-4 w-4" />
                            Dashboard
                        </Link>
                    ) : (
                        <Link
                            href={route('member.login')}
                            className="hidden cursor-pointer rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-brand-200 hover:text-brand-600 active:scale-[0.97] sm:inline-flex sm:px-5 sm:text-sm"
                        >
                            Login
                        </Link>
                    )}
                    <Link
                        href={route('orders.create')}
                        className="inline-flex items-center gap-2 rounded-full bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 active:scale-[0.97] sm:px-5 sm:text-sm"
                    >
                        <ShoppingCart className="h-4 w-4" />
                        Tempah Sekarang
                    </Link>
                </div>
            </div>
        </header>
    );
}
