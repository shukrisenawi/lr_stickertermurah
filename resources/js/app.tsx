import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { ToastProvider } from '@/Components/Toast';
import ErrorBoundary from '@/Components/ErrorBoundary';

declare module 'react' {
  interface CSSProperties {
    [key: `--${string}`]: string | number;
  }
}

const appName = 'StickerTermurah';

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
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
                    <App {...props} />
                </ToastProvider>
            </ErrorBoundary>
        );
    },
    progress: {
        color: '#d91c5c',
    },
});
