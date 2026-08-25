import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, FileText, ShieldCheck } from 'lucide-react';

interface LegalDocumentProps {
  title: string;
  eyebrow: string;
  description: string;
  updatedAt: string;
  icon?: 'privacy' | 'terms';
  children: React.ReactNode;
}

export function LegalSection({ id, title, children }: { id: string; title: string; children: React.ReactNode }) {
  return (
    <section id={id} className="scroll-mt-28 border-b border-slate-100 pb-8 last:border-0 last:pb-0">
      <h2 className="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{title}</h2>
      <div className="mt-4 space-y-4 text-sm leading-7 text-slate-600">{children}</div>
    </section>
  );
}

export default function LegalDocument({ title, eyebrow, description, updatedAt, icon = 'privacy', children }: LegalDocumentProps) {
  const Icon = icon === 'privacy' ? ShieldCheck : FileText;

  return (
    <FrontendLayout hideNavbar>
      <Head title={title} />
      <PublicHeader />
      <div className="min-h-[calc(100vh-72px)] bg-slate-50">
        <div className="mx-auto max-w-[1120px] px-4 py-8 sm:py-12 lg:px-8 lg:py-16">
          <Link href={route('home')} className="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 transition hover:text-brand-700">
            <ArrowLeft className="h-4 w-4" />
            Kembali ke laman utama
          </Link>

          <div className="mt-8 grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <aside className="lg:sticky lg:top-24">
              <div className="rounded-3xl bg-slate-900 p-6 text-white shadow-xl shadow-slate-900/10 sm:p-8">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-500/15 text-brand-300">
                  <Icon className="h-6 w-6" />
                </div>
                <p className="mt-6 text-xs font-bold uppercase tracking-[0.2em] text-brand-300">{eyebrow}</p>
                <h1 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{title}</h1>
                <p className="mt-4 text-sm leading-7 text-slate-300">{description}</p>
                <div className="mt-8 border-t border-white/10 pt-5 text-xs leading-6 text-slate-400">
                  Tarikh kemas kini<br />
                  <span className="font-semibold text-slate-200">{updatedAt}</span>
                </div>
              </div>

              <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-5 text-sm text-slate-600 shadow-sm">
                <p className="font-semibold text-slate-900">Dokumen berkaitan</p>
                <div className="mt-3 flex flex-wrap gap-x-4 gap-y-2">
                  <Link href={route('privacy-policy')} className="font-medium text-brand-600 hover:text-brand-700">
                    Polisi Privasi
                  </Link>
                  <Link href={route('terms-of-service')} className="font-medium text-brand-600 hover:text-brand-700">
                    Terma Perkhidmatan
                  </Link>
                </div>
              </div>
            </aside>

            <article className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-9 lg:p-10">
              <div className="space-y-8">{children}</div>
            </article>
          </div>
        </div>
      </div>
    </FrontendLayout>
  );
}
