import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowDownCircle, CalendarDays, Download, Image as ImageIcon, Receipt, Tag, Trash2, Upload, Wallet, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { formatDate } from '@/lib/utils';

interface ExpenseRecord {
  id: number;
  expense_category_id: number | null;
  description: string;
  amount: number;
  purchase_date: string;
  notes: string | null;
  receipt_original_name: string | null;
  receipt_mime_type: string | null;
  receipt_file_size: number;
  receipt_url: string | null;
  receipt_preview_url: string | null;
  created_at: string;
  creator: { name: string; email: string | null } | null;
  category: { id: number; name: string } | null;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface ExpensesProps {
  expenses: {
    data: ExpenseRecord[];
    links: PaginationLink[];
  };
  totalAmount: number;
  totalCount: number;
  today: string;
  maxReceiptSizeMb: number;
  categories: Array<{ id: number; name: string }>;
}

interface ExpenseFormData {
  expense_category_id: string;
  description: string;
  amount: string;
  purchase_date: string;
  notes: string;
  receipt: File | null;
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(amount);
}

function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export default function ExpensesIndex({ expenses, totalAmount, totalCount, today, maxReceiptSizeMb, categories }: ExpensesProps) {
  const [previewExpense, setPreviewExpense] = useState<ExpenseRecord | null>(null);
  const receiptInputRef = useRef<HTMLInputElement>(null);
  const defaultCategoryId = categories[0] ? String(categories[0].id) : '';
  const expenseForm = useForm<ExpenseFormData>({
    expense_category_id: defaultCategoryId,
    description: '',
    amount: '',
    purchase_date: today,
    notes: '',
    receipt: null,
  });
  const deleteForm = useForm();

  useEffect(() => {
    if (!previewExpense) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setPreviewExpense(null);
    };

    window.addEventListener('keydown', closeOnEscape);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', closeOnEscape);
    };
  }, [previewExpense]);

  const submitExpense = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    expenseForm.post(route('admin.expenses.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        expenseForm.reset();
        if (receiptInputRef.current) receiptInputRef.current.value = '';
      },
    });
  };

  const removeReceipt = () => {
    expenseForm.setData('receipt', null);
    if (receiptInputRef.current) receiptInputRef.current.value = '';
  };

  const handleDelete = (expense: ExpenseRecord) => {
    if (!window.confirm(`Padam rekod "${expense.description}"?`)) return;

    deleteForm.delete(route('admin.expenses.destroy', expense.id), { preserveScroll: true });
  };

  return (
    <AdminLayout>
      <Head title="Duit Keluar" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <ArrowDownCircle className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Rekod Kewangan</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Duit Keluar</h2>
            <p className="admin-page-copy">Catat pembelian dan perbelanjaan syarikat bersama resit sebagai rujukan.</p>
          </div>
          <div className="admin-page-actions">
            <Link href={route('admin.expense-categories.index')} className="admin-btn-secondary">
              <Tag className="h-4 w-4" />
              Urus Kategori
            </Link>
          </div>
        </div>

        <div className="grid gap-4 sm:grid-cols-2">
          <div className="admin-stat-card flex items-center gap-4">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
              <Wallet className="h-5 w-5" />
            </div>
            <div>
              <p className="admin-mini-label">Jumlah duit keluar</p>
              <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900">{formatCurrency(totalAmount)}</p>
            </div>
          </div>
          <div className="admin-stat-card flex items-center gap-4">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
              <Receipt className="h-5 w-5" />
            </div>
            <div>
              <p className="admin-mini-label">Bilangan rekod</p>
              <p className="mt-1 text-2xl font-bold tracking-tight text-slate-900">{totalCount}</p>
            </div>
          </div>
        </div>

        <form onSubmit={submitExpense} className="admin-flat-card p-5 sm:p-6">
          <div className="flex items-start gap-3 border-b border-slate-100 pb-5">
            <div className="admin-icon-badge">
              <ArrowDownCircle className="h-5 w-5" />
            </div>
            <div>
              <h3 className="font-bold text-slate-900">Tambah Rekod Duit Keluar</h3>
              <p className="mt-0.5 text-sm text-slate-500">Masukkan maklumat pembelian dan upload gambar resit jika ada.</p>
            </div>
          </div>

          <div className="mt-5 grid gap-4 md:grid-cols-2">
            <div>
              <label htmlFor="expense-category">Kategori</label>
              <select
                id="expense-category"
                value={expenseForm.data.expense_category_id}
                onChange={(event) => expenseForm.setData('expense_category_id', event.target.value)}
                className="mt-1.5"
                disabled={categories.length === 0}
              >
                <option value="">Pilih kategori</option>
                {categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
              </select>
              {categories.length === 0 && (
                <p className="mt-1 text-xs text-amber-700">
                  Sila <Link href={route('admin.expense-categories.index')} className="font-semibold underline">tambah kategori</Link> dahulu.
                </p>
              )}
              {expenseForm.errors.expense_category_id && <p className="mt-1 text-xs text-rose-600">{expenseForm.errors.expense_category_id}</p>}
            </div>

            <div className="md:col-span-2">
              <label htmlFor="expense-description">Keterangan pembelian</label>
              <input
                id="expense-description"
                type="text"
                value={expenseForm.data.description}
                onChange={(event) => expenseForm.setData('description', event.target.value)}
                className="mt-1.5"
                placeholder="Contoh: Beli ink printer dan kertas"
              />
              {expenseForm.errors.description && <p className="mt-1 text-xs text-rose-600">{expenseForm.errors.description}</p>}
            </div>

            <div>
              <label htmlFor="expense-amount">Jumlah (RM)</label>
              <input
                id="expense-amount"
                type="number"
                min="0.01"
                step="0.01"
                value={expenseForm.data.amount}
                onChange={(event) => expenseForm.setData('amount', event.target.value)}
                className="mt-1.5"
                placeholder="0.00"
              />
              {expenseForm.errors.amount && <p className="mt-1 text-xs text-rose-600">{expenseForm.errors.amount}</p>}
            </div>

            <div>
              <label htmlFor="expense-purchase-date">Tarikh pembelian</label>
              <div className="relative mt-1.5">
                <CalendarDays className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  id="expense-purchase-date"
                  type="date"
                  value={expenseForm.data.purchase_date}
                  onChange={(event) => expenseForm.setData('purchase_date', event.target.value)}
                  className="pl-10"
                />
              </div>
              {expenseForm.errors.purchase_date && <p className="mt-1 text-xs text-rose-600">{expenseForm.errors.purchase_date}</p>}
            </div>

            <div className="md:col-span-2">
              <label htmlFor="expense-notes">Nota (pilihan)</label>
              <textarea
                id="expense-notes"
                rows={2}
                value={expenseForm.data.notes}
                onChange={(event) => expenseForm.setData('notes', event.target.value)}
                className="mt-1.5"
                placeholder="Contoh: Dibeli untuk kegunaan pejabat"
              />
              {expenseForm.errors.notes && <p className="mt-1 text-xs text-rose-600">{expenseForm.errors.notes}</p>}
            </div>

            <div className="md:col-span-2">
              <input
                ref={receiptInputRef}
                id="expense-receipt"
                type="file"
                accept=".jpg,.jpeg,.png,.webp"
                className="sr-only"
                onChange={(event) => expenseForm.setData('receipt', event.target.files?.[0] ?? null)}
              />
              <label htmlFor="expense-receipt" className="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center transition hover:border-brand-300 hover:bg-brand-50/40">
                <ImageIcon className="h-9 w-9 text-slate-400" />
                <span className="mt-3 text-sm font-semibold text-slate-700">
                  {expenseForm.data.receipt ? expenseForm.data.receipt.name : 'Pilih gambar resit'}
                </span>
                <span className="mt-1 text-xs text-slate-500">JPG, PNG atau WEBP · maksimum {maxReceiptSizeMb}MB</span>
              </label>
              {expenseForm.data.receipt && (
                <div className="mt-2 flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                  <span className="min-w-0 truncate">{formatBytes(expenseForm.data.receipt.size)}</span>
                  <button type="button" onClick={removeReceipt} className="shrink-0 font-semibold text-rose-600 hover:underline">Buang</button>
                </div>
              )}
              {expenseForm.errors.receipt && <p className="mt-1 text-xs text-rose-600">{expenseForm.errors.receipt}</p>}
            </div>
          </div>

          <div className="mt-5 flex justify-end">
            <button type="submit" disabled={expenseForm.processing || categories.length === 0} className="admin-btn-primary disabled:cursor-not-allowed disabled:opacity-60">
              <Upload className="h-4 w-4" />
              {expenseForm.processing ? 'Menyimpan...' : categories.length === 0 ? 'Tambah kategori dahulu' : 'Simpan Rekod'}
            </button>
          </div>
        </form>

        <div className="admin-table-card">
          <div className="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 className="font-bold text-slate-900">Senarai Rekod</h3>
              <p className="mt-0.5 text-sm text-slate-500">Rekod disusun daripada tarikh pembelian paling baharu.</p>
            </div>
            <span className="admin-soft-badge">{totalCount} rekod</span>
          </div>
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Tarikh</th>
                  <th>Kategori</th>
                  <th>Keterangan</th>
                  <th>Jumlah</th>
                  <th>Resit</th>
                  <th>Direkod oleh</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {expenses.data.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <ArrowDownCircle className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada rekod duit keluar</p>
                        <p className="admin-table-empty-copy">Gunakan borang di atas untuk merekod pembelian pertama.</p>
                      </div>
                    </td>
                  </tr>
                ) : expenses.data.map((expense) => (
                  <tr key={expense.id}>
                    <td className="min-w-32 whitespace-nowrap text-slate-700">{formatDate(expense.purchase_date)}</td>
                    <td className="min-w-36">
                      {expense.category ? (
                        <span className="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">{expense.category.name}</span>
                      ) : (
                        <span className="text-xs text-slate-400">Tanpa kategori</span>
                      )}
                    </td>
                    <td className="min-w-56">
                      <p className="font-semibold text-slate-900">{expense.description}</p>
                      {expense.notes && <p className="mt-0.5 max-w-sm truncate text-xs text-slate-500">{expense.notes}</p>}
                    </td>
                    <td className="whitespace-nowrap font-semibold text-rose-600">-{formatCurrency(expense.amount)}</td>
                    <td className="min-w-48">
                      {expense.receipt_preview_url ? (
                        <div className="flex items-center gap-2">
                          <button
                            type="button"
                            onClick={() => setPreviewExpense(expense)}
                            className="group relative h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-50"
                            aria-label={`Papar resit ${expense.description}`}
                          >
                            <img src={expense.receipt_preview_url} alt={`Resit ${expense.description}`} className="h-full w-full object-cover transition group-hover:scale-105" />
                          </button>
                          <div className="min-w-0">
                            <p className="max-w-40 truncate text-xs text-slate-700">{expense.receipt_original_name}</p>
                            <p className="text-[11px] text-slate-400">{formatBytes(expense.receipt_file_size)}</p>
                          </div>
                        </div>
                      ) : (
                        <span className="text-xs text-slate-400">Tiada resit</span>
                      )}
                    </td>
                    <td className="min-w-32 text-sm text-slate-500">{expense.creator?.name ?? '—'}</td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        {expense.receipt_url && (
                          <a href={expense.receipt_url} className="rounded-lg p-2 text-brand-600 transition hover:bg-brand-50" aria-label={`Muat turun resit ${expense.description}`}>
                            <Download className="h-4 w-4" />
                          </a>
                        )}
                        <button type="button" onClick={() => handleDelete(expense)} className="rounded-lg p-2 text-rose-500 transition hover:bg-rose-50" aria-label={`Padam rekod ${expense.description}`}>
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {expenses.links.length > 3 && (
            <div className="flex flex-wrap gap-2 border-t border-slate-200 px-5 py-4">
              {expenses.links.map((link) => {
                const label = link.label.replace(/&laquo;/g, 'Sebelum').replace(/&raquo;/g, 'Seterusnya');
                return link.url ? (
                  <Link key={`${link.label}-${link.url}`} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>{label}</Link>
                ) : <span key={`${label}-disabled`} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">{label}</span>;
              })}
            </div>
          )}
        </div>
      </div>

      {previewExpense && (
        <div
          role="dialog"
          aria-modal="true"
          aria-label={`Preview resit ${previewExpense.description}`}
          className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
        >
          <div className="relative flex max-h-[92vh] max-w-[92vw] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div className="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-3">
              <div className="min-w-0">
                <p className="truncate text-sm font-semibold text-slate-900">{previewExpense.description}</p>
                <p className="truncate text-xs text-slate-500">{previewExpense.receipt_original_name}</p>
              </div>
              <button type="button" onClick={() => setPreviewExpense(null)} className="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Tutup preview resit">
                <X className="h-5 w-5" />
              </button>
            </div>
            <div className="overflow-auto bg-slate-100 p-3 sm:p-6">
              <img src={previewExpense.receipt_preview_url ?? ''} alt={`Resit ${previewExpense.description}`} className="mx-auto max-h-[78vh] max-w-full rounded-lg object-contain shadow-sm" />
            </div>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
