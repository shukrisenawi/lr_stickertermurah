import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Calculator, Save } from 'lucide-react';

interface OrderSettingsProps {
  minimumA3SheetsWithoutDesign: number;
}

export default function OrderSettings({ minimumA3SheetsWithoutDesign }: OrderSettingsProps) {
  const { data, setData, put, processing, errors } = useForm({
    minimum_a3_sheets_without_design: minimumA3SheetsWithoutDesign,
  });

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    put(route('admin.settings.order.update'));
  };

  return (
    <AdminLayout>
      <Head title="Tetapan Minimum Order" />
      <div className="max-w-2xl space-y-6">
        <Link href={route('admin.dashboard')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-brand-600">
          <ArrowLeft className="h-4 w-4" />
          Dashboard
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Tetapan Minimum Order</h2>
          <p className="admin-page-copy">Tetapkan jumlah minimum helai A3 untuk order yang belum mempunyai design.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card space-y-6 p-6">
          <div className="flex items-start gap-3 rounded-2xl border border-brand-100 bg-brand-50/70 p-4 text-brand-900">
            <Calculator className="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
            <div>
              <p className="text-sm font-bold">Minimum kertas untuk order tanpa design</p>
              <p className="mt-1 text-xs leading-relaxed text-brand-800">Nilai ini digunakan dalam kiraan harga, invoice dan paparan kepada customer. Order dengan design siap masih boleh bermula daripada 1 helai A3.</p>
            </div>
          </div>

          <div>
            <label htmlFor="minimum-a3-sheets-without-design">Jumlah minimum helai A3</label>
            <input
              id="minimum-a3-sheets-without-design"
              type="number"
              min="1"
              max="1000"
              step="1"
              value={data.minimum_a3_sheets_without_design}
              onChange={(event) => setData('minimum_a3_sheets_without_design', Number(event.target.value))}
              className="mt-1.5"
            />
            {errors.minimum_a3_sheets_without_design && <p className="mt-1 text-sm text-rose-600">{errors.minimum_a3_sheets_without_design}</p>}
            <p className="mt-2 text-xs text-slate-500">Contoh: masukkan 3 untuk minimum 3 helai A3, atau ubah kepada jumlah yang diperlukan.</p>
          </div>

          <button type="submit" disabled={processing} className="admin-btn-primary text-sm">
            <Save className="h-4 w-4" />
            {processing ? 'Menyimpan...' : 'Simpan Tetapan'}
          </button>
        </form>
      </div>
    </AdminLayout>
  );
}
