import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
  Activity,
  ArrowLeft,
  BarChart3,
  CalendarDays,
  CircleAlert,
  ExternalLink,
  Eye,
  Gauge,
  MousePointerClick,
  Radio,
  RefreshCw,
  Save,
  Search,
  UserPlus,
  Users,
} from 'lucide-react';

interface GoogleAnalyticsConfiguration {
  measurementId: string | null;
  propertyId: string | null;
  projectConfigured: boolean;
  credentialsConfigured: boolean;
}

interface GoogleAnalyticsProps {
  configuration: GoogleAnalyticsConfiguration;
  seoKeywords: string;
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
  regions: Array<{
    name: string;
    activeUsers: number;
  }>;
  ageBrackets: Array<{
    bracket: string;
    activeUsers: number;
  }>;
  realtimeActiveUsers: number;
}

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

export default function GoogleAnalytics({ configuration, seoKeywords, report, reportError }: GoogleAnalyticsProps) {
  const keywordForm = useForm<{ seo_keywords: string }>({ seo_keywords: seoKeywords });
  const reportingReady = Boolean(
    configuration.propertyId && configuration.projectConfigured && configuration.credentialsConfigured && report,
  );
  const trend = report?.trend.slice(-14) ?? [];
  const maxTrendUsers = Math.max(...trend.map((day) => day.activeUsers), 1);
  const regions = report?.regions ?? [];
  const maxRegionUsers = Math.max(...regions.map((region) => region.activeUsers), 1);
  const ageBrackets = report?.ageBrackets ?? [];
  const maxAgeUsers = Math.max(...ageBrackets.map((age) => age.activeUsers), 1);

  const handleKeywordSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    keywordForm.put(route('admin.google-analytics.keywords.update'), { preserveScroll: true });
  };

  return (
    <AdminLayout>
      <Head title="Google Analytics" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Google Analytics</h2>
            <p className="admin-page-copy">Laporan GA4 live untuk membantu semak prestasi laman, sumber trafik, lokasi dan demografi.</p>
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
            <p className="mt-3 text-xs text-slate-500">Digunakan untuk laporan property melalui Google Analytics Data API.</p>
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
          <div className="admin-card-header flex-col items-start gap-2 sm:flex-row sm:items-center">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <Search className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Keyword carian laman</h3>
                <p className="text-xs text-slate-500">Kata kunci yang membantu pelanggan menemui StickerTermurah.</p>
              </div>
            </div>
          </div>
          <form onSubmit={handleKeywordSubmit} className="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div>
              <label htmlFor="seo-keywords" className="block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                Keyword SEO
              </label>
              <textarea
                id="seo-keywords"
                rows={3}
                value={keywordForm.data.seo_keywords}
                onChange={(event) => keywordForm.setData('seo_keywords', event.target.value)}
                placeholder="sticker murah, cetak sticker murah, sticker custom"
                className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/15"
              />
              <p className="mt-2 text-xs leading-5 text-slate-500">Pisahkan setiap keyword dengan koma, titik koma atau baris baharu. Keyword berulang akan dibuang secara automatik.</p>
              {keywordForm.errors.seo_keywords && <p className="mt-1 text-xs text-rose-600">{keywordForm.errors.seo_keywords}</p>}
            </div>
            <button type="submit" disabled={keywordForm.processing} className="admin-btn-primary justify-center text-sm disabled:cursor-not-allowed disabled:opacity-60">
              <Save className="h-4 w-4" />
              {keywordForm.processing ? 'Menyimpan...' : 'Simpan keyword'}
            </button>
          </form>
        </section>

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

              <div className="grid gap-6 border-t border-slate-200 p-5 sm:p-6 lg:grid-cols-2">
                <div className="min-w-0">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">Pelawat mengikut negeri</h4>
                      <p className="mt-1 text-xs text-slate-500">Negeri atau wilayah di Malaysia berdasarkan lokasi pelawat.</p>
                    </div>
                    <Users className="h-4 w-4 text-slate-400" />
                  </div>
                  <div className="mt-4 space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                    {regions.length > 0 ? regions.map((region, index) => {
                      const barWidth = region.activeUsers > 0
                        ? Math.max(4, (region.activeUsers / maxRegionUsers) * 100)
                        : 0;

                      return (
                        <div key={region.name}>
                          <div className="flex items-center justify-between gap-3 text-sm">
                            <p className="truncate font-semibold text-slate-800">
                              <span className="mr-2 text-xs font-bold text-slate-400">{index + 1}</span>
                              {region.name}
                            </p>
                            <p className="shrink-0 font-bold text-brand-700">{formatReportNumber(region.activeUsers)}</p>
                          </div>
                          <div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div className="h-full rounded-full bg-brand-500" style={{ width: `${barWidth}%` }} />
                          </div>
                          <p className="mt-1 text-[11px] text-slate-500">pelawat aktif</p>
                        </div>
                      );
                    }) : (
                      <p className="py-8 text-center text-sm text-slate-500">Tiada data negeri untuk tempoh ini.</p>
                    )}
                  </div>
                </div>

                <div className="min-w-0">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">Umur pelawat</h4>
                      <p className="mt-1 text-xs text-slate-500">Kumpulan umur berdasarkan data demografi GA4.</p>
                    </div>
                    <UserPlus className="h-4 w-4 text-slate-400" />
                  </div>
                  <div className="mt-4 space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                    {ageBrackets.length > 0 ? ageBrackets.map((age) => {
                      const barWidth = age.activeUsers > 0
                        ? Math.max(4, (age.activeUsers / maxAgeUsers) * 100)
                        : 0;

                      return (
                        <div key={age.bracket}>
                          <div className="flex items-center justify-between gap-3 text-sm">
                            <p className="font-semibold text-slate-800">{age.bracket}</p>
                            <p className="shrink-0 font-bold text-brand-700">{formatReportNumber(age.activeUsers)}</p>
                          </div>
                          <div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div className="h-full rounded-full bg-brand-500" style={{ width: `${barWidth}%` }} />
                          </div>
                          <p className="mt-1 text-[11px] text-slate-500">pelawat aktif</p>
                        </div>
                      );
                    }) : (
                      <p className="py-8 text-center text-sm text-slate-500">Tiada data umur untuk tempoh ini.</p>
                    )}
                  </div>
                  <p className="mt-2 text-[11px] leading-5 text-slate-500">Data umur mungkin terhad mengikut ambang privasi Google.</p>
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
      </div>
    </AdminLayout>
  );
}
