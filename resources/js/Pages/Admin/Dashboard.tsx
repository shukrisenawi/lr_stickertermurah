import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';

export default function AdminDashboard() {
  return (
    <AdminLayout>
      <Head title="Dashboard" />
      <div className="space-y-8">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Dashboard</h2>
          <p className="mt-1 text-sm text-slate-500">Ringkasan aktiviti kedai anda.</p>
        </div>

        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[
            { label: 'Jumlah Order', value: '0', color: 'bg-blue-500' },
            { label: 'Order Aktif', value: '0', color: 'bg-emerald-500' },
            { label: 'Jumlah Pelanggan', value: '0', color: 'bg-amber-500' },
            { label: 'Jumlah Jualan', value: 'RM 0.00', color: 'bg-brand-600' },
          ].map((stat) => (
            <div key={stat.label} className="admin-kpi-card">
              <div className="flex items-center justify-between">
                <div>
                  <p className="admin-kpi-label">{stat.label}</p>
                  <p className="admin-kpi-value">{stat.value}</p>
                </div>
                <div className={`admin-kpi-icon ${stat.color}`}>
                  <span className="text-lg">📊</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </AdminLayout>
  );
}
