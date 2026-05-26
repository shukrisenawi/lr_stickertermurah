import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import { AlertCircle } from 'lucide-react';

export default function GoogleContacts() {
  return (
    <AdminLayout>
      <Head title="Google Contacts" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Google Contacts</h2>
            <p className="admin-page-copy">Ciri Google Contacts telah dihentikan.</p>
          </div>
        </div>

        <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
          <div className="flex items-start gap-2">
            <AlertCircle className="h-5 w-5 shrink-0" />
            <div>
              <p className="font-medium">Ciri tidak tersedia</p>
              <p className="mt-1">
                Ciri sambungan Google Contacts telah dihentikan. Sila gunakan menu "Ekstrak Contact" untuk mengurus contact.
              </p>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
