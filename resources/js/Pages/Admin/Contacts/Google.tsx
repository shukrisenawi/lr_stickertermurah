import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
  Contact,
  AlertCircle,
  Phone,
  Mail,
  Users,
  LogIn,
  LogOut,
} from 'lucide-react';

interface GoogleContact {
  name: string;
  phones: string;
  emails: string;
}

interface GoogleContactsProps {
  contacts: GoogleContact[];
  isConnected: boolean;
  isConfigured: boolean;
  error: string | null;
}

export default function GoogleContacts({
  contacts,
  isConnected,
  isConfigured,
  error,
}: GoogleContactsProps) {
  const { post: disconnect, processing: disconnecting } = useForm();

  return (
    <AdminLayout>
      <Head title="Google Contacts" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Google Contacts</h2>
            <p className="admin-page-copy">Sambung dan urus kenalan Google.</p>
          </div>
        </div>

        {!isConfigured && (
          <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <div className="flex items-start gap-2">
              <AlertCircle className="h-5 w-5 shrink-0" />
              <div>
                <p className="font-medium">Google OAuth belum dikonfigurasi</p>
                <p className="mt-1">
                  Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET dalam fail .env dahulu.
                </p>
              </div>
            </div>
          </div>
        )}

        {error && (
          <div className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <div className="flex items-start gap-2">
              <AlertCircle className="h-5 w-5 shrink-0" />
              <p className="font-medium">{error}</p>
            </div>
          </div>
        )}

        {isConfigured && (
          <div className="flex items-center gap-3">
            {!isConnected ? (
              <Link
                href={route('admin.contacts.google.connect')}
                className="admin-btn-primary text-sm"
              >
                <LogIn className="h-4 w-4" />
                Sambung Google Contacts
              </Link>
            ) : (
              <button
                type="button"
                onClick={() => {
                  if (confirm('Putuskan sambungan Google Contacts?')) {
                    disconnect(route('admin.contacts.google.disconnect'));
                  }
                }}
                disabled={disconnecting}
                className="admin-btn-danger text-sm"
              >
                <LogOut className="h-4 w-4" />
                {disconnecting ? 'Memutuskan...' : 'Putuskan Sambungan'}
              </button>
            )}
          </div>
        )}

        {isConnected && (
          <div className="admin-table-card">
            <div className="admin-card-header">
              <div className="flex items-center gap-3">
                <div className="admin-icon-badge">
                  <Users className="h-5 w-5" />
                </div>
                <h3 className="text-lg font-bold text-slate-900">
                  Senarai Kenalan ({contacts.length})
                </h3>
              </div>
            </div>
            <div className="admin-table-wrap">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Nama</th>
                    <th>No. Telefon</th>
                    <th>Emel</th>
                  </tr>
                </thead>
                <tbody>
                  {contacts.length === 0 ? (
                    <tr>
                      <td colSpan={3} className="py-16 text-center">
                        <div className="admin-table-empty">
                          <Contact className="mx-auto h-12 w-12 text-slate-300" />
                          <p className="admin-table-empty-title">Tiada Kenalan</p>
                        </div>
                      </td>
                    </tr>
                  ) : (
                    contacts.map((c) => (
                      <tr key={c.name}>
                        <td className="font-medium text-slate-900">{c.name}</td>
                        <td>
                          <div className="flex items-center gap-1.5">
                            <Phone className="h-3.5 w-3.5 text-slate-400" />
                            <span className="text-sm">{c.phones}</span>
                          </div>
                        </td>
                        <td>
                          <div className="flex items-center gap-1.5">
                            <Mail className="h-3.5 w-3.5 text-slate-400" />
                            <span className="text-sm">{c.emails}</span>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
