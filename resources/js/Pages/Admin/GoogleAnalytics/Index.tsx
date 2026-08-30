import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
  Activity,
  ArrowLeft,
  BarChart3,
  CheckCircle2,
  CircleAlert,
  Cloud,
  ExternalLink,
  Gauge,
  KeyRound,
  Lightbulb,
  Radio,
  ShieldCheck,
  Terminal,
  Workflow,
} from 'lucide-react';

interface GoogleAnalyticsConfiguration {
  measurementId: string | null;
  propertyId: string | null;
  projectConfigured: boolean;
  credentialsConfigured: boolean;
}

interface GoogleAnalyticsProps {
  configuration: GoogleAnalyticsConfiguration;
}

interface StatusRowProps {
  label: string;
  value: string;
  copy: string;
  ready: boolean;
}

interface ResourceCardProps {
  title: string;
  copy: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
}

const claudeCommand = 'claude mcp add analytics-mcp --scope user -e "GOOGLE_APPLICATION_CREDENTIALS=PATH_TO_CREDENTIALS_JSON" -e "GOOGLE_PROJECT_ID=YOUR_PROJECT_ID" -- pipx run analytics-mcp';

function StatusRow({ label, value, copy, ready }: StatusRowProps) {
  const Icon = ready ? CheckCircle2 : CircleAlert;

  return (
    <div className="flex items-start gap-3 border-b border-slate-100 py-4 last:border-b-0 last:pb-0 first:pt-0">
      <Icon className={`mt-0.5 h-4 w-4 shrink-0 ${ready ? 'text-emerald-500' : 'text-amber-500'}`} />
      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
          <p className="text-sm font-semibold text-slate-800">{label}</p>
          <p className={`break-all text-sm font-bold ${ready ? 'text-emerald-700' : 'text-amber-700'}`}>{value}</p>
        </div>
        <p className="mt-1 text-xs leading-5 text-slate-500">{copy}</p>
      </div>
    </div>
  );
}

function ResourceCard({ title, copy, href, icon: Icon }: ResourceCardProps) {
  return (
    <a
      href={href}
      target="_blank"
      rel="noreferrer"
      className="group flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-sm"
    >
      <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
        <Icon className="h-4 w-4" />
      </div>
      <div className="min-w-0 flex-1">
        <p className="text-sm font-bold text-slate-900">{title}</p>
        <p className="mt-1 text-xs leading-5 text-slate-500">{copy}</p>
      </div>
      <ExternalLink className="mt-1 h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-brand-500" />
    </a>
  );
}

