import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Plus, Pencil, Trash2, X, DollarSign } from 'lucide-react';

interface PriceSetting {
  id: number;
  sticker_type: string;
  qty_from: number;
  qty_to: number | null;
  price_per_a3: number;
  is_active: boolean;
}

interface PriceSettingsIndexProps {
  priceSettings: PriceSetting[];
}

export default function PriceSettingsIndex({ priceSettings }: PriceSettingsIndexProps) {
  const [showForm, setShowForm] = useState(false);
  const [editing, setEditing] = useState<PriceSetting | null>(null);

  const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
    sticker_type: '',
    qty_from: '',
    qty_to: '',
    price_per_a3: '',
    is_active: true,
  });

  const openCreate = () => {
    reset();
    setEditing(null);
    setShowForm(true);
  };

  const openEdit = (setting: PriceSetting) => {
    setData({
      sticker_type: setting.sticker_type,
      qty_from: String(setting.qty_from),
      qty_to: setting.qty_to ? String(setting.qty_to) : '',
      price_per_a3: String(setting.price_per_a3),
      is_active: setting.is_active,
    });
    setEditing(setting);
    setShowForm(true);
  };

  const closeForm = () => {
    setShowForm(false);
    setEditing(null);
    reset();
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editing) {
      put(route('admin.price-settings.update', editing.id), {
        onSuccess: closeForm,
      });
    } else {
      post(route('admin.price-settings.store'), {
        onSuccess: closeForm,
      });
    }
  };

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam harga ini?')) {
      destroy(route('admin.price-settings.destroy', id));
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', { style: 'currency', currency: 'MYR' }).format(amount);
  };

  return (
    <AdminLayout>
      <Head title="Tetapan Harga" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Tetapan Harga</h2>
            <p className="admin-page-copy">Urus harga per A3 berdasarkan jenis sticker dan julat kuantiti.</p>
          </div>
          {!showForm && (
            <button type="button" onClick={openCreate} className="admin-btn-primary">
              <Plus className="h-4 w-4" />
              Tambah Harga
            </button>
          )}
        </div>

        {/* Add/Edit Form */}
        {showForm && (
          <div className="admin-flat-card p-6">
            <div className="flex items-center justify-between mb-5">
              <h3 className="text-lg font-bold text-slate-900">
                {editing ? 'Kemaskini Harga' : 'Tambah Harga Baru'}
              </h3>
              <button type="button" onClick={closeForm} className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                <X className="h-5 w-5" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Jenis Sticker</label>
                <select value={data.sticker_type} onChange={(e) => setData('sticker_type', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100">
                  <option value="Mirrorcote">Mirrorcote</option>
                  <option value="Glossy">Glossy</option>
                  <option value="Matte">Matte</option>
                  <option value="Clear">Clear</option>
                  <option value="Holographic">Holographic</option>
                </select>
                {errors.sticker_type && <p className="mt-1 text-xs text-rose-600">{errors.sticker_type}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Qty Dari (A3)</label>
                <input type="number" min="1" value={data.qty_from} onChange={(e) => setData('qty_from', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
                {errors.qty_from && <p className="mt-1 text-xs text-rose-600">{errors.qty_from}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Qty Hingga (A3)</label>
                <input type="number" min="1" value={data.qty_to} onChange={(e) => setData('qty_to', e.target.value)} placeholder="Tak terhad" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
                {errors.qty_to && <p className="mt-1 text-xs text-rose-600">{errors.qty_to}</p>}
              </div>
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Harga per A3 (RM)</label>
                <input type="number" step="0.01" min="0" value={data.price_per_a3} onChange={(e) => setData('price_per_a3', e.target.value)} className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100" />
                {errors.price_per_a3 && <p className="mt-1 text-xs text-rose-600">{errors.price_per_a3}</p>}
              </div>
              <div className="flex items-end gap-2">
                <div className="flex items-center gap-2 pb-2.5">
                  <input id="is_active" type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500" />
                  <label htmlFor="is_active" className="text-sm text-slate-700">Aktif</label>
                </div>
                <button type="submit" disabled={processing} className="admin-btn-primary text-sm">
                  {processing ? 'Menyimpan...' : editing ? 'Kemaskini' : 'Simpan'}
                </button>
              </div>
            </form>
          </div>
        )}

        {/* Price Settings List */}
        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Jenis Sticker</th>
                  <th>Julat Kuantiti (A3)</th>
                  <th>Harga per A3</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {priceSettings.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <DollarSign className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Tetapan Harga</p>
                        <p className="admin-table-empty-desc">Klik "Tambah Harga" untuk mula.</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  priceSettings.map((setting) => (
                    <tr key={setting.id}>
                      <td className="font-medium text-slate-900">{setting.sticker_type}</td>
                      <td>
                        {setting.qty_from} - {setting.qty_to ?? '∞'}
                      </td>
                      <td className="font-medium">{formatCurrency(setting.price_per_a3)}</td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${setting.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                          {setting.is_active ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                      </td>
                      <td>
                        <div className="flex items-center gap-2">
                          <button
                            type="button"
                            onClick={() => openEdit(setting)}
                            className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                          >
                            <Pencil className="h-4 w-4" />
                          </button>
                          <button
                            type="button"
                            onClick={() => handleDelete(setting.id)}
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
        </div>

        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-5">
          <p className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Cara Kiraan Harga</p>
          <p className="text-sm text-slate-600">
            Harga dikira berdasarkan: <code className="rounded bg-slate-200 px-1.5 py-0.5 text-xs font-mono">ceil(kuantiti / qty_per_A3) × harga_per_A3</code>
          </p>
          <p className="text-sm text-slate-600 mt-1">
            Contoh: Saiz 5×5cm (41 sticker/A3), beli 100 pcs → <code className="rounded bg-slate-200 px-1.5 py-0.5 text-xs font-mono">ceil(100/41) = 3 A3</code>. Jika harga RM8/A3 → <strong>RM24</strong>.
          </p>
        </div>
      </div>
    </AdminLayout>
  );
}
