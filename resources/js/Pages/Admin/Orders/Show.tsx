import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, BadgeCheck, Clock3, Download, FileText, Image as ImageIcon, MapPin, Package, Pencil, Phone, Receipt, Trash2, Truck, UploadCloud, User, X } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';
import { useState } from 'react';

interface UploadedFile {
  id: string;
  item_label: string;
  name: string;
  url: string;
  download_url?: string;
  preview_url: string | null;
  is_image: boolean;
  origin: 'create_order' | 'admin';
  origin_label: string;
  file_type_label: string;
}

interface OrderItem {
  id: number;
  design: { name: string } | null;
  project: { id: number; title: string } | null;
  size: { name: string } | null;
  quantity: number;
  unit_price: number;
  subtotal: number;
  line_total?: number;
  files: ItemFile[];
  source_files: Array<{ label: string; url: string }>;
  preview_files: Array<{ label: string; url: string }>;
}

type ItemFileType = 'design' | 'source' | 'preview';

interface ItemFile {
  id: string;
  type: ItemFileType;
  index: number;
  label: string;
  name: string;
  url: string;
  download_url: string;
  preview_url: string | null;
  is_image: boolean;
  origin: 'create_order' | 'admin';
  origin_label: string;
  file_type_label: string;
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
  pricing_status: string;
  price_note: string | null;
  custom_request: string | null;
  created_at: string;
  user: { id: number; name: string; email: string } | null;
  invoice: { id: number; invoice_no: string; amount: number } | null;
  items: OrderItem[];
}

interface OrderShowProps {
  order: Order;
  uploadedFiles: UploadedFile[];
  editMode: boolean;
}

interface ItemUploadFormData {
  source_files: File[];
  preview_images: File[];
}

interface ItemReplaceFormData {
  file: File | null;
  _method: 'put';
}

