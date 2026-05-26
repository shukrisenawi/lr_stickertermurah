import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Palette, Image as ImageIcon } from 'lucide-react';

interface Design {
  id: number;
  name: string;
  image_path: string | null;
  category: { name: string } | null;
  is_active: boolean;
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
            <h2 className="text-2xl font-bold text-slate-900">Galeri Design</h2>
            <p className="admin-page-copy">Urus design sticker.</p>
          </div>
          <Link href={route('admin.designs.create')} className="admin-btn-primary">
            <Plus className="h-4 w-4" />
            Tambah Design
          </Link>
        </div>

        {designs.data.length === 0 ? (
          <div className="rounded-2xl border border-slate-200 bg-white py-20 text-center">
            <Palette className="mx-auto h-16 w-16 text-slate-300" />
            <p className="mt-4 text-lg font-semibold text-slate-600">Tiada Design</p>
            <p className="mt-1 text-sm text-slate-400">Tambah design pertama anda untuk bermula.</p>
            <Link href={route('admin.designs.create')} className="admin-btn-primary mt-6 inline-flex">
              <Plus className="h-4 w-4" />
              Tambah Design
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            {designs.data.map((design) => (
              <div key={design.id} className="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                {/* Image */}
                <div className="aspect-square overflow-hidden bg-slate-100">
                  {design.image_path ? (
                    <img
                      src={`/storage/${design.image_path}`}
                      alt={design.name}
                      className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center">
                      <ImageIcon className="h-12 w-12 text-slate-300" />
                    </div>
                  )}
                </div>

                {/* Overlay on hover */}
                <div className="absolute inset-0 flex items-center justify-center gap-2 bg-black/50 opacity-0 transition group-hover:opacity-100">
                  <Link
                    href={route('admin.designs.edit', design.id)}
                    className="inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-lg transition hover:bg-brand-50 hover:text-brand-700"
                  >
                    <Pencil className="h-4 w-4" />
                    Sunting
                  </Link>
                  <button
                    onClick={() => handleDelete(design.id)}
                    className="inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-rose-600 shadow-lg transition hover:bg-rose-50"
                  >
                    <Trash2 className="h-4 w-4" />
                    Padam
                  </button>
                </div>

                {/* Info */}
                <div className="p-3">
                  <h3 className="truncate text-sm font-semibold text-slate-900">{design.name}</h3>
                  <div className="mt-1.5 flex items-center gap-2">
                    <span className="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                      {design.category?.name || '-'}
                    </span>
                    {design.is_active ? (
                      <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        Aktif
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-500">
                        <span className="h-1.5 w-1.5 rounded-full bg-slate-400" />
                        Tidak Aktif
                      </span>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {designs.links.length > 3 && (
          <div className="flex items-center justify-center gap-2">
            {designs.links.map((link, i) => (
              link.url ? (
                <Link key={i} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
              ) : (
                <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
              )
            ))}
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
