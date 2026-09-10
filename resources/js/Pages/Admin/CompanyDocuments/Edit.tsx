import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, FileText, Save } from 'lucide-react';

interface CompanyDocument {
  id: number;
  title: string;
  category: string;
  notes: string | null;
  original_name: string;
}

interface CompanyDocumentsEditProps {
  document: CompanyDocument;
  categories: Array<{ value: string; label: string }>;
}

export default function CompanyDocumentsEdit({ document, categories }: CompanyDocumentsEditProps) {
  const { data, setData, put, processing, errors } = useForm({
    title: document.title,
    category: document.category,
    notes: document.notes ?? '',
  });

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    put(route('admin.company-documents.update', document.id));
  };

  return (
    <AdminLayout>
      <Head title="Kemaskini Dokumen" />
      <div className="max-w-xl space-y-6">
        <div className="admin-page-head">
          <div>
            <div className="mb-2 flex items-center gap-2 text-brand-600">
              <FileText className="h-5 w-5" />
              <span className="text-xs font-bold uppercase tracking-[0.18em]">Dokumen Syarikat</span>
            </div>
            <h2 className="text-2xl font-bold text-slate-900">Kemaskini Dokumen</h2>
            <p className="admin-page-copy">Sunting nama, kategori atau nota dokumen.</p>
          </div>
          <Link href={route('admin.company-documents.index', { category: document.category })} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card space-y-5 p-6">
          <div>
            <label htmlFor="company-document-title">Nama dokumen</label>
            <input
              id="company-document-title"
              type="text"
              value={data.title}
              onChange={(event) => setData('title', event.target.value)}
              className="mt-1.5"
              required
            />
            {errors.title && <p className="mt-1 text-xs text-rose-600">{errors.title}</p>}
          </div>

          <div>
            <label htmlFor="company-document-category">Kategori</label>
            <select
              id="company-document-category"
              value={data.category}
              onChange={(event) => setData('category', event.target.value)}
              className="mt-1.5"
            >
              {categories.map((category) => <option key={category.value} value={category.value}>{category.label}</option>)}
            </select>
            {errors.category && <p className="mt-1 text-xs text-rose-600">{errors.category}</p>}
          </div>

          <div>
            <label htmlFor="company-document-notes">Nota (pilihan)</label>
            <textarea
              id="company-document-notes"
              rows={3}
              value={data.notes}
              onChange={(event) => setData('notes', event.target.value)}
              className="mt-1.5"
            />
            {errors.notes && <p className="mt-1 text-xs text-rose-600">{errors.notes}</p>}
          </div>

          <div className="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Fail asal</p>
            <p className="mt-1 truncate text-sm text-slate-700">{document.original_name}</p>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <button type="submit" disabled={processing} className="admin-btn-primary text-sm disabled:cursor-not-allowed disabled:opacity-60">
              <Save className="h-4 w-4" />
              {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
            </button>
            <Link href={route('admin.company-documents.index', { category: document.category })} className="admin-btn-secondary text-sm">
              Batal
            </Link>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
