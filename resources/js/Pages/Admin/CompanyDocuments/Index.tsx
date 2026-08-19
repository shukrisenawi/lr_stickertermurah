import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Download, FileText, FolderLock, Search, Trash2, Upload } from 'lucide-react';
import { useRef } from 'react';
import { formatDate } from '@/lib/utils';

interface CompanyDocument {
  id: number;
  title: string;
  category: string;
  category_label: string;
  notes: string | null;
  original_name: string;
  mime_type: string | null;
  file_size: number;
  download_url: string;
  created_at: string;
  uploader: { name: string; email: string | null } | null;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface CompanyDocumentsProps {
  documents: {
    data: CompanyDocument[];
    links: PaginationLink[];
  };
  filters: {
    search: string;
    category: string;
  };
  categories: Array<{ value: string; label: string }>;
  maxFileSizeMb: number;
}

interface UploadFormData {
  title: string;
  category: string;
  notes: string;
  file: File | null;
}

function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function fileTypeLabel(mimeType: string | null): string {
  if (!mimeType) return 'Fail';
  if (mimeType === 'application/pdf') return 'PDF';
  if (mimeType.startsWith('image/')) return 'Imej';
  if (mimeType.includes('spreadsheet') || mimeType.includes('excel')) return 'Excel';
  if (mimeType.includes('word') || mimeType.includes('document')) return 'Word';
  return 'Fail';
}

export default function CompanyDocumentsIndex({ documents, filters, categories, maxFileSizeMb }: CompanyDocumentsProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const uploadForm = useForm<UploadFormData>({
    title: '',
    category: 'other',
    notes: '',
    file: null,
  });
  const filterForm = useForm({
    q: filters.search,
    category: filters.category,
  });
  const deleteForm = useForm();

  const submitUpload = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!uploadForm.data.file) return;

