import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import { ToastProvider } from '@/Components/Toast';
import ErrorBoundary from '@/Components/ErrorBoundary';
import { useEffect, useRef } from 'react';

declare module 'react' {
  interface CSSProperties {
    [key: `--${string}`]: string | number;
  }
}

const appName = 'StickerTermurah';

function isProtectedHistoryUrl(href: string): boolean {
    const pathname = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';

    return pathname === '/admin'
        || pathname.startsWith('/admin/')
        || pathname === '/ahli'
        || pathname.startsWith('/ahli/');
}

function BrowserHistoryGuard() {
    const reloadQueued = useRef(false);
    const historyNavigationPending = useRef(false);
    const skipPersistedPageShow = useRef(false);

    useEffect(() => {
        let timer: number | undefined;
        let pageShowResetTimer: number | undefined;

        const reloadProtectedPage = () => {
            timer = undefined;

            if (reloadQueued.current || !isProtectedHistoryUrl(window.location.href)) {
                return;
            }

            reloadQueued.current = true;
            router.reload({
                onFinish: () => {
                    reloadQueued.current = false;
                },
            });
        };

        const scheduleReload = () => {
            if (timer !== undefined) {
                window.clearTimeout(timer);
            }

            timer = window.setTimeout(reloadProtectedPage, 0);
        };

        const handlePopState = () => {
            historyNavigationPending.current = true;
            skipPersistedPageShow.current = true;

            if (pageShowResetTimer !== undefined) {
                window.clearTimeout(pageShowResetTimer);
            }

            pageShowResetTimer = window.setTimeout(() => {
                skipPersistedPageShow.current = false;
                pageShowResetTimer = undefined;
            }, 1000);
        };
        const removeNavigateListener = router.on('navigate', (event) => {
            if (! historyNavigationPending.current) {
                return;
            }

            historyNavigationPending.current = false;

            if (isProtectedHistoryUrl(event.detail.page.url)) {
                scheduleReload();
            }
        });
        const handlePageShow = (event: PageTransitionEvent) => {
            if (!event.persisted) {
                return;
            }

            if (skipPersistedPageShow.current) {
                skipPersistedPageShow.current = false;

                if (pageShowResetTimer !== undefined) {
                    window.clearTimeout(pageShowResetTimer);
                    pageShowResetTimer = undefined;
                }

                return;
            }

            scheduleReload();
        };

        window.addEventListener('popstate', handlePopState);
        window.addEventListener('pageshow', handlePageShow);

        return () => {
            removeNavigateListener();
            window.removeEventListener('popstate', handlePopState);
            window.removeEventListener('pageshow', handlePageShow);

            if (timer !== undefined) {
                window.clearTimeout(timer);
            }

            if (pageShowResetTimer !== undefined) {
                window.clearTimeout(pageShowResetTimer);
            }
        };
    }, []);

    return null;
}

createInertiaApp({
    title: (title) => {
        const normalizedTitle = title?.trim();

        if (!normalizedTitle || normalizedTitle === appName) {
            return appName;
        }

        return normalizedTitle.endsWith(`| ${appName}`)
            ? normalizedTitle
            : `${normalizedTitle} | ${appName}`;
    },
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.tsx') as Record<
            string,
            () => Promise<{ default: React.ComponentType }>
        >;
        const page = pages[`./Pages/${name}.tsx`];
        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }
        return page() as any;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <ErrorBoundary>
                <ToastProvider>
                    <BrowserHistoryGuard />
                    <App {...props} />
                </ToastProvider>
            </ErrorBoundary>
        );
    },
    progress: {
        color: '#d91c5c',
    },
});
