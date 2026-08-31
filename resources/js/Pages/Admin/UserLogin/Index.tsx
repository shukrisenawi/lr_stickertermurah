import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Clock3, LogIn, Radio, UserRound, Users } from 'lucide-react';

interface UserLogin {
  id: number;
  name: string;
  email: string | null;
  no_tel: string | null;
  is_admin: boolean;
  last_login_at: string | null;
  last_seen_at: string | null;
  is_online: boolean;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface UserLoginProps {
  users: {
    data: UserLogin[];
    links: PaginationLink[];
  };
  summary: {
    total: number;
    loggedIn: number;
    online: number;
  };
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'Belum pernah';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return new Intl.DateTimeFormat('ms-MY', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
}

function paginationLabel(label: string): string {
  const normalizedLabel = label.toLowerCase();

  if (normalizedLabel.includes('previous')) {
    return 'Sebelum';
  }

  if (normalizedLabel.includes('next')) {
    return 'Seterusnya';
  }

  return label;
}

export default function UserLoginIndex({ users, summary }: UserLoginProps) {
  return (
    <AdminLayout>
      <Head title="User Login" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">User Login</h2>
            <p className="admin-page-copy">Pantau login terbaru dan masa terakhir user aktif di laman.</p>
          </div>
          <Link href={route('admin.dashboard')} className="admin-btn-secondary text-sm">
            Dashboard
          </Link>
        </div>

        <div className="grid gap-3 sm:grid-cols-3">
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between gap-3">
              <div>
                <p className="admin-kpi-label">Jumlah User</p>
                <p className="admin-kpi-value">{summary.total.toLocaleString('ms-MY')}</p>
              </div>
              <Users className="h-5 w-5 text-brand-600" />
            </div>
            <p className="mt-1 text-xs text-slate-500">Semua akaun berdaftar</p>
          </div>
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between gap-3">
              <div>
                <p className="admin-kpi-label">Pernah Login</p>
                <p className="admin-kpi-value">{summary.loggedIn.toLocaleString('ms-MY')}</p>
              </div>
              <LogIn className="h-5 w-5 text-brand-600" />
            </div>
            <p className="mt-1 text-xs text-slate-500">Login berjaya direkod</p>
          </div>
          <div className="admin-kpi-card">
            <div className="flex items-center justify-between gap-3">
              <div>
                <p className="admin-kpi-label">Aktif 15 Minit</p>
                <p className="admin-kpi-value">{summary.online.toLocaleString('ms-MY')}</p>
              </div>
              <Radio className="h-5 w-5 text-emerald-600" />
            </div>
            <p className="mt-1 text-xs text-slate-500">Berdasarkan aktiviti terakhir</p>
          </div>
        </div>

        <section className="admin-table-card overflow-hidden">
          <div className="admin-card-header flex-col items-start gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <UserRound className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Aktiviti user</h3>
                <p className="text-xs text-slate-500">Disusun daripada login paling baharu.</p>
              </div>
            </div>
            <div className="flex items-center gap-1.5 text-xs text-slate-500">
              <Clock3 className="h-3.5 w-3.5" />
              Online = aktif dalam 15 minit terakhir
            </div>
          </div>

          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Hubungan</th>
                  <th>Login terakhir</th>
                  <th>Online terakhir</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {users.data.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-16 text-center">
                      <UserRound className="mx-auto h-12 w-12 text-slate-300" />
                      <p className="mt-4 text-sm font-semibold text-slate-500">Tiada user berdaftar</p>
                    </td>
                  </tr>
                ) : users.data.map((user) => (
                  <tr key={user.id}>
                    <td className="min-w-48">
                      <p className="font-semibold text-slate-900">{user.name}</p>
                      <span className={`mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold ${user.is_admin ? 'bg-violet-50 text-violet-700' : 'bg-slate-100 text-slate-600'}`}>
                        {user.is_admin ? 'Admin' : 'Ahli'}
                      </span>
                    </td>
                    <td className="min-w-48">
                      <p className="text-sm text-slate-700">{user.email || 'Tiada email'}</p>
                      <p className="mt-0.5 text-xs text-slate-500">{user.no_tel || 'Tiada no. telefon'}</p>
                    </td>
                    <td className="min-w-44 text-sm text-slate-700">{formatDate(user.last_login_at)}</td>
                    <td className="min-w-44 text-sm text-slate-700">{formatDate(user.last_seen_at)}</td>
                    <td>
                      {user.is_online ? (
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                          <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                          Online
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                          <span className="h-1.5 w-1.5 rounded-full bg-slate-400" />
                          Offline
                        </span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {users.links.length > 3 && (
            <div className="flex items-center gap-2 border-t border-slate-200 px-6 py-4">
              {users.links.map((link) => (
                link.url ? (
                  <Link
                    key={`${link.label}-${link.url}`}
                    href={link.url}
                    className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}
                  >
                    {paginationLabel(link.label)}
                  </Link>
                ) : (
                  <span key={`${link.label}-disabled`} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">
                    {paginationLabel(link.label)}
                  </span>
                )
              ))}
            </div>
          )}
        </section>
      </div>
    </AdminLayout>
  );
}