export default function OrderShow({ order, uploadedFiles, editMode }: OrderShowProps) {
  const [previewFile, setPreviewFile] = useState<UploadedFile | null>(null);
  const [uploadItem, setUploadItem] = useState<OrderItem | null>(null);
  const [replaceFile, setReplaceFile] = useState<ItemFile | null>(null);
  const { data, setData, put, processing } = useForm({
    status: order.status,
    tracking_no: order.tracking_no || '',
  });
  const quoteForm = useForm({
    amount: order.total > 0 ? String(order.total) : '',
    price_note: order.price_note || '',
  });
  const itemUploadForm = useForm<ItemUploadFormData>({
    source_files: [],
    preview_images: [],
  });
  const itemReplaceForm = useForm<ItemReplaceFormData>({
    file: null,
    _method: 'put',
  });

  const handleUpdate = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.orders.update', order.id));
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
      case 'paid': return 'bg-blue-100 text-blue-700 border-blue-200';
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
    auto_priced: 'Harga automatik',
    pending_admin: 'Menunggu harga admin',
    awaiting_customer_approval: 'Menunggu kelulusan customer',
    approved: 'Harga diluluskan customer',
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

  const handleQuote = (e: React.FormEvent) => {
    e.preventDefault();
    quoteForm.post(route('admin.orders.quote', order.id), { preserveScroll: true });
  };

  const openUploadModal = (item: OrderItem) => {
    itemUploadForm.reset();
    itemUploadForm.clearErrors();
    itemReplaceForm.reset();
    itemReplaceForm.clearErrors();
    setReplaceFile(null);
    setUploadItem(item);
  };

  const openReplaceModal = (item: OrderItem, file: ItemFile) => {
    itemUploadForm.reset();
    itemUploadForm.clearErrors();
    itemReplaceForm.reset();
    itemReplaceForm.clearErrors();
    setReplaceFile(file);
    setUploadItem(item);
  };

  const closeUploadModal = () => {
    itemUploadForm.reset();
    itemUploadForm.clearErrors();
    itemReplaceForm.reset();
    itemReplaceForm.clearErrors();
    setReplaceFile(null);
    setUploadItem(null);
  };

  const handleItemUpload = (e: React.FormEvent) => {
    e.preventDefault();
    if (!uploadItem) return;

    itemUploadForm.post(route('admin.orders.items.files.store', { order: order.id, item: uploadItem.id }), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: closeUploadModal,
    });
  };

  const handleItemReplace = (e: React.FormEvent) => {
    e.preventDefault();
    if (!uploadItem || !replaceFile || !itemReplaceForm.data.file) return;

    itemReplaceForm.post(route('admin.orders.items.files.update', {
      order: order.id,
      item: uploadItem.id,
      type: replaceFile.type,
      index: replaceFile.index,
    }), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: closeUploadModal,
    });
  };

  const handleItemFileDelete = (item: OrderItem, file: ItemFile) => {
    if (!window.confirm(`Padam ${file.label} (${file.name}) daripada item ini?`)) return;

    router.delete(route('admin.orders.items.files.destroy', {
      order: order.id,
      item: item.id,
      type: file.type,
      index: file.index,
    }), { preserveScroll: true });
  };

  return (
    <AdminLayout>
      <Head title={`Order ${order.order_no}`} />
      <div className="space-y-6">
        {/* Back Link */}
        <Link
          href={route('admin.orders.index')}
          className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition"
        >
          <ArrowLeft className="h-4 w-4" />
          Kembali ke Senarai Order
        </Link>

        {/* Order Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <div className="admin-icon-badge">
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

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          {/* Customer Info */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Maklumat Pelanggan</h3>
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
              {order.user && (
                <div className="flex items-center gap-3 pt-2 border-t border-slate-100">
                  <span className="text-xs text-slate-500">Akaun:</span>
                  <span className="text-sm text-slate-900">{order.user.name} ({order.user.email})</span>
                </div>
              )}
            </div>
          </div>

          {/* Order Summary */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Ringkasan Order</h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Material</span>
                <span className="text-sm font-medium text-slate-900">{order.material}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-slate-500">Subtotal</span>
                <span className="text-sm text-slate-900">{formatCurrency(order.subtotal)}</span>
              </div>
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm font-semibold text-slate-900">Jumlah</span>
                <span className="text-lg font-bold text-brand-600">{formatCurrency(order.total)}</span>
              </div>
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <span className="text-sm text-slate-500">Status harga</span>
                <span className="text-right text-sm font-semibold text-amber-700">{pricingLabels[order.pricing_status] ?? order.pricing_status}</span>
              </div>
            </div>
          </div>

          {/* Update Form */}
          <div className="admin-flat-card p-6">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Kemaskini Status</h3>
            <form onSubmit={handleUpdate} className="space-y-4">
              <div>
                <label htmlFor="status" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                <select
                  id="status"
                  value={data.status}
                  onChange={(e) => setData('status', e.target.value)}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                >
                  <option value="pending">Menunggu semakan</option>
                  <option value="paid">Bayaran diterima</option>
                  <option value="processing">Sedang diproses</option>
                  <option value="shipped">Sedang dihantar</option>
                  <option value="completed">Selesai</option>
                  <option value="cancelled">Dibatalkan</option>
                </select>
              </div>
              <div>
                <label htmlFor="tracking_no" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">No. Tracking</label>
                <input
                  id="tracking_no"
                  type="text"
                 value={data.tracking_no}
                  onChange={(e) => {
                    const trackingNo = e.target.value;
                    setData('tracking_no', trackingNo);
                    if (trackingNo.trim() !== '') {
                      setData('status', 'shipped');
                    }
                  }}
                  placeholder="Contoh: JNT123456"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
                {data.tracking_no.trim() !== '' && (
                  <p className="mt-1.5 text-xs text-emerald-600">Status akan ditetapkan sebagai sedang dihantar apabila tracking disimpan.</p>
                )}
              </div>
              <button
                type="submit"
                disabled={processing}
                className="admin-btn-primary w-full text-sm"
              >
                {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
              </button>
            </form>
          </div>
        </div>

        <div className="admin-flat-card p-6">
          <div className="flex items-start gap-3">
            {order.pricing_status === 'awaiting_customer_approval' || order.pricing_status === 'pending_admin' ? <Clock3 className="mt-0.5 h-5 w-5 shrink-0 text-amber-600" /> : <BadgeCheck className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />}
            <div>
              <h3 className="text-lg font-bold text-slate-900">Tetapkan Harga Order</h3>
              <p className="mt-1 text-sm text-slate-500">Untuk saiz atau kuantiti yang tiada dalam jadual harga, masukkan jumlah dan hantar kepada customer untuk kelulusan.</p>
            </div>
          </div>
          <form onSubmit={handleQuote} className="mt-5 grid grid-cols-1 gap-4 md:grid-cols-[220px_1fr_auto] md:items-end">
            <div>
              <label htmlFor="quote-amount" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Jumlah harga (RM)</label>
              <input
                id="quote-amount"
                type="number"
                min="0.01"
                step="0.01"
                value={quoteForm.data.amount}
                onChange={(e) => quoteForm.setData('amount', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="Contoh: 85.00"
              />
              {quoteForm.errors.amount && <p className="mt-1 text-xs text-rose-600">{quoteForm.errors.amount}</p>}
            </div>
            <div>
              <label htmlFor="quote-note" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nota kepada customer (pilihan)</label>
              <input
                id="quote-note"
                type="text"
                value={quoteForm.data.price_note}
                onChange={(e) => quoteForm.setData('price_note', e.target.value)}
                className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                placeholder="Contoh: Termasuk caj setup die-cut"
              />
            </div>
            <button type="submit" disabled={quoteForm.processing || !!order.invoice} className="admin-btn-primary text-sm disabled:opacity-50">
              {quoteForm.processing ? 'Menghantar...' : 'Hantar Harga'}
            </button>
          </form>
        </div>

        {/* Order Items */}
        <div className="admin-flat-card">
          <div className="admin-card-header">
            <div className="flex items-center gap-3">
              <div className="admin-icon-badge">
                <Package className="h-5 w-5" />
              </div>
              <h3 className="text-lg font-bold text-slate-900">Item Order</h3>
            </div>
          </div>
          <div className="admin-table-wrap">
              <table className="admin-table">
                <thead>
                  <tr>
                    <th>Bil.</th>
                    <th>Design</th>
                    <th>Saiz</th>
                    <th>Kuantiti</th>
                     <th>Harga Unit</th>
                     <th>Subtotal</th>
                     <th>Fail Item</th>
                  </tr>
                </thead>
                <tbody>
                  {order.items.map((item, index) => (
                    <tr key={item.id}>
                      <td className="font-semibold text-slate-500">{index + 1}</td>
                      <td>{item.design?.name || item.project?.title || 'Design sendiri'}</td>
                      <td>{item.size?.name || 'Saiz custom'}</td>
                        <td>{item.quantity}</td>
                        <td>{formatCurrency(item.unit_price)}</td>
                        <td className="font-medium">{formatCurrency(item.line_total ?? item.subtotal)}</td>
                        <td>
                          <div className="grid min-w-[320px] grid-cols-2 gap-2">
                            {item.files.length > 0 ? item.files.map((file) => (
                              <div key={file.id} className="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 p-1.5">
                                <a
                                  href={file.url}
                                  target="_blank"
                                  rel="noreferrer"
                                  title={file.label}
                                  className="flex min-w-0 flex-1 items-center gap-2 rounded-lg px-1.5 py-1 text-left transition hover:bg-white"
                                >
                                  {file.preview_url ? (
                                    <img src={file.preview_url} alt="" className="h-8 w-8 shrink-0 rounded-md bg-white object-cover" />
                                  ) : file.type === 'preview' ? (
                                    <ImageIcon className="h-4 w-4 shrink-0 text-emerald-600" />
                                  ) : (
                                    <FileText className="h-4 w-4 shrink-0 text-brand-600" />
                                  )}
                                  <span className="min-w-0">
                                    <span className="block truncate text-[11px] font-bold text-slate-700">{file.label}</span>
                                  </span>
                                </a>
                                {editMode && (
                                  <div className="flex shrink-0 items-center gap-1">
                                    <button
                                      type="button"
                                      onClick={() => openReplaceModal(item, file)}
                                      aria-label={`Ganti ${file.label}`}
                                      title={`Ganti ${file.label}`}
                                      className="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white hover:text-brand-600"
                                    >
                                      <Pencil className="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                      type="button"
                                      onClick={() => handleItemFileDelete(item, file)}
                                      aria-label={`Padam ${file.label}`}
                                      title={`Padam ${file.label}`}
                                      className="inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-500 transition hover:bg-white hover:text-rose-700"
                                    >
                                      <Trash2 className="h-3.5 w-3.5" />
                                    </button>
                                  </div>
                                )}
                              </div>
                            )) : (
                              <span className="block rounded-lg bg-slate-50 px-2 py-1 text-[11px] font-medium text-slate-400">Tiada fail</span>
                            )}
                            {editMode && (
                              <button
                                type="button"
                                onClick={() => openUploadModal(item)}
                                className="col-span-2 inline-flex items-center justify-center gap-1 rounded-lg bg-brand-50 px-2 py-1 text-[11px] font-bold text-brand-700 transition hover:bg-brand-100"
                              >
                                <UploadCloud className="h-3 w-3" />
                                Tambah fail
                              </button>
                            )}
                          </div>
                        </td>
                    </tr>
                  ))}
                </tbody>
              </table>
          </div>
        </div>

        {!editMode && (
          <section className="admin-flat-card p-6">
            <div className="flex items-start gap-3">
              <div className="admin-icon-badge">
                <FileText className="h-5 w-5" />
              </div>
              <div>
                <h3 className="text-lg font-bold text-slate-900">Fail Design Dihantar</h3>
                <p className="mt-1 text-sm text-slate-500">Fail customer dan fail yang admin upload dipaparkan bersama label asal masing-masing.</p>
              </div>
            </div>

            {uploadedFiles.length > 0 ? (
              <div className="mt-5 grid gap-3 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
                {uploadedFiles.map((file) => (
                  file.is_image ? (
                    <div key={file.id} className="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-brand-300 hover:shadow-md">
                      <button
                        type="button"
                        onClick={() => setPreviewFile(file)}
                        className="block w-full text-left"
                        aria-label={`Lihat gambar ${file.item_label}`}
                      >
                        <img src={file.preview_url ?? file.url} alt={`Gambar ${file.item_label}`} loading="lazy" className="h-40 w-full bg-slate-100 object-contain" />
                        <span className="block border-t border-slate-100 px-3 py-2">
                          <span className="block text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{file.item_label}</span>
                          <span className="mt-0.5 block text-xs font-medium text-slate-700">{file.file_type_label}</span>
                          <span className={`mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ${file.origin === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-brand-50 text-brand-700'}`}>{file.origin_label}</span>
                        </span>
                      </button>
                      <a
                        href={file.download_url ?? file.url}
                        download
                        aria-label={`Download gambar ${file.item_label}`}
                        title="Download gambar"
                        className="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white/95 text-brand-600 shadow-sm transition hover:bg-brand-50"
                      >
                        <Download className="h-4 w-4" />
                      </a>
                    </div>
                  ) : (
                    <a
                      key={file.id}
                      href={file.url}
                      target="_blank"
                      rel="noreferrer"
                      className="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-brand-300 hover:bg-brand-50"
                    >
                      <FileText className="h-8 w-8 shrink-0 text-brand-500" />
                      <span className="min-w-0">
                        <span className="block text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">{file.item_label}</span>
                        <span className="mt-0.5 block text-xs font-medium text-slate-700">{file.file_type_label}</span>
                        <span className={`mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ${file.origin === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-brand-50 text-brand-700'}`}>{file.origin_label}</span>
                      </span>
                      <Download className="ml-auto h-4 w-4 shrink-0 text-slate-400" />
                    </a>
                  )
                ))}
              </div>
            ) : (
              <p className="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-500">Tiada fail design dimuat naik untuk order ini.</p>
            )}
          </section>
        )}

        {/* Invoice & Actions */}
        <div className="flex flex-wrap items-center gap-3">
          {order.invoice ? (
            <Link
              href={route('admin.invoices.show', order.invoice.id)}
              className="admin-btn-secondary text-sm"
            >
              <Receipt className="h-4 w-4" />
              Lihat Invoice ({order.invoice.invoice_no})
            </Link>
          ) : order.pricing_status === 'pending_admin' || order.pricing_status === 'awaiting_customer_approval' || order.total <= 0 ? (
            <a
              href="#quote-amount"
              className="admin-btn-secondary text-sm"
            >
              Menunggu Harga / Kelulusan
            </a>
          ) : (
            <Link
              href={route('admin.invoices.store', order.id)}
              method="post"
              as="button"
              type="button"
              className="admin-btn-primary text-sm"
            >
              <Receipt className="h-4 w-4" />
              Cipta Invoice
            </Link>
          )}
          <Link
            href={route('admin.jnt.index', { order_id: order.id })}
            className="admin-btn-secondary text-sm"
          >
            <Truck className="h-4 w-4" />
            J&T Waybill
          </Link>
        </div>

         {previewFile && (
          <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-label={`Preview ${previewFile.item_label}`}
            onMouseDown={(event) => {
              if (event.target === event.currentTarget) setPreviewFile(null);
            }}
          >
            <div className="relative flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col items-center rounded-3xl border border-white/10 bg-slate-900/95 p-3 shadow-2xl sm:p-5">
              <button
                type="button"
                onClick={() => setPreviewFile(null)}
                className="absolute right-3 top-3 z-10 rounded-xl bg-white/10 p-2 text-white transition hover:bg-white/20"
                aria-label="Tutup preview gambar"
              >
                <X className="h-5 w-5" />
              </button>
              <img
                src={previewFile.preview_url ?? previewFile.url}
                alt={`Preview ${previewFile.item_label}`}
                className="max-h-[calc(100vh-7rem)] max-w-full rounded-2xl object-contain"
              />
              <p className="mt-3 text-center text-xs font-semibold uppercase tracking-[0.14em] text-slate-300">{previewFile.item_label}</p>
            </div>
          </div>
         )}

         {uploadItem && (
           <div
             className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
             role="dialog"
             aria-modal="true"
             aria-labelledby="item-upload-title"
           >
             <div className="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
               <button
                 type="button"
                 onClick={closeUploadModal}
                 className="absolute right-4 top-4 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                 aria-label="Tutup upload fail item"
               >
                 <X className="h-5 w-5" />
                </button>
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-600">Bil. {order.items.findIndex((item) => item.id === uploadItem.id) + 1}</p>
                <h2 id="item-upload-title" className="mt-1 pr-8 text-xl font-bold text-slate-900">
                  {replaceFile ? `Ganti ${replaceFile.label}` : 'Tambah fail item'}
                </h2>
                <p className="mt-1 text-sm text-slate-500">
                  {uploadItem.design?.name || uploadItem.project?.title || 'Design sendiri'} • {uploadItem.size?.name || 'Saiz custom'} • {uploadItem.quantity} pcs
                </p>
                {replaceFile ? (
                  <form onSubmit={handleItemReplace} className="mt-6 space-y-5">
                    <div>
                      <label htmlFor="item-replace-file" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Fail baharu</label>
                      <input
                        id="item-replace-file"
                        type="file"
                        accept={replaceFile.type === 'preview' ? 'image/*' : replaceFile.type === 'design' ? '.jpg,.jpeg,.png,.webp,.pdf' : undefined}
                        onChange={(event) => itemReplaceForm.setData('file', event.target.files?.[0] ?? null)}
                        className="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                      />
                      <p className="mt-1 text-xs text-slate-400">Fail lama akan diganti dengan fail baharu ini.</p>
                      {itemReplaceForm.errors.file && <p className="mt-1 text-xs text-rose-600">{itemReplaceForm.errors.file}</p>}
                    </div>
                    <div className="flex justify-end gap-2 border-t border-slate-100 pt-5">
                      <button type="button" onClick={closeUploadModal} className="admin-btn-secondary text-sm">Batal</button>
                      <button type="submit" disabled={itemReplaceForm.processing || !itemReplaceForm.data.file} className="admin-btn-primary text-sm disabled:opacity-50">
                        <Pencil className="h-4 w-4" />
                        {itemReplaceForm.processing ? 'Mengemaskini...' : 'Ganti Fail'}
                      </button>
                    </div>
                  </form>
                ) : (
                  <form onSubmit={handleItemUpload} className="mt-6 space-y-5">
                    <div>
                      <label htmlFor="item-source-file" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Source file untuk rujukan admin</label>
                      <input
                        id="item-source-file"
                        type="file"
                        multiple
                        onChange={(event) => itemUploadForm.setData('source_files', Array.from(event.target.files ?? []))}
                        className="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                      />
                      <p className="mt-1 text-xs text-slate-400">Boleh pilih lebih daripada satu fail. AI, PSD, PDF atau format kerja lain. Maksimum 50MB setiap fail.</p>
                      {itemUploadForm.data.source_files.length > 0 && <p className="mt-1 text-xs font-semibold text-brand-600">{itemUploadForm.data.source_files.length} source dipilih.</p>}
                      {itemUploadForm.errors.source_files && <p className="mt-1 text-xs text-rose-600">{itemUploadForm.errors.source_files}</p>}
                    </div>
                    <div>
                      <label htmlFor="item-preview-image" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Gambar untuk customer</label>
                      <input
                        id="item-preview-image"
                        type="file"
                        accept="image/*"
                        multiple
                        onChange={(event) => itemUploadForm.setData('preview_images', Array.from(event.target.files ?? []))}
                        className="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                      />
                      <p className="mt-1 text-xs text-slate-400">Boleh pilih lebih daripada satu gambar. Setiap gambar dioptimumkan pada quality 70 dan maksimum 10MB.</p>
                      {itemUploadForm.data.preview_images.length > 0 && <p className="mt-1 text-xs font-semibold text-emerald-600">{itemUploadForm.data.preview_images.length} gambar dipilih.</p>}
                      {itemUploadForm.errors.preview_images && <p className="mt-1 text-xs text-rose-600">{itemUploadForm.errors.preview_images}</p>}
                    </div>
                    <div className="flex justify-end gap-2 border-t border-slate-100 pt-5">
                      <button type="button" onClick={closeUploadModal} className="admin-btn-secondary text-sm">Batal</button>
                      <button type="submit" disabled={itemUploadForm.processing} className="admin-btn-primary text-sm">
                        <UploadCloud className="h-4 w-4" />
                        {itemUploadForm.processing ? 'Memuat naik...' : 'Simpan Fail'}
                      </button>
                    </div>
                  </form>
                )}
             </div>
           </div>
         )}

      </div>
    </AdminLayout>
  );
}
