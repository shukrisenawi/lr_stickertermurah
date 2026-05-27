import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Ruler, Plus, Pencil, Trash2 } from 'lucide-react';

interface Size {
  id: number;
  name: string;
  width_cm: number;
  height_cm: number;
  shape: string | null;
  qty_per_a3: number | null;
  is_active: boolean;
  is_default: boolean;
}

interface SizesIndexProps {
  sizes: {
    data: Size[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function SizesIndex({ sizes }: SizesIndexProps) {
  const { delete: destroy } = useForm();

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam saiz ini?')) {
      destroy(route('admin.sizes.destroy', id));
    }
  };

  return (
    <AdminLayout>
      <Head title="Senarai Saiz" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Saiz</h2>
            <p className="admin-page-copy">Urus saiz sticker dan kuantiti per A3.</p>
          </div>
          <Link href={route('admin.sizes.create')} className="admin-btn-primary">
            <Plus className="h-4 w-4" />
            Tambah Saiz
          </Link>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Saiz (cm)</th>
                  <th>Bentuk</th>
                  <th>Qty/A3</th>
                  <th>Default</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {sizes.data.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Ruler className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Saiz</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  sizes.data.map((size) => (
                    <tr key={size.id}>
                      <td className="font-medium text-slate-900">{size.name}</td>
                      <td>{size.width_cm} x {size.height_cm}</td>
                      <td>{size.shape ?? '-'}</td>
                      <td className="font-medium">{size.qty_per_a3 ?? '-'}</td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${size.is_default ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'}`}>
                          {size.is_default ? 'Ya' : 'Tidak'}
                        </span>
                      </td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${size.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                          {size.is_active ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                      </td>
                      <td>
                        <div className="flex items-center gap-2">
                          <Link
                            href={route('admin.sizes.edit', size.id)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                          >
                            <Pencil className="h-4 w-4" />
                          </Link>
                          <button
                            type="button"
                            onClick={() => handleDelete(size.id)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50 transition"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {sizes.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {sizes.links.map((link) => {
                  const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
                  return link.url ? (
                    <Link key={link.label} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>
                      {label}
                    </Link>
                  ) : (
                    <span key={link.label} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">{label}</span>
                  );
                })}
              </div>
            </div>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
