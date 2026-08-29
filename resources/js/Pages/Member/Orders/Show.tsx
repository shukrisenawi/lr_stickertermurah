import MemberLayout from '@/Components/Layouts/MemberLayout';
import CustomQuoteCalculator from '@/Components/CustomQuoteCalculator';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, Clock3, Image as ImageIcon, MapPin, Package, Pencil, Phone, Receipt, User, X } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';
import { useEffect, useState } from 'react';

interface OrderItem {
  id: number;
  design: { id: number; name: string } | null;
  project: { id: number; title: string } | null;
  size: { id: number; name: string } | null;
  quantity: number;
  unit_price: number;
  line_total: number;
  quoted_qty_per_a3: number | null;
  quoted_price_per_a3: number | string | null;
  custom_design_description: string | null;
  requested_size: string | null;
  cut_type: 'standard' | 'die-cut';
  preview_url: string | null;
  preview_urls: string[];
}

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  material: string;
  status: string;
  tracking_no: string | null;
  subtotal: number;
  total: number;
  shipping_fee: number;
  shipping_region: string | null;
  pricing_status: string;
  price_note: string | null;
  custom_request: string | null;
  created_at: string;
  invoice: { id: number; invoice_no: string } | null;
  items: OrderItem[];
}

interface OrderShowProps {
  order: Order;
  itemEditOptions: {
    designs: Array<{ id: number; name: string }>;
    projects: Array<{ id: number; title: string }>;
    sizes: Array<{ id: number; name: string }>;
  };
}

interface PreviewImage {
  url: string;
  alt: string;
}

interface ItemEditFormData {
  design_id: number | '';
  project_id: number | '';
  size_id: number | '';
  custom_design_description: string;
  requested_size: string;
  quantity: number;
  cut_type: 'standard' | 'die-cut';
}

