import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Ruler, Plus, Pencil, Trash2, Eye, EyeOff } from 'lucide-react';

interface Size {
  id: number;
  name: string;
  width_cm: number;
  height_cm: number;
  shape: string | null;
  qty_per_a3: number | null;
  is_active: boolean;
  is_default: boolean;
  show: boolean;
}

interface SizesIndexProps {
  sizes: {
    data: Size[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function SizesIndex({ sizes }: SizesIndexProps) {
  const { delete: destroy } = useForm();
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [visibilityProcessing, setVisibilityProcessing] = useState(false);
  const selectAllRef = useRef<HTMLInputElement>(null);
  const currentPageIds = sizes.data.map((size) => size.id);
  const allSelected = currentPageIds.length > 0 && currentPageIds.every((id) => selectedIds.includes(id));
  const someSelected = selectedIds.some((id) => currentPageIds.includes(id));

  useEffect(() => {
    if (selectAllRef.current) {
      selectAllRef.current.indeterminate = someSelected && !allSelected;
    }
  }, [allSelected, someSelected]);

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam saiz ini?')) {
      destroy(route('admin.sizes.destroy', id));
    }
  };

  const toggleSelection = (id: number) => {
    setSelectedIds((ids) => ids.includes(id) ? ids.filter((selectedId) => selectedId !== id) : [...ids, id]);
  };

  const toggleSelectAll = () => {
    setSelectedIds((ids) => allSelected
      ? ids.filter((id) => !currentPageIds.includes(id))
      : [...new Set([...ids, ...currentPageIds])]);
  };

  const updateVisibility = (show: boolean) => {
    if (selectedIds.length === 0 || visibilityProcessing) return;

    setVisibilityProcessing(true);
    router.patch(route('admin.sizes.visibility.update'), {
      size_ids: selectedIds,
      show,
    }, {
      preserveScroll: true,
      onSuccess: () => setSelectedIds([]),
      onFinish: () => setVisibilityProcessing(false),
    });
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

        {selectedIds.length > 0 && (
          <div className="flex flex-col gap-3 rounded-2xl border border-brand-200 bg-brand-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm font-semibold text-brand-800">{selectedIds.length} saiz dipilih</p>
            <div className="flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => updateVisibility(true)}
                disabled={visibilityProcessing}
                className="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <Eye className="h-4 w-4" />
                Papar
              </button>
              <button
                type="button"
                onClick={() => updateVisibility(false)}
                disabled={visibilityProcessing}
                className="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
              >
                <EyeOff className="h-4 w-4" />
                Unshow
              </button>
            </div>
          </div>
        )}

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th className="w-12">
                    <input
                      ref={selectAllRef}
                      type="checkbox"
                      checked={allSelected}
                      onChange={toggleSelectAll}
                      aria-label="Pilih semua saiz pada halaman ini"
                      className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                  </th>
                  <th>Nama</th>
                  <th>Saiz (cm)</th>
                  <th>Bentuk</th>
                  <th>Qty/A3</th>
                  <th>Default</th>
                  <th>Papar Harga</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {sizes.data.length === 0 ? (
                  <tr>
                    <td colSpan={9} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Ruler className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Saiz</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  sizes.data.map((size) => (
                    <tr key={size.id}>
                      <td>
                        <input
                          type="checkbox"
                          checked={selectedIds.includes(size.id)}
                          onChange={() => toggleSelection(size.id)}
                          aria-label={`Pilih ${size.name}`}
                          className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                        />
                      </td>
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
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${size.show ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                          {size.show ? 'Ya' : 'Tidak'}
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
                    <Link key={link.label} href={link.url} preserveState className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>
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
