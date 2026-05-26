import { Link } from '@inertiajs/react';
import {
  AlertTriangle,
  Home,
  ArrowLeft,
  Shield,
  Search,
} from 'lucide-react';

interface ErrorPageProps {
  status: number;
  message?: string;
}

const errorConfig: Record<number, { icon: typeof AlertTriangle; title: string; description: string; color: string }> = {
  403: {
    icon: Shield,
    title: 'Akses Dilarang',
    description: 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.',
    color: 'text-amber-600',
  },
  404: {
    icon: Search,
    title: 'Halaman Tidak Dijumpai',
    description: 'Halaman yang anda cari mungkin telah dipindahkan atau tidak wujud.',
    color: 'text-sky-600',
  },
  419: {
    icon: Shield,
    title: 'Ses Tamat',
    description: 'Sesi anda telah tamat. Sila muat semula halaman dan cuba lagi.',
    color: 'text-amber-600',
  },
  429: {
    icon: AlertTriangle,
    title: 'Terlalu Banyak Permintaan',
    description: 'Anda telah membuat terlalu banyak permintaan. Sila tunggu seketika.',
    color: 'text-orange-600',
  },
  500: {
    icon: AlertTriangle,
    title: 'Ralat Pelayan',
    description: 'Sesuatu telah berlaku pada pelayan kami. Sila cuba lagi kemudian.',
    color: 'text-rose-600',
  },
  503: {
    icon: AlertTriangle,
    title: 'Perkhidmatan Tidak Tersedia',
    description: 'Pelayan sedang dalam penyelenggaraan. Sila cuba lagi kemudian.',
    color: 'text-rose-600',
  },
};

export default function ErrorPage({ status, message }: ErrorPageProps) {
  const config = errorConfig[status] ?? {
    icon: AlertTriangle,
    title: 'Ralat Berlaku',
    description: message || 'Sesuatu yang tidak dijangka telah berlaku.',
    color: 'text-slate-600',
  };

  const Icon = config.icon;

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-md text-center">
        <div className={`mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-white shadow-sm`}>
          <Icon className={`h-12 w-12 ${config.color}`} />
        </div>

        <h1 className="text-6xl font-black text-slate-900">{status}</h1>
        <h2 className="mt-2 text-xl font-bold text-slate-800">{config.title}</h2>
        <p className="mt-3 text-slate-500">{config.description}</p>

        <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
          <button
            type="button"
            onClick={() => window.history.back()}
            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          >
            <ArrowLeft className="h-4 w-4" />
            Kembali
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
