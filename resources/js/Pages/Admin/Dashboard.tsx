import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { BarChart3, Package, Palette, Tag, Clock, ArrowRight, Receipt, Users, TrendingUp } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Invoice {
  id: number;
  invoice_no: string;
  customer_name: string;
  amount: number;
  payment_status: string;
  issue_date: string;
}

interface SalesMonth {
  key: string;
  label: string;
  amount: number;
  invoice_count: number;
}

interface SalesStats {
  months: SalesMonth[];
  total_amount: number;
  total_invoices: number;
}

interface StateStatistic {
  state: string;
  count: number;
}

interface AddressStatistics {
  states: StateStatistic[];
  total_default_addresses: number;
  classified_addresses: number;
  unclassified_addresses: number;
}

interface DashboardProps {
  totalOrders: number;
  pendingOrders: number;
  totalDesigns: number;
  totalCategories: number;
  recentInvoices: Invoice[];
  salesStats: SalesStats;
  addressStatistics: AddressStatistics;
}

function AddressStatisticsChart({ statistics }: { statistics: AddressStatistics }) {
  const maxCount = Math.max(...statistics.states.map((item) => item.count), 1);
  const summary = [
    { label: 'Alamat Default', value: statistics.total_default_addresses, copy: 'Jumlah alamat yang ditetapkan sebagai default' },
    { label: 'Alamat Ada Negeri', value: statistics.classified_addresses, copy: 'Alamat default dengan negeri yang sah' },
    { label: 'Tidak Dikenalpasti', value: statistics.unclassified_addresses, copy: 'Alamat default tanpa negeri yang dapat dikesan' },
  ];

  return (
    <div className="space-y-5">
      <div className="grid gap-3 sm:grid-cols-3">
        {summary.map((item) => (
          <div key={item.label} className="admin-kpi-card">
            <p className="admin-kpi-value">{item.value}</p>
            <p className="text-xs font-semibold text-slate-700">{item.label}</p>
            <p className="mt-1 text-[11px] leading-4 text-slate-500">{item.copy}</p>
          </div>
        ))}
      </div>

      <div className="admin-flat-card overflow-hidden">
        <div className="admin-card-header">
          <div className="flex items-center gap-2.5">
            <div className="admin-icon-badge">
              <BarChart3 className="h-4 w-4" />
            </div>
            <div>
              <h3 className="text-base font-bold text-slate-900">Alamat Default Mengikut Negeri</h3>
              <p className="text-xs text-slate-500">Bilangan alamat default pelanggan yang dikelompokkan berdasarkan negeri</p>
            </div>
          </div>
          <span className="hidden rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-brand-700 sm:inline-flex">
            Default Sahaja
          </span>
        </div>

        {statistics.states.length === 0 ? (
          <div className="px-5 py-16 text-center sm:px-6">
            <BarChart3 className="mx-auto h-12 w-12 text-slate-300" />
            <p className="mt-3 text-sm font-semibold text-slate-700">Belum ada data negeri</p>
            <p className="mt-1 text-xs text-slate-500">Tiada alamat default dengan negeri yang dapat dikenalpasti.</p>
          </div>
        ) : (
          <div className="space-y-4 p-5 sm:p-6" role="img" aria-label="Graf bilangan alamat default mengikut negeri">
            <div className="grid grid-cols-[minmax(7rem,0.55fr)_minmax(0,1fr)_3rem] items-center gap-3 border-b border-slate-100 pb-3 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
              <span>Negeri</span>
              <span>Graf</span>
              <span className="text-right">Jumlah</span>
            </div>
            {statistics.states.map((item) => (
              <div key={item.state} className="grid grid-cols-[minmax(7rem,0.55fr)_minmax(0,1fr)_3rem] items-center gap-3">
                <span className="min-w-0 break-words text-sm font-semibold text-slate-700">{item.state}</span>
                <div
                  className="h-3 overflow-hidden rounded-full bg-slate-100"
                  role="progressbar"
                  aria-label={`${item.state}: ${item.count} alamat default`}
                  aria-valuemin={0}
                  aria-valuemax={maxCount}
                  aria-valuenow={item.count}
                >
                  <div
                    className="h-full rounded-full bg-gradient-to-r from-brand-700 to-brand-400 transition-all"
                    style={{ width: `${Math.max((item.count / maxCount) * 100, 4)}%` }}
                  />
                </div>
                <span className="text-right text-sm font-bold text-slate-900">{item.count}</span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export default function Dashboard({ totalOrders, pendingOrders, totalDesigns, totalCategories, recentInvoices, salesStats, addressStatistics }: DashboardProps) {
  const stats = [
    { label: 'Jumlah Order', value: totalOrders, icon: Package, tint: 'bg-blue-50 text-blue-600' },
    { label: 'Order Aktif', value: pendingOrders, icon: Clock, tint: 'bg-emerald-50 text-emerald-600' },
    { label: 'Jumlah Design', value: totalDesigns, icon: Palette, tint: 'bg-amber-50 text-amber-600' },
    { label: 'Jumlah Kategori', value: totalCategories, icon: Tag, tint: 'bg-brand-50 text-brand-600' },
  ];

  const quickActions = [
    { label: 'Invoices', copy: 'Jana invois baharu', icon: Receipt, href: route('admin.invoices.create') },
    { label: 'Orders', copy: 'Urus tempahan', icon: Package, href: route('admin.orders.index') },
    { label: 'Customers', copy: 'Senarai pelanggan', icon: Users, href: route('admin.customers.index') },
    { label: 'Designs', copy: 'Katalog design', icon: Palette, href: route('admin.designs.index') },
  ];

  const getInvoiceStatus = (status: string) => {
    switch (status) {
      case 'paid': return { label: 'Telah Bayar', color: 'bg-emerald-100 text-emerald-700' };
      case 'partial': return { label: 'Bayaran Separa', color: 'bg-violet-100 text-violet-700' };
      case 'submitted': return { label: 'Menunggu Semakan', color: 'bg-amber-100 text-amber-700' };
      case 'rejected': return { label: 'Ditolak', color: 'bg-rose-100 text-rose-700' };
      default: return { label: 'Belum Bayar', color: 'bg-slate-100 text-slate-700' };
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  const formatChartValue = (amount: number) => {
    if (amount >= 1_000_000) return `RM ${(amount / 1_000_000).toFixed(1).replace('.0', '')}j`;
    if (amount >= 1_000) return `RM ${(amount / 1_000).toFixed(1).replace('.0', '')}k`;
    return `RM ${Math.round(amount)}`;
  };

  const chartWidth = 800;
  const chartHeight = 280;
  const chartPadding = { top: 20, right: 18, bottom: 44, left: 68 };
  const chartInnerWidth = chartWidth - chartPadding.left - chartPadding.right;
  const chartInnerHeight = chartHeight - chartPadding.top - chartPadding.bottom;
  const rawChartMax = Math.max(...salesStats.months.map((month) => Number(month.amount)), 0);
  const chartMagnitude = rawChartMax > 0 ? 10 ** Math.floor(Math.log10(rawChartMax)) : 1;
  const chartMax = rawChartMax > 0 ? Math.ceil(rawChartMax / chartMagnitude) * chartMagnitude : 1;
  const chartPoints = salesStats.months.map((month, index) => {
    const x = chartPadding.left + (index / Math.max(salesStats.months.length - 1, 1)) * chartInnerWidth;
    const y = chartPadding.top + (1 - Number(month.amount) / chartMax) * chartInnerHeight;

    return { ...month, x, y };
  });
  const linePoints = chartPoints.map((point) => `${point.x},${point.y}`).join(' ');
  const areaPoints = chartPoints.length > 0
    ? `${chartPadding.left},${chartPadding.top + chartInnerHeight} ${linePoints} ${chartPadding.left + chartInnerWidth},${chartPadding.top + chartInnerHeight}`
    : '';
  const chartTicks = Array.from({ length: 5 }, (_, index) => {
    const value = chartMax - (chartMax / 4) * index;
    const y = chartPadding.top + (chartInnerHeight / 4) * index;

    return { value, y };
  });

  return (
    <AdminLayout>
      <Head title="Dashboard" />
      <div className="space-y-5">
        {/* Stats Grid */}
        <div className="grid grid-cols-2 gap-3 xl:grid-cols-4">
          {stats.map((stat) => (
            <div key={stat.label} className="admin-kpi-card">
              <div className="flex items-center gap-3">
                <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${stat.tint}`}>
                  <stat.icon className="h-5 w-5" />
                </div>
                <div className="min-w-0">
                  <p className="admin-kpi-value">{stat.value}</p>
                  <p className="truncate text-xs font-medium text-slate-500">{stat.label}</p>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-2 gap-3 xl:grid-cols-4">
          {quickActions.map((action) => (
            <Link
              key={action.label}
              href={action.href}
              className="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md"
            >
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                <action.icon className="h-4 w-4" />
              </div>
              <div className="min-w-0 flex-1">
                <p className="truncate text-[13px] font-bold text-slate-900">{action.label}</p>
                <p className="truncate text-[11px] text-slate-500">{action.copy}</p>
              </div>
              <ArrowRight className="h-3.5 w-3.5 shrink-0 text-slate-300 transition group-hover:text-brand-500" />
            </Link>
          ))}
        </div>

        {/* Sales Chart */}
        <div className="admin-flat-card overflow-hidden">
          <div className="admin-card-header">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <TrendingUp className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Statistik Jualan</h3>
                <p className="text-xs text-slate-500">Jumlah nilai invoice mengikut bulan untuk 12 bulan terakhir</p>
              </div>
            </div>
            <span className="hidden rounded-full border border-brand-100 bg-brand-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-brand-700 sm:inline-flex">
              Berdasarkan Invoice
            </span>
          </div>

          <div className="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_13rem] lg:items-start">
            <div className="min-w-0">
              <div className="h-[250px] w-full sm:h-[290px]">
                <svg
                  viewBox={`0 0 ${chartWidth} ${chartHeight}`}
                  className="h-full w-full overflow-visible"
                  role="img"
                  aria-labelledby="sales-chart-title sales-chart-description"
                >
                  <title id="sales-chart-title">Graf statistik jualan bulanan</title>
                  <desc id="sales-chart-description">Jumlah nilai invoice bagi setiap bulan dalam 12 bulan terakhir.</desc>

                  <defs>
                    <linearGradient id="sales-area-gradient" x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" stopColor="#d91c5c" stopOpacity="0.2" />
                      <stop offset="100%" stopColor="#d91c5c" stopOpacity="0" />
                    </linearGradient>
                  </defs>

                  {chartTicks.map((tick) => (
                    <g key={tick.y}>
                      <line
                        x1={chartPadding.left}
                        x2={chartPadding.left + chartInnerWidth}
                        y1={tick.y}
                        y2={tick.y}
                        className="stroke-slate-100"
                        strokeDasharray="4 6"
                      />
                      <text
                        x={chartPadding.left - 12}
                        y={tick.y + 4}
                        textAnchor="end"
                        className="fill-slate-400 text-[16px] sm:text-[10px]"
                      >
                        {formatChartValue(tick.value)}
                      </text>
                    </g>
                  ))}

                  {areaPoints && <polygon points={areaPoints} fill="url(#sales-area-gradient)" />}
                  {linePoints && (
                    <polyline
                      points={linePoints}
                      fill="none"
                      stroke="#d91c5c"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth="3"
                    />
                  )}

                  {chartPoints.map((point) => (
                    <g key={point.key}>
                      <circle cx={point.x} cy={point.y} r="5" fill="#ffffff" stroke="#d91c5c" strokeWidth="3">
                        <title>{`${point.label}: ${formatCurrency(Number(point.amount))} (${point.invoice_count} invoice)`}</title>
                      </circle>
                      {Number(point.amount) > 0 && (
                        <text
                          x={point.x}
                          y={point.y - 12}
                          textAnchor="middle"
                          className="fill-brand-700 text-[14px] font-bold sm:text-[10px]"
                        >
                          {formatCurrency(Number(point.amount))}
                        </text>
                      )}
                      <text
                        x={point.x}
                        y={chartHeight - 12}
                        textAnchor="middle"
                        className="fill-slate-400 text-[16px] sm:text-[10px]"
                      >
                        {point.label}
                      </text>
                    </g>
                  ))}
                </svg>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-3 lg:grid-cols-1">
              <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Nilai Jualan</p>
                <p className="mt-1 text-xl font-extrabold tracking-tight text-slate-900">{formatCurrency(salesStats.total_amount)}</p>
                <p className="mt-1 text-[11px] text-slate-500">12 bulan terakhir</p>
              </div>
              <div className="rounded-2xl border border-slate-200 bg-white p-4">
                <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Jumlah Invoice</p>
                <p className="mt-1 text-xl font-extrabold tracking-tight text-slate-900">{salesStats.total_invoices}</p>
                <p className="mt-1 text-[11px] text-slate-500">Invoice dalam graf</p>
              </div>
            </div>
          </div>
        </div>

        {/* Address Statistics */}
        <AddressStatisticsChart statistics={addressStatistics} />

        {/* Recent Invoices */}
        <div className="admin-flat-card">
          <div className="admin-card-header">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <Receipt className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Invoice Terbaru</h3>
                <p className="text-xs text-slate-500">{recentInvoices.length} invoice terbaharu</p>
              </div>
            </div>
            <Link href={route('admin.invoices.index')} className="admin-btn-secondary text-xs">
              Lihat Semua
            </Link>
          </div>

          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>No. Invoice</th>
                  <th>Pelanggan</th>
                  <th>Jumlah</th>
                  <th>Status</th>
                  <th>Tarikh</th>
                </tr>
              </thead>
              <tbody>
                {recentInvoices.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-12 text-center">
                      <div className="admin-table-empty">
                        <p className="admin-table-empty-title">Tiada Invoice</p>
                        <p className="admin-table-empty-copy">Belum ada invoice direkodkan dalam sistem.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  recentInvoices.map((invoice) => {
                    const status = getInvoiceStatus(invoice.payment_status);

                    return (
                      <tr key={invoice.id}>
                        <td>
                          <Link href={route('admin.invoices.show', invoice.id)} className="font-semibold text-brand-700 hover:text-brand-800 hover:underline">
                            {invoice.invoice_no}
                          </Link>
                        </td>
                        <td>{invoice.customer_name}</td>
                        <td className="whitespace-nowrap font-semibold text-slate-900">{formatCurrency(invoice.amount)}</td>
                        <td>
                          <span className={`inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${status.color}`}>
                            {status.label}
                          </span>
                        </td>
                        <td className="whitespace-nowrap text-slate-500">{formatDate(invoice.issue_date)}</td>
                      </tr>
                    );
                  })
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
