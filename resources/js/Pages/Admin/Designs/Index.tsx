import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Palette } from 'lucide-react';

interface Design {
  id: number;
  name: string;
  category: { name: string } | null;
  is_active: boolean;
  created_at: string;
}

interface DesignsIndexProps {
  designs: {
    data: Design[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function DesignsIndex({ designs }: DesignsIndexProps) {
  const { delete: destroy } = useForm();

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam design ini?')) {
      destroy(route('admin.designs.destroy', id));
    }
  };

  return (
    <AdminLayout>
      <Head title="Senarai Design" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Design</h2>
            <p className="admin-page-copy">Urus design sticker.</p>
          </div>
          <Link href={route('admin.designs.create')} className="admin-btn-primary">
            <Plus className="h-4 w-4" />
            Tambah Design
          </Link>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Kategori</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {designs.data.length === 0 ? (
                  <tr>
                    <td colSpan={4} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Palette className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Design</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  designs.data.map((design) => (
                    <tr key={design.id}>
                      <td className="font-medium text-slate-900">{design.name}</td>
                      <td>{design.category?.name || '-'}</td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${design.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                          {design.is_active ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                      </td>
                      <td>
                        <div className="flex items-center gap-2">
                          <Link
                            href={route('admin.designs.edit', design.id)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                          >
                            <Pencil className="h-4 w-4" />
                          </Link>
                          <button
                            onClick={() => handleDelete(design.id)}
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

          {designs.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {designs.links.map((link, i) => (
                  link.url ? (
                    <Link key={i} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                  ) : (
                    <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                  )
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
