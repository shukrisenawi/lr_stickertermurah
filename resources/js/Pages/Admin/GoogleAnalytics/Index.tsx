import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
  Activity,
  ArrowLeft,
  BarChart3,
  CalendarDays,
  CheckCircle2,
  CircleAlert,
  Cloud,
  ExternalLink,
  Eye,
  Gauge,
  KeyRound,
  Lightbulb,
  MousePointerClick,
  Radio,
  RefreshCw,
  ShieldCheck,
  Terminal,
  UserPlus,
  Users,
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
  report: GoogleAnalyticsReport | null;
  reportError: string | null;
}

interface GoogleAnalyticsReport {
  generatedAt: string;
  dateRange: {
    start: string;
    end: string;
  };
  summary: {
    activeUsers: number;
    newUsers: number;
    sessions: number;
    engagedSessions: number;
    eventCount: number;
    screenPageViews: number;
  };
  trend: Array<{
    date: string;
    activeUsers: number;
    sessions: number;
    screenPageViews: number;
  }>;
  topPages: Array<{
    title: string;
    path: string;
    screenPageViews: number;
    activeUsers: number;
  }>;
  topSources: Array<{
    channel: string;
    sessions: number;
    activeUsers: number;
  }>;
  realtimeActiveUsers: number;
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
const serverEnvExample = `GOOGLE_ANALYTICS_PROPERTY_ID=YOUR_PROPERTY_ID
GOOGLE_PROJECT_ID=YOUR_PROJECT_ID
GOOGLE_APPLICATION_CREDENTIALS=/path/to/analytics.json`;

function formatReportNumber(value: number): string {
  return value.toLocaleString('ms-MY');
}

function formatReportDate(value: string): string {
  const date = new Date(`${value}T00:00:00`);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short' }).format(date);
}

function formatReportTimestamp(value: string): string {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return 'Baru sahaja';
  }

  return new Intl.DateTimeFormat('ms-MY', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

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

interface ReportMetricCardProps {
  label: string;
  value: number;
  copy: string;
  icon: React.ComponentType<{ className?: string }>;
}

function ReportMetricCard({ label, value, copy, icon: Icon }: ReportMetricCardProps) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
      <div className="flex items-center justify-between gap-3">
        <p className="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{label}</p>
        <Icon className="h-4 w-4 text-brand-600" />
      </div>
      <p className="mt-3 text-2xl font-bold tracking-tight text-slate-900">{formatReportNumber(value)}</p>
      <p className="mt-1 text-xs text-slate-500">{copy}</p>
    </div>
  );
}

