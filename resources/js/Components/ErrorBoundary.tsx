import { Component, type ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { AlertTriangle, Home, RefreshCw } from 'lucide-react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error?: Error;
}

export default class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(): State {
    return { hasError: true };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error('ErrorBoundary caught an error:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
          <div className="w-full max-w-md text-center">
            <div className="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white shadow-sm">
              <AlertTriangle className="h-12 w-12 text-rose-600" />
            </div>

            <h1 className="text-4xl font-black text-slate-900">Oops!</h1>
            <h2 className="mt-2 text-lg font-bold text-slate-800">Ralat pada Aplikasi</h2>
            <p className="mt-3 text-slate-500">
              Sesuatu yang tidak dijangka telah berlaku pada paparan. Sila muat semula halaman.
            </p>

            <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
              <button
                type="button"
                onClick={() => window.location.reload()}
                className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
              >
                <RefreshCw className="h-4 w-4" />
                Muat Semula
              </button>
              <Link
                href={route('home')}
                className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-brand-700"
              >
                <Home className="h-4 w-4" />
                Halaman Utama
              </Link>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
