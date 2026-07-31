import { Link, router, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';

const WHATSAPP_LINK = 'https://wa.me/601169409606';

function WhatsAppIcon({ className = 'h-4 w-4' }: { className?: string }) {
    return (
        <svg className={className} fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.004 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
        </svg>
    );
}

interface PublicHeaderProps {
    active?: 'design' | 'cara-tempah' | 'harga' | 'testimoni';
    showTestimoni?: boolean;
}

export default function PublicHeader({ active, showTestimoni = false }: PublicHeaderProps) {
    const { app } = usePage<PageProps>().props;

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
        ...(showTestimoni
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
                                className={`text-sm font-semibold transition hover:text-brand-600 ${
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
                                className={`text-sm font-semibold transition hover:text-brand-600 ${
                                    active === item.key ? 'text-brand-600' : 'text-slate-600'
                                }`}
                            >
                                {item.label}
                            </a>
                        ),
                    )}
                </nav>
                <div className="flex items-center gap-2">
                    <Link
                        href={route('member.login')}
                        className="hidden rounded-full border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-brand-200 hover:text-brand-600 active:scale-[0.97] sm:inline-flex sm:px-5 sm:text-sm"
                    >
                        Log Masuk
                    </Link>
                    <a
                        href={WHATSAPP_LINK}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600 active:scale-[0.97] sm:px-5 sm:text-sm"
                    >
                        <WhatsAppIcon className="h-4 w-4" />
                        WhatsApp Kami
                    </a>
                </div>
            </div>
        </header>
    );
}