export default function GoogleAnalytics({ configuration, report, reportError }: GoogleAnalyticsProps) {
  const reportingReady = Boolean(
    configuration.propertyId && configuration.projectConfigured && configuration.credentialsConfigured && report,
  );
  const trend = report?.trend.slice(-14) ?? [];
  const maxTrendUsers = Math.max(...trend.map((day) => day.activeUsers), 1);

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
            <p className="admin-page-copy">Laporan GA4 live untuk membantu semak prestasi laman dan sumber trafik.</p>
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
                <Radio className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="admin-kpi-value">{report ? 'LIVE' : 'API'}</p>
                <p className="truncate text-xs font-medium text-slate-500">GA4 Data API</p>
              </div>
            </div>
            <p className="mt-3 text-xs leading-5 text-slate-500">Dibaca terus oleh server Laravel menggunakan akses read-only.</p>
          </div>
        </div>

        <section className="admin-flat-card overflow-hidden">
          <div className="admin-card-header flex-col items-start sm:flex-row sm:items-center">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <BarChart3 className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Laporan GA4 live</h3>
                <p className="text-xs text-slate-500">
                  {report
                    ? `${formatReportDate(report.dateRange.start)} - ${formatReportDate(report.dateRange.end)} · Dikemaskini ${formatReportTimestamp(report.generatedAt)}`
                    : 'Data laporan akan dipaparkan selepas sambungan API berjaya.'}
                </p>
              </div>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              {report && (
                <span className="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-700">
                  <Radio className="h-3.5 w-3.5" />
                  {formatReportNumber(report.realtimeActiveUsers)} realtime
                </span>
              )}
              <Link
                href={`${route('admin.google-analytics.index')}?refresh=1`}
                preserveScroll
                className="admin-btn-secondary text-xs"
              >
                <RefreshCw className="h-3.5 w-3.5" />
                Segar data
              </Link>
            </div>
          </div>

          {report ? (
            <>
              <div className="grid gap-3 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3 xl:grid-cols-6">
                <ReportMetricCard label="Pengguna aktif" value={report.summary.activeUsers} copy="30 hari terakhir" icon={Users} />
                <ReportMetricCard label="Pengguna baharu" value={report.summary.newUsers} copy="30 hari terakhir" icon={UserPlus} />
                <ReportMetricCard label="Sesi" value={report.summary.sessions} copy="Jumlah kunjungan" icon={Activity} />
                <ReportMetricCard label="Sesi engaged" value={report.summary.engagedSessions} copy="Sesi berkualiti" icon={MousePointerClick} />
                <ReportMetricCard label="Event" value={report.summary.eventCount} copy="Jumlah event" icon={Radio} />
                <ReportMetricCard label="Page views" value={report.summary.screenPageViews} copy="Paparan halaman" icon={Eye} />
              </div>

              <div className="grid gap-6 border-t border-slate-200 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
                <div className="min-w-0">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">Trend pengguna aktif</h4>
                      <p className="mt-1 text-xs text-slate-500">14 hari terakhir dalam tempoh laporan.</p>
                    </div>
                    <CalendarDays className="h-4 w-4 shrink-0 text-slate-400" />
                  </div>
                  <div className="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    {trend.length > 0 ? (
                      <div className="flex h-48 min-w-[36rem] items-end gap-2">
                        {trend.map((day) => {
                          const barHeight = day.activeUsers > 0
                            ? Math.max(8, (day.activeUsers / maxTrendUsers) * 100)
                            : 2;

                          return (
                            <div key={day.date} className="flex h-full min-w-5 flex-1 flex-col items-center justify-end gap-2">
                              <div className="flex h-full w-full items-end">
                                <div
                                  className="w-full rounded-t-lg bg-brand-500/80 transition hover:bg-brand-600"
                                  style={{ height: `${barHeight}%` }}
                                  title={`${formatReportDate(day.date)}: ${formatReportNumber(day.activeUsers)} pengguna aktif`}
                                />
                              </div>
                              <span className="whitespace-nowrap text-[10px] text-slate-400">{formatReportDate(day.date)}</span>
                            </div>
                          );
                        })}
                      </div>
                    ) : (
                      <p className="flex h-48 items-center justify-center text-sm text-slate-500">Tiada data trend untuk tempoh ini.</p>
                    )}
                  </div>
                </div>

                <div className="rounded-2xl border border-brand-100 bg-brand-50/70 p-5">
                  <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2.5">
                      <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-brand-600">
                        <Radio className="h-4 w-4" />
                      </div>
                      <div>
                        <h4 className="text-sm font-bold text-brand-950">Realtime</h4>
                        <p className="text-xs text-brand-800/70">Aktiviti sekitar 30 minit terakhir.</p>
                      </div>
                    </div>
                    <span className="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-brand-700">Live</span>
                  </div>
                  <p className="mt-7 text-4xl font-bold tracking-tight text-brand-950">{formatReportNumber(report.realtimeActiveUsers)}</p>
                  <p className="mt-1 text-sm font-semibold text-brand-900">pengguna aktif sekarang</p>
                  <p className="mt-4 text-xs leading-5 text-brand-800/80">Nilai ini berubah mengikut aktiviti pengunjung dan mungkin mengambil sedikit masa untuk dikemas kini oleh Google.</p>
                </div>
              </div>

              <div className="grid gap-6 border-t border-slate-200 p-5 sm:p-6 lg:grid-cols-2">
                <div className="min-w-0">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">Halaman teratas</h4>
                      <p className="mt-1 text-xs text-slate-500">Berdasarkan page views.</p>
                    </div>
                    <Eye className="h-4 w-4 text-slate-400" />
                  </div>
                  <div className="admin-table-card mt-4">
                    <div className="admin-table-wrap">
                      <table className="admin-table">
                        <thead>
                          <tr>
                            <th>Halaman</th>
                            <th>Views</th>
                            <th>Users</th>
                          </tr>
                        </thead>
                        <tbody>
                          {report.topPages.length > 0 ? report.topPages.map((page) => (
                            <tr key={`${page.path}-${page.title}`}>
                              <td className="max-w-[18rem]">
                                <p className="truncate font-semibold text-slate-800" title={page.title}>{page.title}</p>
                                <p className="truncate text-[11px] text-slate-400" title={page.path}>{page.path}</p>
                              </td>
                              <td className="font-semibold">{formatReportNumber(page.screenPageViews)}</td>
                              <td>{formatReportNumber(page.activeUsers)}</td>
                            </tr>
                          )) : (
                            <tr><td colSpan={3} className="py-8 text-center text-slate-500">Tiada data halaman.</td></tr>
                          )}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <div className="min-w-0">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">Sumber trafik</h4>
                      <p className="mt-1 text-xs text-slate-500">Saluran yang membawa sesi terbanyak.</p>
                    </div>
                    <MousePointerClick className="h-4 w-4 text-slate-400" />
                  </div>
                  <div className="mt-4 divide-y divide-slate-100 rounded-2xl border border-slate-200 bg-white px-4">
                    {report.topSources.length > 0 ? report.topSources.map((source) => (
                      <div key={source.channel} className="flex items-center justify-between gap-4 py-3">
                        <div className="min-w-0">
                          <p className="truncate text-sm font-semibold text-slate-800">{source.channel}</p>
                          <p className="mt-0.5 text-xs text-slate-500">{formatReportNumber(source.activeUsers)} pengguna aktif</p>
                        </div>
                        <p className="shrink-0 text-sm font-bold text-brand-700">{formatReportNumber(source.sessions)} sesi</p>
                      </div>
                    )) : (
                      <p className="py-8 text-center text-sm text-slate-500">Tiada data sumber trafik.</p>
                    )}
                  </div>
                </div>
              </div>
            </>
          ) : (
            <div className="flex flex-col items-start gap-4 p-5 sm:flex-row sm:p-6">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <CircleAlert className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <h4 className="text-sm font-bold text-slate-900">Laporan belum tersedia</h4>
                <p className="mt-1 text-sm leading-6 text-slate-600">{reportError ?? 'Pastikan konfigurasi Google Analytics telah lengkap sebelum cuba semula.'}</p>
                <p className="mt-2 text-xs leading-5 text-slate-500">Data diambil secara read-only melalui Google Analytics Data API dan tidak disimpan dalam database aplikasi.</p>
              </div>
            </div>
          )}
        </section>

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
                copy="Project ini digunakan untuk permintaan Google Analytics Data API."
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
                  <h3 className="text-base font-bold text-slate-900">Setup laporan server</h3>
                  <p className="text-xs text-slate-500">Data API membaca credential daripada server Laravel.</p>
                </div>
              </div>
            </div>
            <div className="space-y-4 p-5 sm:p-6">
              <ol className="space-y-3 text-sm text-slate-600">
                <li className="flex gap-3"><span className="font-bold text-brand-600">1.</span><span>Aktifkan Google Analytics Admin API dan Data API.</span></li>
                <li className="flex gap-3"><span className="font-bold text-brand-600">2.</span><span>Tambah email service account sebagai pengguna <strong>Viewer</strong> pada GA4 Property.</span></li>
                <li className="flex gap-3"><span className="font-bold text-brand-600">3.</span><span>Isi tiga nilai berikut dalam `.env` production server.</span></li>
              </ol>
              <pre className="overflow-x-auto rounded-2xl bg-slate-950 p-4 text-[11px] leading-5 text-slate-200"><code>{serverEnvExample}</code></pre>
              <div>
                <p className="text-xs font-semibold text-slate-700">MCP untuk analisis AI (pilihan)</p>
                <pre className="mt-2 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-[11px] leading-5 text-slate-200"><code>{claudeCommand}</code></pre>
              </div>
              <p className="text-xs leading-5 text-slate-500">Simpan fail credential di luar folder public dan jangan letakkan fail JSON tersebut dalam git.</p>
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
            <p className="mt-1 leading-6 text-brand-800">Laporan di atas dibaca terus oleh Laravel melalui Google Analytics Data API dengan akses read-only. MCP rasmi ialah pilihan tambahan untuk bertanya soalan analisis melalui Claude atau Gemini.</p>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