    uploadForm.post(route('admin.company-documents.store'), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        uploadForm.reset();
        if (fileInputRef.current) fileInputRef.current.value = '';
      },
    });
  };

  const submitFilters = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    filterForm.get(route('admin.company-documents.index'), { preserveState: true, replace: true });
  };

  const handleDelete = (document: CompanyDocument) => {
    if (! window.confirm(`Padam dokumen "${document.title}"?`)) return;

    deleteForm.delete(route('admin.company-documents.destroy', document.id), { preserveScroll: true });
  };

  return (
    <AdminLayout>
      <Head title="Dokumen Syarikat" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <FolderLock className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Simpanan Private</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Dokumen Syarikat</h2>
            <p className="admin-page-copy">Simpan SSM, resit, lesen dan dokumen penting syarikat di satu tempat.</p>
          </div>
        </div>

        <div className="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4">
          <div className="flex items-start gap-3">
            <FolderLock className="mt-0.5 h-5 w-5 shrink-0 text-blue-700" />
            <div>
              <p className="text-sm font-semibold text-blue-900">Dokumen ini hanya boleh diakses oleh admin</p>
              <p className="mt-0.5 text-xs leading-5 text-blue-700">Fail disimpan dalam storan private dan tidak mempunyai URL public.</p>
            </div>
          </div>
        </div>

        <form onSubmit={submitUpload} className="admin-flat-card p-5 sm:p-6">
          <div className="flex items-start gap-3 border-b border-slate-100 pb-5">
            <div className="admin-icon-badge">
              <Upload className="h-5 w-5" />
            </div>
            <div>
              <h3 className="font-bold text-slate-900">Muat Naik Dokumen</h3>
              <p className="mt-0.5 text-sm text-slate-500">PDF, imej, Word atau Excel. Maksimum {maxFileSizeMb}MB setiap fail.</p>
            </div>
          </div>

          <div className="mt-5 grid gap-4 lg:grid-cols-[1fr_220px]">
            <div>
              <label htmlFor="document-title">Nama dokumen</label>
              <input
                id="document-title"
                type="text"
                value={uploadForm.data.title}
                onChange={(event) => uploadForm.setData('title', event.target.value)}
                className="mt-1.5"
                placeholder="Contoh: SSM Syarikat 2026"
                required
              />
              {uploadForm.errors.title && <p className="mt-1 text-xs text-rose-600">{uploadForm.errors.title}</p>}
            </div>
            <div>
              <label htmlFor="document-category">Kategori</label>
              <select
                id="document-category"
                value={uploadForm.data.category}
                onChange={(event) => uploadForm.setData('category', event.target.value)}
                className="mt-1.5"
              >
                {categories.map((category) => <option key={category.value} value={category.value}>{category.label}</option>)}
              </select>
              {uploadForm.errors.category && <p className="mt-1 text-xs text-rose-600">{uploadForm.errors.category}</p>}
            </div>
            <div className="lg:col-span-2">
              <label htmlFor="document-notes">Nota (pilihan)</label>
              <textarea
                id="document-notes"
                rows={2}
                value={uploadForm.data.notes}
                onChange={(event) => uploadForm.setData('notes', event.target.value)}
                className="mt-1.5"
                placeholder="Contoh: Perlu diperbaharui pada bulan Disember"
              />
              {uploadForm.errors.notes && <p className="mt-1 text-xs text-rose-600">{uploadForm.errors.notes}</p>}
            </div>
            <div className="lg:col-span-2">
              <input
                ref={fileInputRef}
                id="company-document-file"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv"
                className="sr-only"
                onChange={(event) => uploadForm.setData('file', event.target.files?.[0] ?? null)}
              />
              <label htmlFor="company-document-file" className="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center transition hover:border-brand-300 hover:bg-brand-50/40">
                <FileText className="h-9 w-9 text-slate-400" />
                <span className="mt-3 text-sm font-semibold text-slate-700">{uploadForm.data.file?.name ?? 'Pilih fail untuk dimuat naik'}</span>
                <span className="mt-1 text-xs text-slate-500">PDF, JPG, PNG, WEBP, DOC, DOCX, XLS, XLSX atau CSV</span>
              </label>
              {uploadForm.errors.file && <p className="mt-1 text-xs text-rose-600">{uploadForm.errors.file}</p>}
            </div>
          </div>

          <div className="mt-5 flex justify-end">
            <button type="submit" disabled={uploadForm.processing || !uploadForm.data.file} className="admin-btn-primary disabled:cursor-not-allowed disabled:opacity-60">
              <Upload className="h-4 w-4" />
              {uploadForm.processing ? 'Memuat naik...' : 'Simpan Dokumen'}
            </button>
          </div>
        </form>

        <form onSubmit={submitFilters} className="admin-toolbar-card">
          <div className="grid flex-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px]">
            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
              <input
                type="search"
                value={filterForm.data.q}
                onChange={(event) => filterForm.setData('q', event.target.value)}
                className="pl-10"
                placeholder="Cari nama atau nama fail..."
              />
            </div>
            <label htmlFor="document-filter-category" className="sr-only">Tapis kategori dokumen</label>
            <select id="document-filter-category" value={filterForm.data.category} onChange={(event) => filterForm.setData('category', event.target.value)}>
              <option value="">Semua kategori</option>
              {categories.map((category) => <option key={category.value} value={category.value}>{category.label}</option>)}
            </select>
          </div>
          <button type="submit" className="admin-btn-secondary text-sm">Cari</button>
        </form>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Dokumen</th>
                  <th>Kategori</th>
                  <th>Fail</th>
                  <th>Dimuat naik</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {documents.data.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <FolderLock className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada dokumen</p>
                        <p className="admin-table-empty-copy">Muat naik dokumen pertama untuk mula menyimpan rekod syarikat.</p>
                      </div>
                    </td>
                  </tr>
                ) : documents.data.map((document) => (
                  <tr key={document.id}>
                    <td className="min-w-56">
                      <p className="font-semibold text-slate-900">{document.title}</p>
                      {document.notes && <p className="mt-0.5 max-w-sm truncate text-xs text-slate-500">{document.notes}</p>}
                    </td>
                    <td><span className="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">{document.category_label}</span></td>
                    <td className="min-w-52">
                      <div className="flex items-center gap-2">
                        <FileText className="h-4 w-4 shrink-0 text-slate-400" />
                        <div className="min-w-0">
                          <p className="max-w-52 truncate text-sm text-slate-700" title={document.original_name}>{document.original_name}</p>
                          <p className="text-xs text-slate-400">{fileTypeLabel(document.mime_type)} · {formatBytes(document.file_size)}</p>
                        </div>
                      </div>
                    </td>
                    <td className="min-w-36">
                      <p className="text-sm text-slate-700">{document.uploader?.name ?? '-'}</p>
                      <p className="text-xs text-slate-400">{formatDate(document.created_at)}</p>
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <a href={document.download_url} className="rounded-lg p-2 text-brand-600 transition hover:bg-brand-50" aria-label={`Muat turun ${document.title}`}>
                          <Download className="h-4 w-4" />
                        </a>
                        <button type="button" onClick={() => handleDelete(document)} className="rounded-lg p-2 text-rose-500 transition hover:bg-rose-50" aria-label={`Padam ${document.title}`}>
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {documents.links.length > 3 && (
            <div className="flex flex-wrap gap-2 border-t border-slate-200 px-5 py-4">
              {documents.links.map((link) => {
                const label = link.label.replace(/&laquo;/g, 'Sebelum').replace(/&raquo;/g, 'Seterusnya');
                return link.url ? (
                  <Link key={`${link.label}-${link.url}`} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>{label}</Link>
                ) : <span key={`${label}-disabled`} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">{label}</span>;
              })}
            </div>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
