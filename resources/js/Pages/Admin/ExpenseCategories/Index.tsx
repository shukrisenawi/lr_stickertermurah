import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { Pencil, Plus, Tag, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

interface ExpenseCategory {
  id: number;
  name: string;
  expenses_count: number;
}

interface ExpenseCategoriesProps {
  categories: ExpenseCategory[];
}

export default function ExpenseCategoriesIndex({ categories }: ExpenseCategoriesProps) {
  const [showForm, setShowForm] = useState(false);
  const [editingCategory, setEditingCategory] = useState<ExpenseCategory | null>(null);
  const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({ name: '' });

  const openCreate = () => {
    reset();
    setEditingCategory(null);
    setShowForm(true);
  };

  const openEdit = (category: ExpenseCategory) => {
    setData('name', category.name);
    setEditingCategory(category);
    setShowForm(true);
  };

  const closeForm = () => {
    if (processing) return;

    setShowForm(false);
    setEditingCategory(null);
    reset();
  };

  useEffect(() => {
    if (!showForm) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key !== 'Escape' || processing) return;

      setShowForm(false);
      setEditingCategory(null);
      reset();
    };

    window.addEventListener('keydown', closeOnEscape);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', closeOnEscape);
    };
  }, [showForm, processing, reset]);

  const submitCategory = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (editingCategory) {
      put(route('admin.expense-categories.update', editingCategory.id), { onSuccess: closeForm });
      return;
    }

    post(route('admin.expense-categories.store'), { onSuccess: closeForm });
  };

  const handleDelete = (category: ExpenseCategory) => {
    const message = category.expenses_count > 0
      ? `Kategori "${category.name}" digunakan oleh ${category.expenses_count} rekod. Padam kategori? Rekod akan kekal tanpa kategori.`
      : `Padam kategori "${category.name}"?`;

    if (!window.confirm(message)) return;

    destroy(route('admin.expense-categories.destroy', category.id));
  };

  return (
    <AdminLayout>
      <Head title="Kategori Duit Keluar" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <Tag className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Tetapan Kewangan</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Kategori Duit Keluar</h2>
            <p className="admin-page-copy">Urus kategori yang digunakan untuk mengelaskan rekod pembelian dan perbelanjaan.</p>
          </div>
          {!showForm && (
            <button type="button" onClick={openCreate} className="admin-btn-primary">
              <Plus className="h-4 w-4" />
              Tambah Kategori
            </button>
          )}
        </div>

        {showForm && (
          <div className="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/65 p-4 backdrop-blur-sm" role="presentation">
            <div className="w-full max-w-lg rounded-3xl border border-white/70 bg-slate-50 p-6 shadow-2xl shadow-slate-950/25" role="dialog" aria-modal="true" aria-labelledby="expense-category-modal-title">
              <div className="mb-5 flex items-start justify-between gap-4">
                <div>
                  <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-brand-600">Kategori Duit Keluar</p>
                  <h3 id="expense-category-modal-title" className="mt-1 text-lg font-bold text-slate-900">
                    {editingCategory ? 'Kemaskini Kategori' : 'Tambah Kategori Baru'}
                  </h3>
                </div>
                <button type="button" onClick={closeForm} disabled={processing} aria-label="Tutup borang kategori" className="rounded-full p-1.5 text-slate-400 transition hover:bg-white hover:text-slate-700 disabled:opacity-50">
                  <X className="h-5 w-5" />
                </button>
              </div>
              <form onSubmit={submitCategory} className="space-y-4">
                <div>
                  <label htmlFor="expense-category-name">Nama kategori</label>
                  <input
                    id="expense-category-name"
                    type="text"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    className="mt-1.5"
                    placeholder="Contoh: Bahan Mentah"
                  />
                  {errors.name && <p className="mt-1 text-xs text-rose-600">{errors.name}</p>}
                </div>
                <div className="flex justify-end gap-2">
                  <button type="button" onClick={closeForm} disabled={processing} className="admin-btn-secondary">Batal</button>
                  <button type="submit" disabled={processing} className="admin-btn-primary disabled:cursor-not-allowed disabled:opacity-60">
                    {processing ? 'Menyimpan...' : editingCategory ? 'Kemaskini' : 'Simpan'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nama Kategori</th>
                  <th>Bilangan Rekod</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {categories.length === 0 ? (
                  <tr>
                    <td colSpan={3} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Tag className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada kategori</p>
                        <p className="admin-table-empty-copy">Tambah kategori pertama untuk mula mengelaskan duit keluar.</p>
                      </div>
                    </td>
                  </tr>
                ) : categories.map((category) => (
                  <tr key={category.id}>
                    <td className="font-semibold text-slate-900">{category.name}</td>
                    <td className="text-slate-500">{category.expenses_count} rekod</td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <button type="button" onClick={() => openEdit(category)} className="rounded-lg p-2 text-slate-600 transition hover:bg-slate-50" aria-label={`Kemaskini kategori ${category.name}`}>
                          <Pencil className="h-4 w-4" />
                        </button>
                        <button type="button" onClick={() => handleDelete(category)} className="rounded-lg p-2 text-rose-600 transition hover:bg-rose-50" aria-label={`Padam kategori ${category.name}`}>
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
          <p className="text-sm text-slate-600">Memadam kategori tidak akan memadam rekod duit keluar. Rekod tersebut akan dipaparkan sebagai <strong>Tanpa kategori</strong>.</p>
        </div>
      </div>
    </AdminLayout>
  );
}
