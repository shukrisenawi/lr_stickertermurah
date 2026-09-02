import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeft, Plus, Trash2 } from 'lucide-react';

interface SizeRow {
  name: string;
  width_cm: string;
  height_cm: string;
}

interface SizeFormData {
  sizes: SizeRow[];
  shape: string;
  qty_per_a3: string;
  is_active: boolean;
  is_default: boolean;
  show: boolean;
}

function dimensionsFromName(name: string): Pick<SizeRow, 'width_cm' | 'height_cm'> | null {
  const match = name.match(/(\d+(?:[.,]\d+)?)\s*(?:cm)?\s*[x×]\s*(\d+(?:[.,]\d+)?)\s*(?:cm)?/i);

  if (!match) return null;

  return {
    width_cm: match[1].replace(',', '.'),
    height_cm: match[2].replace(',', '.'),
  };
}

export default function SizesCreate() {
  const [rowIds, setRowIds] = useState([0]);
  const { data, setData, post, processing, errors } = useForm<SizeFormData>({
    sizes: [{ name: '', width_cm: '', height_cm: '' }],
    shape: '',
    qty_per_a3: '',
    is_active: true,
    is_default: false,
    show: true,
  });

  const updateSize = (index: number, field: keyof SizeRow, value: string) => {
    setData('sizes', data.sizes.map((size, sizeIndex) => (
      sizeIndex === index ? { ...size, [field]: value } : size
    )));
  };

  const updateSizeName = (index: number, value: string) => {
    const dimensions = dimensionsFromName(value);

    setData('sizes', data.sizes.map((size, sizeIndex) => (
      sizeIndex === index
        ? { ...size, name: value, ...(dimensions ?? {}) }
        : size
    )));
  };

  const addSize = () => {
    setData('sizes', [...data.sizes, { name: '', width_cm: '', height_cm: '' }]);
    setRowIds((ids) => [...ids, (ids[ids.length - 1] ?? -1) + 1]);
  };

  const removeSize = (index: number) => {
    if (data.sizes.length === 1) return;

    setData('sizes', data.sizes.filter((_, sizeIndex) => sizeIndex !== index));
    setRowIds((ids) => ids.filter((_, sizeIndex) => sizeIndex !== index));
  };

  const sizeError = (index: number, field: keyof SizeRow) => errors[`sizes.${index}.${field}`];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.sizes.store'));
  };

  return (
    <AdminLayout>
      <Head title="Tambah Saiz" />
      <div className="max-w-3xl space-y-6">
        <Link href={route('admin.sizes.index')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 transition hover:text-brand-600">
          <ArrowLeft className="h-4 w-4" />
          Kembali
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Tambah Saiz</h2>
          <p className="admin-page-copy">Tambah satu atau beberapa saiz dengan kuantiti per A3 yang sama.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card space-y-6 p-6">
          <div className="space-y-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <h3 className="text-sm font-bold text-slate-900">Senarai Saiz</h3>
                <p className="mt-1 text-xs leading-relaxed text-slate-500">Masukkan nama dan ukuran bagi setiap saiz yang hendak ditambah.</p>
              </div>
              <button type="button" onClick={addSize} className="admin-btn-secondary text-sm">
                <Plus className="h-4 w-4" />
                Tambah Baris
              </button>
            </div>

            {data.sizes.map((size, index) => (
              <div key={rowIds[index]} className="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <div className="mb-4 flex items-center justify-between gap-3">
                  <p className="text-xs font-bold uppercase tracking-[0.16em] text-brand-600">Saiz {index + 1}</p>
                  {data.sizes.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeSize(index)}
                      className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                    >
                      <Trash2 className="h-3.5 w-3.5" />
                      Buang
                    </button>
                  )}
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="sm:col-span-2">
                    <label htmlFor={`size-name-${index}`} className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Saiz</label>
                    <input
                      id={`size-name-${index}`}
                      type="text"
                      required
                      value={size.name}
                      onChange={(e) => updateSizeName(index, e.target.value)}
                      placeholder="Contoh: 3cm x 3cm"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {sizeError(index, 'name') && <p className="mt-1 text-sm text-rose-600">{sizeError(index, 'name')}</p>}
                    <p className="mt-1 text-xs text-slate-400">Contoh `3x3cm`, `3 x 3cm` atau `3cm x 3cm` akan isi lebar dan tinggi secara automatik.</p>
                  </div>
                  <div>
                    <label htmlFor={`size-width-${index}`} className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Lebar (cm)</label>
                    <input
                      id={`size-width-${index}`}
                      type="number"
                      required
                      min="0.01"
                      step="0.01"
                      value={size.width_cm}
                      onChange={(e) => updateSize(index, 'width_cm', e.target.value)}
                      placeholder="Contoh: 3"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {sizeError(index, 'width_cm') && <p className="mt-1 text-sm text-rose-600">{sizeError(index, 'width_cm')}</p>}
                  </div>
                  <div>
                    <label htmlFor={`size-height-${index}`} className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tinggi (cm)</label>
                    <input
                      id={`size-height-${index}`}
                      type="number"
                      required
                      min="0.01"
                      step="0.01"
                      value={size.height_cm}
                      onChange={(e) => updateSize(index, 'height_cm', e.target.value)}
                      placeholder="Contoh: 3"
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {sizeError(index, 'height_cm') && <p className="mt-1 text-sm text-rose-600">{sizeError(index, 'height_cm')}</p>}
                  </div>
                </div>
              </div>
            ))}

            {errors.sizes && <p className="text-sm text-rose-600">{errors.sizes}</p>}
          </div>

          <div className="rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
            <h3 className="text-sm font-bold text-slate-900">Tetapan Dikongsi</h3>
            <p className="mt-1 text-xs leading-relaxed text-slate-600">Kuantiti per A3 dan tetapan di bawah akan digunakan untuk semua saiz dalam senarai.</p>

            <div className="mt-4 grid gap-4 sm:grid-cols-2">
              <div>
                <label htmlFor="shape" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Bentuk</label>
                <select id="shape" value={data.shape} onChange={(e) => setData('shape', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                  <option value="">Pilih bentuk</option>
                  <option value="Petak">Petak</option>
                  <option value="Segi Empat">Segi Empat</option>
                  <option value="Bulat">Bulat</option>
                  <option value="Oval">Oval</option>
                  <option value="Bebas">Bebas / Custom</option>
                </select>
                {errors.shape && <p className="mt-1 text-sm text-rose-600">{errors.shape}</p>}
              </div>
              <div>
                <label htmlFor="qty_per_a3" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kuantiti per A3</label>
                <input id="qty_per_a3" type="number" min="1" value={data.qty_per_a3} onChange={(e) => setData('qty_per_a3', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" placeholder="Contoh: 100" />
                {errors.qty_per_a3 && <p className="mt-1 text-sm text-rose-600">{errors.qty_per_a3}</p>}
              </div>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-6">
            <div className="flex items-center gap-3">
              <input id="is_active" type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
              <label htmlFor="is_active" className="text-sm text-slate-700">Aktif untuk pelanggan</label>
            </div>
            <div className="flex items-center gap-3">
              <input id="is_default" type="checkbox" checked={data.is_default} onChange={(e) => setData('is_default', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
              <label htmlFor="is_default" className="text-sm text-slate-700">Tanda sebagai default</label>
            </div>
            <div className="flex items-center gap-3">
              <input id="show" type="checkbox" checked={data.show} onChange={(e) => setData('show', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
              <label htmlFor="show" className="text-sm text-slate-700">Papar dalam dropdown perbandingan harga</label>
            </div>
          </div>

          <div className="flex items-center gap-3 border-t border-slate-100 pt-5">
            <Link href={route('admin.sizes.index')} className="admin-btn-secondary flex-1 text-sm">Batal</Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : `Simpan ${data.sizes.length} Saiz`}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