export default function MemberOrderShow({ order, itemEditOptions }: OrderShowProps) {
  const [previewImage, setPreviewImage] = useState<PreviewImage | null>(null);
  const [editingItem, setEditingItem] = useState<OrderItem | null>(null);
  const customQuoteItems = order.items.filter((item) => item.quoted_qty_per_a3 && item.quoted_price_per_a3);
  const canEditItems = order.status === 'pending';
  const itemEditForm = useForm<ItemEditFormData>({
    design_id: '',
    project_id: '',
    size_id: '',
    custom_design_description: '',
    requested_size: '',
    quantity: 1,
    cut_type: 'standard',
  });

  useEffect(() => {
    if (!previewImage) return;

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        setPreviewImage(null);
      }
    };
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [previewImage]);

  const openEditItemModal = (item: OrderItem) => {
    itemEditForm.setData({
      design_id: item.design?.id ?? '',
      project_id: item.project?.id ?? '',
      size_id: item.size?.id ?? '',
      custom_design_description: item.custom_design_description ?? '',
      requested_size: item.requested_size ?? '',
      quantity: item.quantity,
      cut_type: item.cut_type,
    });
    itemEditForm.clearErrors();
    setEditingItem(item);
  };

  const closeEditItemModal = () => {
    itemEditForm.reset();
    itemEditForm.clearErrors();
    setEditingItem(null);
  };

  const handleItemEdit = (event: React.FormEvent) => {
    event.preventDefault();
    if (!editingItem) return;

    itemEditForm.put(route('member.orders.items.update', {
      order: order.id,
      item: editingItem.id,
    }), {
      preserveScroll: true,
      onSuccess: closeEditItemModal,
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
      case 'paid': return 'bg-blue-100 text-blue-700 border-blue-200';
      case 'partial': return 'bg-violet-100 text-violet-700 border-violet-200';
      case 'processing': return 'bg-purple-100 text-purple-700 border-purple-200';
      case 'shipped': return 'bg-sky-100 text-sky-700 border-sky-200';
      case 'completed': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
      case 'cancelled': return 'bg-rose-100 text-rose-700 border-rose-200';
      default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('ms-MY', {
      style: 'currency',
      currency: 'MYR',
    }).format(amount);
  };

  const pricingLabels: Record<string, string> = {
    auto_priced: 'Harga automatik tersedia',
    pending_admin: 'Menunggu harga admin',
    awaiting_customer_approval: 'Menunggu kelulusan anda',
    approved: 'Harga telah diluluskan',
  };

  const statusLabels: Record<string, string> = {
    pending: 'Menunggu semakan',
    paid: 'Bayaran diterima',
    partial: 'Bayaran separa',
    processing: 'Sedang diproses',
    shipped: 'Sedang dihantar',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  };

  return (
    <MemberLayout>
      <Head title={`Order ${order.order_no}`} />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8 space-y-6">
        <Link
          href={route('member.orders.index')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          Kembali ke Order Saya
        </Link>

        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-brand-600">
              <Package className="h-5 w-5" />
            </div>
            <div>
              <h1 className="text-xl font-bold text-slate-900">{order.order_no}</h1>
              <p className="text-sm text-slate-500">{formatDateTime(order.created_at)}</p>
            </div>
          </div>
          <span className={`inline-flex rounded-full border px-4 py-1.5 text-xs font-semibold uppercase tracking-wider ${getStatusColor(order.status)}`}>
            {statusLabels[order.status] ?? order.status}
          </span>
        </div>

        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
          {/* Customer Info */}
          <div className="frontend-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Maklumat Penghantaran</h3>
            <div className="space-y-3">
              <div className="flex items-center gap-3">
                <User className="h-4 w-4 text-slate-400" />
                <span className="text-sm text-slate-900">{order.customer_name}</span>
              </div>
              <div className="flex items-center gap-3">
                <Phone className="h-4 w-4 text-slate-400" />
                <span className="text-sm text-slate-900">{order.customer_phone}</span>
              </div>
              <div className="flex items-start gap-3">
                <MapPin className="h-4 w-4 text-slate-400 mt-0.5" />
                <span className="text-sm text-slate-900">{order.customer_address}</span>
              </div>
              {order.tracking_no && (
                <div className="pt-2 border-t border-slate-100">
                  <span className="text-xs text-slate-500">No. Tracking: </span>
                  <span className="text-sm font-medium text-slate-900">{order.tracking_no}</span>
                </div>
              )}
            </div>
          </div>

          {/* Order Summary */}
          <div className="frontend-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Ringkasan</h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Material</span>
                <span className="text-sm font-medium text-slate-900">{order.material}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Subtotal</span>
                <span className="text-sm text-slate-900">{formatCurrency(order.subtotal)}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Pos</span>
                <span className={`text-sm font-medium ${Number(order.shipping_fee ?? 0) === 0 ? 'text-emerald-600' : 'text-slate-900'}`}>
                  {Number(order.shipping_fee ?? 0) === 0 ? 'Percuma' : formatCurrency(Number(order.shipping_fee))}
                </span>
              </div>
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm font-semibold text-slate-900">Jumlah</span>
                <span className="text-lg font-bold text-brand-600">{formatCurrency(order.total)}</span>
              </div>
            </div>
            {order.invoice && (
              <div className="mt-4 pt-4 border-t border-slate-100">
                <Link
                  href={route('member.invoices.show', order.invoice.id)}
                  className="frontend-btn-secondary w-full text-sm"
                >
                  <Receipt className="h-4 w-4" />
                  Lihat Invoice
                </Link>
              </div>
            )}
          </div>
        </div>

        <div className={`rounded-2xl border p-5 ${order.pricing_status === 'awaiting_customer_approval' ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white'}`}>
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-start gap-3">
              {order.pricing_status === 'awaiting_customer_approval' ? <Clock3 className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" /> : <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />}
              <div>
                <p className="font-bold text-slate-900">{pricingLabels[order.pricing_status] ?? 'Status harga sedang disemak'}</p>
                {order.price_note && <p className="mt-1 text-sm text-slate-600">Nota admin: {order.price_note}</p>}
                {order.pricing_status === 'pending_admin' && <p className="mt-1 text-sm text-slate-600">Admin akan semak dan masukkan harga untuk saiz atau kuantiti ini.</p>}
                {order.pricing_status === 'auto_priced' && <p className="mt-1 text-sm text-slate-600">Harga dikira berdasarkan saiz dan kuantiti dalam sistem.</p>}
              </div>
            </div>
            {order.pricing_status === 'awaiting_customer_approval' && (
              <Link
                href={route('member.orders.approve-price', order.id)}
                method="post"
                as="button"
                type="button"
                className="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700"
              >
                <CheckCircle2 className="h-4 w-4" />
                Luluskan Harga
              </Link>
            )}
          </div>
        </div>

        <CustomQuoteCalculator
          items={customQuoteItems.map((item, index) => ({
            id: item.id,
            name: item.design?.name || item.project?.title || item.custom_design_description || `Item ${index + 1}`,
            size: item.size?.name || item.requested_size || 'Saiz custom',
            quantity: item.quantity,
            quoted_qty_per_a3: item.quoted_qty_per_a3 as number,
            quoted_price_per_a3: item.quoted_price_per_a3 as number | string,
          }))}
        />

        {/* Items */}
        <div className="frontend-flat-card">
          <div className="px-5 py-4 border-b border-slate-200">
            <h3 className="text-lg font-bold text-slate-900">Item Order</h3>
          </div>
          <div className="frontend-table-wrap">
            <table className="frontend-table">
              <thead>
              <tr>
                  <th>Gambar</th>
                  <th>Design</th>
                  <th>Saiz</th>
                  <th>Kuantiti</th>
                  <th>Harga Unit</th>
                  <th>Subtotal</th>
                  {canEditItems && <th>Tindakan</th>}
                </tr>
              </thead>
              <tbody>
                {order.items.map((item) => (
                  <tr key={item.id}>
                    <td>
                      {item.preview_urls.length > 0 ? (
                        <div className="flex max-w-[220px] flex-wrap gap-2">
                          {item.preview_urls.map((previewUrl, previewIndex) => (
                            <button
                              key={previewUrl}
                              type="button"
                              onClick={() => setPreviewImage({
                                url: previewUrl,
                                alt: `Preview ${item.design?.name || item.project?.title || 'design'} ${previewIndex + 1}`,
                              })}
                              className="inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 transition hover:border-brand-300"
                              aria-label={`Lihat gambar ${previewIndex + 1}`}
                            >
                              <img src={previewUrl} alt={`Preview ${item.design?.name || item.project?.title || 'design'} ${previewIndex + 1}`} loading="lazy" className="h-full w-full object-contain" />
                            </button>
                          ))}
                        </div>
                      ) : (
                        <span className="inline-flex h-14 w-14 items-center justify-center rounded-xl bg-slate-50 text-slate-300">
                          <ImageIcon className="h-5 w-5" />
                        </span>
                      )}
                    </td>
                    <td>{item.design?.name || item.project?.title || 'Design sendiri'}</td>
                    <td>{item.size?.name || item.requested_size || 'Saiz custom'}</td>
                    <td>{item.quantity}</td>
                    <td>{formatCurrency(item.unit_price)}</td>
                    <td className="font-medium">{formatCurrency(item.line_total)}</td>
                    {canEditItems && (
                      <td>
                        <button
                          type="button"
                          onClick={() => openEditItemModal(item)}
                          className="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-2.5 py-1.5 text-xs font-bold text-brand-700 transition hover:bg-brand-100"
                        >
                          <Pencil className="h-3.5 w-3.5" />
                          Edit item
                        </button>
                      </td>
                    )}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
        {editingItem && (
          <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="member-item-edit-title"
          >
            <div className="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
              <button
                type="button"
                onClick={closeEditItemModal}
                className="absolute right-4 top-4 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tutup edit item"
              >
                <X className="h-5 w-5" />
              </button>
              <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-600">Bil. {order.items.findIndex((item) => item.id === editingItem.id) + 1}</p>
              <h2 id="member-item-edit-title" className="mt-1 pr-8 text-xl font-bold text-slate-900">Edit item order</h2>
              <p className="mt-1 text-sm text-slate-500">Item boleh diedit selagi status order menunggu semakan.</p>

              <form onSubmit={handleItemEdit} className="mt-6 space-y-5">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label htmlFor="member-item-design" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Design</label>
                    <select
                      id="member-item-design"
                      value={itemEditForm.data.design_id}
                      onChange={(event) => {
                        itemEditForm.setData('design_id', event.target.value ? Number(event.target.value) : '');
                        if (event.target.value) itemEditForm.setData('project_id', '');
                      }}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      <option value="">Design sendiri / project</option>
                      {itemEditOptions.designs.map((design) => <option key={design.id} value={design.id}>{design.name}</option>)}
                    </select>
                    {itemEditForm.errors.design_id && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.design_id}</p>}
                  </div>
                  <div>
                    <label htmlFor="member-item-project" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Project saya</label>
                    <select
                      id="member-item-project"
                      value={itemEditForm.data.project_id}
                      onChange={(event) => {
                        itemEditForm.setData('project_id', event.target.value ? Number(event.target.value) : '');
                        if (event.target.value) itemEditForm.setData('design_id', '');
                      }}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      <option value="">Tiada project / design sendiri</option>
                      {itemEditOptions.projects.map((project) => <option key={project.id} value={project.id}>{project.title}</option>)}
                    </select>
                    {itemEditForm.errors.project_id && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.project_id}</p>}
                  </div>
                  <div>
                    <label htmlFor="member-item-size" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saiz</label>
                    <select
                      id="member-item-size"
                      value={itemEditForm.data.size_id}
                      onChange={(event) => itemEditForm.setData('size_id', event.target.value ? Number(event.target.value) : '')}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      <option value="">Saiz custom</option>
                      {itemEditOptions.sizes.map((size) => <option key={size.id} value={size.id}>{size.name}</option>)}
                    </select>
                    {itemEditForm.errors.size_id && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.size_id}</p>}
                  </div>
                  <div>
                    <label htmlFor="member-item-quantity" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Kuantiti</label>
                    <input
                      id="member-item-quantity"
                      type="number"
                      min="1"
                      step="1"
                      value={itemEditForm.data.quantity}
                      onChange={(event) => itemEditForm.setData('quantity', Number(event.target.value))}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {itemEditForm.errors.quantity && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.quantity}</p>}
                  </div>
                  <div>
                    <label htmlFor="member-item-cut-type" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Jenis potongan</label>
                    <select
                      id="member-item-cut-type"
                      value={itemEditForm.data.cut_type}
                      onChange={(event) => itemEditForm.setData('cut_type', event.target.value as ItemEditFormData['cut_type'])}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      <option value="standard">Potong standard</option>
                      <option value="die-cut">Potong ikut bentuk</option>
                    </select>
                    {itemEditForm.errors.cut_type && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.cut_type}</p>}
                  </div>
                </div>
                <div>
                  <label htmlFor="member-item-requested-size" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saiz custom</label>
                  <input
                    id="member-item-requested-size"
                    type="text"
                    value={itemEditForm.data.requested_size}
                    onChange={(event) => itemEditForm.setData('requested_size', event.target.value)}
                    placeholder="Contoh: 5cm x 5cm"
                    className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  />
                  {itemEditForm.errors.requested_size && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.requested_size}</p>}
                </div>
                <div>
                  <label htmlFor="member-item-custom-description" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Keterangan design</label>
                  <textarea
                    id="member-item-custom-description"
                    rows={3}
                    value={itemEditForm.data.custom_design_description}
                    onChange={(event) => itemEditForm.setData('custom_design_description', event.target.value)}
                    placeholder="Nota atau arahan design"
                    className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  />
                  {itemEditForm.errors.custom_design_description && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.custom_design_description}</p>}
                </div>
                <div className="flex justify-end gap-2 border-t border-slate-100 pt-5">
                  <button type="button" onClick={closeEditItemModal} className="frontend-btn-secondary text-sm">Batal</button>
                  <button type="submit" disabled={itemEditForm.processing} className="frontend-btn-primary text-sm disabled:opacity-50">
                    <Pencil className="h-4 w-4" />
                    {itemEditForm.processing ? 'Menyimpan...' : 'Simpan Item'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
        {previewImage && (
          <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-label={previewImage.alt}
            onMouseDown={(event) => {
              if (event.target === event.currentTarget) setPreviewImage(null);
            }}
          >
            <div className="relative flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col items-center rounded-3xl border border-white/10 bg-slate-900/95 p-3 shadow-2xl sm:p-5">
              <button
                type="button"
                onClick={() => setPreviewImage(null)}
                className="absolute right-3 top-3 z-10 rounded-xl bg-white/10 p-2 text-white transition hover:bg-white/20"
                aria-label="Tutup preview gambar"
              >
                <X className="h-5 w-5" />
              </button>
              <img src={previewImage.url} alt={previewImage.alt} className="max-h-[calc(100vh-7rem)] max-w-full rounded-2xl object-contain" />
              <p className="mt-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-300">Preview order {order.order_no}</p>
            </div>
          </div>
        )}
      </div>
    </MemberLayout>
  );
}