export default function GoogleAnalytics({ configuration }: GoogleAnalyticsProps) {
  const reportingReady = Boolean(
    configuration.propertyId && configuration.projectConfigured && configuration.credentialsConfigured,
  );

  const capabilities = [
    { label: 'Akaun & property', copy: 'Cari akaun dan property GA4 yang boleh diakses.', icon: BarChart3 },
    { label: 'Laporan teras', copy: 'Bandingkan pengguna, sesi, event dan sumber trafik.', icon: Activity },
    { label: 'Realtime', copy: 'Semak pengguna aktif dan event yang sedang berlaku.', icon: Radio },
    { label: 'Funnel & dimensi custom', copy: 'Teliti laluan pengguna serta metadata property.', icon: Workflow },
  ];

  const prompts = [
    'Bandingkan pengguna, sesi, dan event utama 30 hari ini dengan 30 hari sebelumnya.',
    'Apakah landing page yang paling banyak membawa sesi dan engagement?',
    'Tunjukkan sumber trafik terbaik untuk tempahan sticker dan cadangkan fokus pemasaran.',
    'Berapa pengguna aktif sekarang dan event realtime yang sedang berlaku?',
  ];

  return (
    <AdminLayout>
      <Head title="Google Analytics" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Google Analytics</h2>
            <p className="admin-page-copy">Pusat semakan tracking laman dan panduan analisis GA4 menggunakan MCP.</p>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            <Link href={route('admin.dashboard')} className="admin-btn-secondary text-sm">
              <ArrowLeft className="h-4 w-4" />
              Dashboard
            </Link>
            <a
              href="https://analytics.google.com/analytics/web/"
              target="_blank"
              rel="noreferrer"
              className="admin-btn-primary text-sm"
            >
              Buka Google Analytics
              <ExternalLink className="h-4 w-4" />
            </a>
          </div>
        </div>

        <div className="grid gap-3 md:grid-cols-3">
          <div className="admin-kpi-card">
            <div className="flex items-center gap-3">
              <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${configuration.measurementId ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'}`}>
                <Gauge className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="admin-kpi-value">{configuration.measurementId ? 'Aktif' : 'Belum'}</p>
                <p className="truncate text-xs font-medium text-slate-500">Tracking laman</p>
              </div>
            </div>
            <p className="mt-3 break-all text-xs text-slate-500">{configuration.measurementId ?? 'Measurement ID belum diisi.'}</p>
          </div>

          <div className="admin-kpi-card">
            <div className="flex items-center gap-3">
              <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${configuration.propertyId ? 'bg-brand-50 text-brand-600' : 'bg-amber-50 text-amber-600'}`}>
                <BarChart3 className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="mt-1.5 break-all text-xl font-bold tracking-tight text-slate-900">{configuration.propertyId ?? 'Belum set'}</p>
                <p className="truncate text-xs font-medium text-slate-500">GA4 Property ID</p>
              </div>
            </div>
            <p className="mt-3 text-xs text-slate-500">Digunakan untuk laporan property melalui MCP.</p>
          </div>

          <div className="admin-kpi-card">
            <div className="flex items-center gap-3">
              <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${reportingReady ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'}`}>
                <Terminal className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="admin-kpi-value">STDIO</p>
                <p className="truncate text-xs font-medium text-slate-500">Analytics MCP</p>
              </div>
            </div>
            <p className="mt-3 text-xs leading-5 text-slate-500">Dijalankan secara tempatan melalui Claude atau Gemini, bukan dalam browser.</p>
          </div>
        </div>

        <div className="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.65fr)]">
          <section className="admin-flat-card overflow-hidden">
            <div className="admin-card-header">
              <div className="flex items-center gap-2.5">
                <div className="admin-icon-badge">
                  <ShieldCheck className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900">Status integrasi</h3>
                  <p className="text-xs text-slate-500">Semakan konfigurasi tanpa mendedahkan credential.</p>
                </div>
              </div>
              <span className={`hidden rounded-full border px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] sm:inline-flex ${reportingReady ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-amber-100 bg-amber-50 text-amber-700'}`}>
                {reportingReady ? 'Sedia laporan' : 'Perlu setup'}
              </span>
            </div>
            <div className="p-5 sm:p-6">
              <StatusRow
                label="Measurement ID"
                value={configuration.measurementId ?? 'Belum diisi'}
                copy="Menentukan tracking gtag.js untuk laman web."
                ready={Boolean(configuration.measurementId)}
              />
              <StatusRow
                label="GA4 Property ID"
                value={configuration.propertyId ?? 'Belum diisi'}
                copy="Property yang digunakan untuk pertanyaan laporan GA4."
                ready={Boolean(configuration.propertyId)}
              />
              <StatusRow
                label="Google Cloud project"
                value={configuration.projectConfigured ? 'Disediakan' : 'Belum disediakan'}
                copy="MCP rasmi menggunakan GOOGLE_PROJECT_ID ketika dijalankan."
                ready={configuration.projectConfigured}
              />
              <StatusRow
                label="Application Default Credentials"
                value={configuration.credentialsConfigured ? 'Disediakan' : 'Belum disediakan'}
                copy="Credential read-only diperlukan untuk mengakses Google Analytics API."
                ready={configuration.credentialsConfigured}
              />
            </div>
          </section>

          <section className="admin-flat-card overflow-hidden">
            <div className="admin-card-header">
              <div className="flex items-center gap-2.5">
                <div className="admin-icon-badge">
                  <ExternalLink className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900">Pautan penting</h3>
                  <p className="text-xs text-slate-500">Sumber rasmi untuk setup dan semakan.</p>
                </div>
              </div>
            </div>
            <div className="space-y-3 p-5 sm:p-6">
              <ResourceCard
                title="Google Analytics"
                copy="Lihat laporan lengkap dan konfigurasi property."
                href="https://analytics.google.com/analytics/web/"
                icon={BarChart3}
              />
              <ResourceCard
                title="Google Analytics Data API"
                copy="Aktifkan Data API untuk laporan, realtime dan metrik GA4."
                href="https://console.cloud.google.com/apis/library/analyticsdata.googleapis.com"
                icon={Cloud}
              />
              <ResourceCard
                title="Google Analytics Admin API"
                copy="Aktifkan Admin API untuk akaun, property dan metadata GA4."
                href="https://console.cloud.google.com/apis/library/analyticsadmin.googleapis.com"
                icon={ShieldCheck}
              />
              <ResourceCard
                title="Google Analytics MCP"
                copy="Kod sumber, release dan setup rasmi daripada Google Analytics."
                href="https://github.com/googleanalytics/google-analytics-mcp"
                icon={Terminal}
              />
            </div>
          </section>
        </div>

        <div className="grid gap-6 lg:grid-cols-2">
          <section className="admin-flat-card overflow-hidden">
            <div className="admin-card-header">
              <div className="flex items-center gap-2.5">
                <div className="admin-icon-badge">
                  <Lightbulb className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900">Apa yang MCP boleh bantu</h3>
                  <p className="text-xs text-slate-500">Analisis bahasa semula jadi untuk keputusan pemasaran.</p>
                </div>
              </div>
            </div>
            <div className="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
              {capabilities.map((capability) => (
                <div key={capability.label} className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <capability.icon className="h-4 w-4 text-brand-600" />
                  <p className="mt-3 text-sm font-bold text-slate-900">{capability.label}</p>
                  <p className="mt-1 text-xs leading-5 text-slate-500">{capability.copy}</p>
                </div>
              ))}
            </div>
          </section>

          <section className="admin-flat-card overflow-hidden">
            <div className="admin-card-header">
              <div className="flex items-center gap-2.5">
                <div className="admin-icon-badge">
                  <KeyRound className="h-4 w-4" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-slate-900">Setup ringkas</h3>
                  <p className="text-xs text-slate-500">MCP rasmi membaca credential daripada komputer AI.</p>
                </div>
              </div>
            </div>
            <div className="space-y-4 p-5 sm:p-6">
              <ol className="space-y-3 text-sm text-slate-600">
                <li className="flex gap-3"><span className="font-bold text-brand-600">1.</span><span>Aktifkan Google Analytics Admin API dan Data API.</span></li>
                <li className="flex gap-3"><span className="font-bold text-brand-600">2.</span><span>Login ADC dengan scope <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-700">analytics.readonly</code>.</span></li>
                <li className="flex gap-3"><span className="font-bold text-brand-600">3.</span><span>Daftarkan server MCP dalam Claude atau Gemini.</span></li>
              </ol>
              <pre className="overflow-x-auto rounded-2xl bg-slate-950 p-4 text-[11px] leading-5 text-slate-200"><code>{claudeCommand}</code></pre>
              <p className="text-xs leading-5 text-slate-500">Simpan nilai konfigurasi dalam `.env` atau environment mesin. Jangan letakkan fail credential JSON dalam git.</p>
            </div>
          </section>
        </div>

        <section className="admin-flat-card overflow-hidden">
          <div className="admin-card-header">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <Activity className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Contoh soalan analisis</h3>
                <p className="text-xs text-slate-500">Gunakan prompt ini selepas `analytics-mcp` disambungkan kepada AI client.</p>
              </div>
            </div>
          </div>
          <div className="grid gap-3 p-5 md:grid-cols-2 sm:p-6">
            {prompts.map((prompt) => (
              <div key={prompt} className="rounded-2xl border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-700">
                &quot;{prompt}&quot;
              </div>
            ))}
          </div>
        </section>

        <div className="flex items-start gap-3 rounded-2xl border border-brand-100 bg-brand-50/70 p-4 text-sm text-brand-900">
          <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
          <div>
            <p className="font-bold">Nota integrasi</p>
            <p className="mt-1 leading-6 text-brand-800">MCP Google Analytics ini ialah server tempatan berasaskan STDIO. Menu ini membantu setup dan navigasi, tetapi tidak memanggil MCP dari browser atau memaparkan laporan live dalam Laravel.</p>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
