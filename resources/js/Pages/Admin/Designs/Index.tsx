import { useState } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Plus, Pencil, Trash2, Palette, Image as ImageIcon, X, Eye } from 'lucide-react';

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
  const [preview, setPreview] = useState<Design | null>(null);

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
              <div
                key={design.id}
                onClick={() => setPreview(design)}
                className="group relative cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
              >
                {/* Image */}
                <div className="aspect-square w-full overflow-hidden bg-slate-100">
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
                  {design.image_path && (
                    <div className="absolute right-2 top-2 rounded-lg bg-black/50 p-1.5 text-white opacity-0 transition group-hover:opacity-100 pointer-events-none">
                      <Eye className="h-4 w-4" />
                    </div>
                  )}
                </div>

                {/* Overlay on hover */}
                <div className="absolute inset-x-0 bottom-0 flex items-center justify-center gap-2 bg-gradient-to-t from-black/60 to-transparent pb-20 pt-8 opacity-0 transition group-hover:opacity-100 pointer-events-none">
                  <Link
                    href={route('admin.designs.edit', design.id)}
                    onClick={(e) => e.stopPropagation()}
                    className="pointer-events-auto inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-lg transition hover:bg-brand-50 hover:text-brand-700"
                  >
                    <Pencil className="h-4 w-4" />
                    Sunting
                  </Link>
                  <button
                    type="button"
                    onClick={(e) => { e.stopPropagation(); handleDelete(design.id); }}
                    className="pointer-events-auto inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-rose-600 shadow-lg transition hover:bg-rose-50"
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

      {/* Lightbox Modal */}
      {preview && (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
          onClick={() => setPreview(null)}
        >
          {/* Close button */}
          <button
            onClick={() => setPreview(null)}
            className="absolute right-4 top-4 z-10 rounded-xl bg-white/10 p-2 text-white backdrop-blur-md transition hover:bg-white/20"
          >
            <X className="h-6 w-6" />
          </button>

          {/* Image */}
          <div
            className="relative max-h-[85vh] max-w-[85vw]"
            onClick={(e) => e.stopPropagation()}
          >
            {preview.image_path ? (
              <img
                src={`/storage/${preview.image_path}`}
                alt={preview.name}
                className="max-h-[85vh] max-w-[85vw] rounded-2xl object-contain shadow-2xl"
              />
            ) : (
              <div className="flex h-64 w-64 items-center justify-center rounded-2xl bg-slate-800">
                <ImageIcon className="h-16 w-16 text-slate-600" />
              </div>
            )}

            {/* Caption */}
            <div className="absolute bottom-0 left-0 right-0 rounded-b-2xl bg-gradient-to-t from-black/70 to-transparent p-6 pt-12">
              <p className="text-lg font-bold text-white">{preview.name}</p>
              <p className="text-sm text-slate-300">{preview.category?.name || '-'}</p>
            </div>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
