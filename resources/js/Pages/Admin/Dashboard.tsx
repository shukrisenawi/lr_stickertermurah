import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Package, Palette, Tag, Clock, ArrowRight, Receipt, Users } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  total: number;
  status: string;
  created_at: string;
}

interface DashboardProps {
  totalOrders: number;
  pendingOrders: number;
  totalDesigns: number;
  totalCategories: number;
  recentOrders: Order[];
}

export default function Dashboard({ totalOrders, pendingOrders, totalDesigns, totalCategories, recentOrders }: DashboardProps) {
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

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-100 text-amber-700';
      case 'paid': return 'bg-blue-100 text-blue-700';
      case 'processing': return 'bg-purple-100 text-purple-700';
      case 'shipped': return 'bg-sky-100 text-sky-700';
      case 'completed': return 'bg-emerald-100 text-emerald-700';
      case 'cancelled': return 'bg-rose-100 text-rose-700';
      default: return 'bg-slate-100 text-slate-700';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

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

        {/* Recent Orders */}
        <div className="admin-flat-card">
          <div className="admin-card-header">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <Clock className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Order Terkini</h3>
                <p className="text-xs text-slate-500">{recentOrders.length} order terbaharu</p>
              </div>
            </div>
            <Link href={route('admin.orders.index')} className="admin-btn-secondary text-xs">
              Lihat Semua
            </Link>
          </div>

          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>No. Order</th>
                  <th>Pelanggan</th>
                  <th>Jumlah</th>
                  <th>Status</th>
                  <th>Tarikh</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-12 text-center">
                      <div className="admin-table-empty">
                        <p className="admin-table-empty-title">Tiada Order</p>
                        <p className="admin-table-empty-copy">Belum ada order direkodkan dalam sistem.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  recentOrders.map((order) => (
                    <tr key={order.id}>
                      <td className="font-medium text-slate-900">{order.order_no}</td>
                      <td>{order.customer_name}</td>
                      <td>{formatCurrency(order.total)}</td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ${getStatusColor(order.status)}`}>
                          {order.status.toUpperCase()}
                        </span>
                      </td>
                      <td className="text-slate-500">{formatDate(order.created_at)}</td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
