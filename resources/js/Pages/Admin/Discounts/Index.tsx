import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Percent, Plus, Pencil, Trash2 } from 'lucide-react';

interface Design {
  id: number;
  name: string;
}

interface Size {
  id: number;
  name: string;
  shape: string | null;
}

interface Discount {
  id: number;
  name: string;
  sticker_design_id: number | null;
  sticker_size_id: number | null;
  min_qty: number;
  max_qty: number | null;
  type: string;
  value: number;
  is_active: boolean;
  design: Design | null;
  size: Size | null;
}

interface DiscountsIndexProps {
  discounts: {
    data: Discount[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

export default function DiscountsIndex({ discounts }: DiscountsIndexProps) {
  const { delete: destroy } = useForm();

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam diskaun ini?')) {
      destroy(route('admin.discounts.destroy', id));
    }
  };

  const formatValue = (discount: Discount) => {
    if (discount.type === 'fixed') {
      return `RM${Number(discount.value).toFixed(2)}`;
    }
    return `${Number(discount.value).toFixed(0)}%`;
  };

  const formatQty = (discount: Discount) => {
    if (discount.max_qty) {
      return `${discount.min_qty} - ${discount.max_qty}`;
    }
    return `${discount.min_qty}+`;
  };

  return (
    <AdminLayout>
      <Head title="Senarai Diskaun" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Senarai Diskaun</h2>
            <p className="admin-page-copy">Urus diskaun untuk sticker.</p>
          </div>
          <Link href={route('admin.discounts.create')} className="admin-btn-primary">
            <Plus className="h-4 w-4" />
            Tambah Diskaun
          </Link>
        </div>

        <div className="admin-table-card">
          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Design</th>
                  <th>Saiz</th>
                  <th>Kuantiti</th>
                  <th>Diskaun</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {discounts.data.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Percent className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada Diskaun</p>
                      </div>
                    </td>
                  </tr>
                ) : (
                  discounts.data.map((discount) => (
                    <tr key={discount.id}>
                      <td className="font-medium text-slate-900">{discount.name}</td>
                      <td>{discount.design?.name ?? 'Semua'}</td>
                      <td>{discount.size ? `${discount.size.name}${discount.size.shape ? ` (${discount.size.shape})` : ''}` : 'Semua'}</td>
                      <td className="font-medium">{formatQty(discount)}</td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${discount.type === 'percentage' ? 'bg-violet-100 text-violet-700' : 'bg-emerald-100 text-emerald-700'}`}>
                          {formatValue(discount)}
                        </span>
                      </td>
                      <td>
                        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${discount.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}`}>
                          {discount.is_active ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                      </td>
                      <td>
                        <div className="flex items-center gap-2">
                          <Link href={route('admin.discounts.edit', discount.id)} className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                            <Pencil className="h-4 w-4" />
                          </Link>
                          <button type="button" onClick={() => handleDelete(discount.id)} className="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-rose-600 hover:bg-rose-50 transition">
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
          {discounts.links.length > 3 && (
            <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
              <div className="flex items-center gap-2">
                {discounts.links.map((link) => {
                  const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
                  return link.url ? (
                    <Link key={link.label} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>
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
