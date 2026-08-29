import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, BadgeCheck, Calculator, Clock3, Download, FileText, Image as ImageIcon, MapPin, Package, Pencil, Phone, Receipt, Ruler, Trash2, Truck, UploadCloud, User, X } from 'lucide-react';
import { formatDateTime } from '@/lib/utils';
import { useState } from 'react';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';

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
  design: { id: number; name: string } | null;
  project: { id: number; title: string } | null;
  size: { id: number; name: string } | null;
  custom_design_description: string | null;
  requested_size: string | null;
  quantity: number;
  unit_price: number;
  subtotal: number;
  line_total?: number;
  quoted_qty_per_a3: number | null;
  quoted_price_per_a3: number | string | null;
  quoted_sticker_type: string | null;
  cut_type: 'standard' | 'die-cut';
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
  subtotal: number;
  total: number;
  shipping_fee: number;
  shipping_region: string | null;
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
  itemEditEnabled: boolean;
  priceSettings: PriceSetting[];
  itemEditOptions: {
    designs: Array<{ id: number; name: string }>;
    projects: Array<{ id: number; title: string }>;
    sizes: Array<{ id: number; name: string }>;
  };
}

interface PriceSetting {
  sticker_type: string;
  qty_from: number;
  qty_to: number | null;
  price_per_a3: number;
}

interface ItemUploadFormData {
  source_files: File[];
  preview_images: File[];
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

interface ItemQuoteFormData {
  item_id: number;
  qty_per_a3: string;
  sticker_type: string;
}

interface QuoteFormData {
  amount: string;
  price_note: string;
  item_quotes: ItemQuoteFormData[];
}

interface CommonSizeFormData {
  name: string;
  width_cm: string;
  height_cm: string;
  shape: string;
  return_to_order: boolean;
}

export default function OrderShow({ order, uploadedFiles, editMode, itemEditEnabled, priceSettings, itemEditOptions }: OrderShowProps) {
  const [previewFile, setPreviewFile] = useState<UploadedFile | null>(null);
  const [uploadItem, setUploadItem] = useState<OrderItem | null>(null);
  const [editingItem, setEditingItem] = useState<OrderItem | null>(null);
  const [sizeSourceItem, setSizeSourceItem] = useState<OrderItem | null>(null);
  const quoteItems = order.items.filter((item) => item.size === null || Boolean(item.requested_size) || Number(item.line_total ?? item.subtotal ?? 0) <= 0);
  const { data, setData, put, processing } = useForm({
    status: order.status,
  });
  const quoteForm = useForm<QuoteFormData>({
    amount: order.subtotal > 0 ? String(order.subtotal) : '',
    price_note: order.price_note || '',
    item_quotes: quoteItems.map((item) => ({
      item_id: item.id,
      qty_per_a3: item.quoted_qty_per_a3 ? String(item.quoted_qty_per_a3) : '',
      sticker_type: item.quoted_sticker_type ?? '',
    })),
  });
  const sizeForm = useForm<CommonSizeFormData>({
    name: '',
    width_cm: '',
    height_cm: '',
    shape: '',
    return_to_order: true,
  });
  const itemUploadForm = useForm<ItemUploadFormData>({
    source_files: [],
    preview_images: [],
  });
  const itemEditForm = useForm<ItemEditFormData>({
    design_id: '',
    project_id: '',
    size_id: '',
    custom_design_description: '',
    requested_size: '',
    quantity: 1,
    cut_type: 'standard',
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

  const stickerTypes = Array.from(new Set(priceSettings.map((setting) => setting.sticker_type)));
  const quoteCalculations = quoteItems.map((item) => {
    const quote = quoteForm.data.item_quotes.find((itemQuote) => itemQuote.item_id === item.id);
    const qtyPerA3 = Number(quote?.qty_per_a3 ?? 0);
    const a3Sheets = Number.isInteger(qtyPerA3) && qtyPerA3 > 0 ? Math.ceil(item.quantity / qtyPerA3) : null;
    const stickerType = quote?.sticker_type ?? '';
    const priceSetting = a3Sheets !== null && stickerType
      ? priceSettings.find((setting) => setting.sticker_type === stickerType
        && a3Sheets >= setting.qty_from
        && (setting.qty_to === null || a3Sheets <= setting.qty_to))
      : null;
    const pricePerA3 = priceSetting ? Number(priceSetting.price_per_a3) : null;
    const isComplete = a3Sheets !== null && pricePerA3 !== null && Number.isFinite(pricePerA3) && pricePerA3 > 0;

    return {
      item,
      stickerType,
      qtyPerA3,
      pricePerA3,
      a3Sheets,
      total: a3Sheets === null || pricePerA3 === null ? null : a3Sheets * pricePerA3,
      isComplete,
    };
  });
  const hasQuoteInputs = quoteForm.data.item_quotes.some((itemQuote) => itemQuote.qty_per_a3 !== '' || itemQuote.sticker_type !== '');
  const customQuoteReady = quoteItems.length > 0 && hasQuoteInputs && quoteCalculations.every((calculation) => calculation.isComplete);
  const customQuoteTotal = customQuoteReady
    ? quoteCalculations.reduce((total, calculation) => total + (calculation.total ?? 0), 0)
    : null;

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
    quoteForm.transform((form) => ({
      ...form,
      amount: customQuoteTotal === null ? form.amount : customQuoteTotal.toFixed(2),
    }));
    quoteForm.post(route('admin.orders.quote', order.id), { preserveScroll: true });
  };

  const updateItemQuote = (itemId: number, field: 'qty_per_a3' | 'sticker_type', value: string) => {
    quoteForm.setData('item_quotes', quoteForm.data.item_quotes.map((itemQuote) => itemQuote.item_id === itemId
      ? { ...itemQuote, [field]: value }
      : itemQuote));
  };

  const openSizeModal = (item: OrderItem) => {
    sizeForm.setData({
      name: item.design?.name || item.project?.title || item.custom_design_description || 'Saiz custom',
      width_cm: '',
      height_cm: '',
      shape: '',
      return_to_order: true,
    });
    sizeForm.clearErrors();
    setSizeSourceItem(item);
  };

  const closeSizeModal = () => {
    sizeForm.reset();
    sizeForm.clearErrors();
    setSizeSourceItem(null);
  };

  const handleSizeSubmit = (event: React.FormEvent) => {
    event.preventDefault();
    sizeForm.post(route('admin.sizes.store'), {
      preserveScroll: true,
      onSuccess: closeSizeModal,
    });
  };

  const openUploadModal = (item: OrderItem) => {
    itemUploadForm.reset();
    itemUploadForm.clearErrors();
    setUploadItem(item);
  };

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

  const closeUploadModal = () => {
    itemUploadForm.reset();
    itemUploadForm.clearErrors();
    setUploadItem(null);
  };

  const closeEditItemModal = () => {
    itemEditForm.reset();
    itemEditForm.clearErrors();
    setEditingItem(null);
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

  const handleItemEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingItem) return;

    itemEditForm.put(route('admin.orders.items.update', {
      order: order.id,
      item: editingItem.id,
    }), {
      preserveScroll: true,
      onSuccess: closeEditItemModal,
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
                <p className="mt-1 text-sm text-slate-500">Untuk saiz atau kuantiti yang tiada dalam jadual harga, pilih jenis sticker dan bilangan per A3. Harga akan diambil daripada database.</p>
              </div>
            </div>
            {quoteItems.length > 0 && (
              <div className="mt-5 rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                <div className="flex items-start gap-3">
                  <Calculator className="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                  <div>
                    <h4 className="text-sm font-bold text-brand-900">Kiraan saiz custom per A3</h4>
                    <p className="mt-1 text-xs leading-relaxed text-brand-800">Isi bilangan sticker yang boleh dimuatkan dalam 1 A3 dan pilih jenis sticker. Harga 1 A3 akan dikira mengikut tier dalam database.</p>
                  </div>
                </div>
                <div className="mt-4 space-y-3">
                  {quoteCalculations.map((calculation) => {
                    const quote = quoteForm.data.item_quotes.find((itemQuote) => itemQuote.item_id === calculation.item.id);

                  return (
                    <div key={calculation.item.id} className="rounded-xl border border-brand-100 bg-white p-3">
                      <div className="flex flex-wrap items-baseline justify-between gap-2">
                        <p className="text-sm font-bold text-slate-900">
                          {calculation.item.design?.name || calculation.item.project?.title || 'Design sendiri'}
                        </p>
                        <div className="flex flex-wrap items-center gap-2">
                          <p className="text-xs text-slate-500">
                            {calculation.item.size?.name || calculation.item.requested_size || 'Saiz custom'} • {calculation.item.quantity} pcs
                          </p>
                          <button
                            type="button"
                            onClick={() => openSizeModal(calculation.item)}
                            className="inline-flex items-center gap-1 rounded-lg border border-brand-200 bg-white px-2.5 py-1.5 text-[11px] font-bold text-brand-700 transition hover:bg-brand-50"
                          >
                            <Ruler className="h-3.5 w-3.5" />
                            Simpan saiz umum
                          </button>
                        </div>
                      </div>
                      <div className="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                          <label htmlFor={`quote-qty-${calculation.item.id}`} className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Bilangan sticker dalam 1 A3</label>
                          <input
                            id={`quote-qty-${calculation.item.id}`}
                            type="number"
                            min="1"
                            step="1"
                            value={quote?.qty_per_a3 ?? ''}
                            onChange={(event) => updateItemQuote(calculation.item.id, 'qty_per_a3', event.target.value)}
                            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                            placeholder="Contoh: 24"
                          />
                        </div>
                        <div>
                          <label htmlFor={`quote-type-${calculation.item.id}`} className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Jenis sticker (harga database)</label>
                          <select
                            id={`quote-type-${calculation.item.id}`}
                            value={quote?.sticker_type ?? ''}
                            onChange={(event) => updateItemQuote(calculation.item.id, 'sticker_type', event.target.value)}
                            className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                          >
                            <option value="">Pilih jenis sticker</option>
                            {stickerTypes.map((stickerType) => <option key={stickerType} value={stickerType}>{stickerType}</option>)}
                          </select>
                          {stickerTypes.length === 0 && (
                            <p className="mt-1 text-xs text-rose-600">Tiada jenis sticker aktif. Tambah harga dahulu di menu Tetapan Harga.</p>
                          )}
                        </div>
                      </div>
                      {calculation.isComplete && calculation.a3Sheets !== null && calculation.total !== null && (
                        <p className="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">
                          ceil({calculation.item.quantity} / {calculation.qtyPerA3}) = {calculation.a3Sheets} A3 x {calculation.stickerType} @ RM {calculation.pricePerA3?.toFixed(2)} = RM {calculation.total.toFixed(2)}
                        </p>
                      )}
                      {calculation.stickerType && calculation.a3Sheets !== null && !calculation.isComplete && (
                        <p className="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                          Tiada tier harga {calculation.stickerType} untuk {calculation.a3Sheets} A3 dalam database.
                        </p>
                      )}
                    </div>
                  );
                })}
              </div>
              {customQuoteReady && customQuoteTotal !== null && (
                <p className="mt-3 flex items-center justify-between rounded-xl bg-brand-600 px-3 py-2.5 text-sm font-bold text-white">
                  <span>Jumlah sticker sebelum pos</span>
                  <span>RM {customQuoteTotal.toFixed(2)}</span>
                </p>
              )}
              {quoteForm.errors.item_quotes && <p className="mt-2 text-xs text-rose-600">{quoteForm.errors.item_quotes}</p>}
            </div>
          )}
          <form onSubmit={handleQuote} className="mt-5 grid grid-cols-1 gap-4 md:grid-cols-[220px_1fr_auto] md:items-end">
            <div>
              <label htmlFor="quote-amount" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga sticker sebelum pos (RM)</label>
              <input
                id="quote-amount"
                type="number"
                min="0.01"
                step="0.01"
                value={customQuoteTotal !== null ? customQuoteTotal.toFixed(2) : quoteForm.data.amount}
                onChange={(e) => quoteForm.setData('amount', e.target.value)}
                readOnly={customQuoteTotal !== null}
                className={`w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 ${customQuoteTotal !== null ? 'bg-slate-100' : 'bg-white'}`}
                placeholder="Contoh: 85.00"
              />
              {quoteForm.errors.amount && <p className="mt-1 text-xs text-rose-600">{quoteForm.errors.amount}</p>}
              <p className="mt-1 text-xs text-slate-400">{customQuoteTotal !== null ? 'Jumlah dikira daripada bilangan per A3 dan jenis sticker di atas.' : 'Caj pos akan ditambah automatik mengikut lokasi customer.'}</p>
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
            <button type="submit" disabled={quoteForm.processing || !!order.invoice || (customQuoteTotal === null && !quoteForm.data.amount)} className="admin-btn-primary text-sm disabled:opacity-50">
              {quoteForm.processing ? 'Menghantar...' : 'Hantar Harga'}
            </button>
          </form>
        </div>

        {sizeSourceItem && (
          <div
            className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="common-size-modal-title"
            onMouseDown={(event) => {
              if (event.target === event.currentTarget) closeSizeModal();
            }}
          >
            <div className="relative max-h-[calc(100vh-2rem)] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
              <button
                type="button"
                onClick={closeSizeModal}
                className="absolute right-4 top-4 rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tutup modal saiz umum"
              >
                <X className="h-5 w-5" />
              </button>
              <p className="text-xs font-semibold uppercase tracking-[0.16em] text-brand-600">Database saiz umum</p>
              <h2 id="common-size-modal-title" className="mt-1 pr-8 text-xl font-bold text-slate-900">Simpan saiz untuk kegunaan umum</h2>
              <p className="mt-1 text-sm leading-relaxed text-slate-500">Nama diambil daripada nama item dan boleh diubah sebelum disimpan.</p>

              <form onSubmit={handleSizeSubmit} className="mt-6 space-y-5">
                <div>
                  <label htmlFor="common-size-name" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Nama saiz</label>
                  <input
                    id="common-size-name"
                    type="text"
                    required
                    value={sizeForm.data.name}
                    onChange={(event) => sizeForm.setData('name', event.target.value)}
                    className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  />
                  {sizeForm.errors.name && <p className="mt-1 text-xs text-rose-600">{sizeForm.errors.name}</p>}
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label htmlFor="common-size-width" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Lebar (cm)</label>
                    <input
                      id="common-size-width"
                      type="number"
                      required
                      min="0.01"
                      step="0.01"
                      value={sizeForm.data.width_cm}
                      onChange={(event) => sizeForm.setData('width_cm', event.target.value)}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Contoh: 3"
                    />
                    {sizeForm.errors.width_cm && <p className="mt-1 text-xs text-rose-600">{sizeForm.errors.width_cm}</p>}
                  </div>
                  <div>
                    <label htmlFor="common-size-height" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tinggi (cm)</label>
                    <input
                      id="common-size-height"
                      type="number"
                      required
                      min="0.01"
                      step="0.01"
                      value={sizeForm.data.height_cm}
                      onChange={(event) => sizeForm.setData('height_cm', event.target.value)}
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Contoh: 10"
                    />
                    {sizeForm.errors.height_cm && <p className="mt-1 text-xs text-rose-600">{sizeForm.errors.height_cm}</p>}
                  </div>
                </div>
                <div>
                  <label htmlFor="common-size-shape" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Bentuk</label>
                  <select
                    id="common-size-shape"
                    required
                    value={sizeForm.data.shape}
                    onChange={(event) => sizeForm.setData('shape', event.target.value)}
                    className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  >
                    <option value="">Pilih bentuk</option>
                    <option value="Petak">Petak</option>
                    <option value="Segi Empat">Segi Empat</option>
                    <option value="Bulat">Bulat</option>
                    <option value="Oval">Oval</option>
                    <option value="Bebas">Bebas / Custom</option>
                  </select>
                  {sizeForm.errors.shape && <p className="mt-1 text-xs text-rose-600">{sizeForm.errors.shape}</p>}
                </div>
                <p className="rounded-xl bg-amber-50 px-3 py-2.5 text-xs leading-relaxed text-amber-700">Saiz akan disimpan sebagai aktif. Isi kuantiti per A3 di menu Saiz jika mahu harga automatik untuk order akan datang.</p>
                <div className="flex justify-end gap-2 border-t border-slate-100 pt-5">
                  <button type="button" onClick={closeSizeModal} className="admin-btn-secondary text-sm">Batal</button>
                  <button type="submit" disabled={sizeForm.processing} className="admin-btn-primary text-sm">
                    <Ruler className="h-4 w-4" />
                    {sizeForm.processing ? 'Menyimpan...' : 'Simpan Saiz'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

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
                      {itemEditEnabled && <th>Tindakan</th>}
                     <th>Fail Item</th>
                  </tr>
                </thead>
                <tbody>
                  {order.items.map((item, index) => (
                    <tr key={item.id}>
                      <td className="font-semibold text-slate-500">{index + 1}</td>
                      <td>{item.design?.name || item.project?.title || 'Design sendiri'}</td>
                       <td>{item.size?.name || item.requested_size || 'Saiz custom'}</td>
                        <td>{item.quantity}</td>
                        <td>{formatCurrency(item.unit_price)}</td>
                        <td className="font-medium">{formatCurrency(item.line_total ?? item.subtotal)}</td>
                        {itemEditEnabled && (
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
                        <td>
                          <div className="grid min-w-[320px] grid-cols-2 gap-2">
                            {item.files.length > 0 ? item.files.map((file) => (
                              <div key={file.id} className="flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 p-1.5">
                                <Tooltip>
                                  <TooltipTrigger asChild>
                                    <a
                                      href={file.url}
                                      target="_blank"
                                      rel="noreferrer"
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
                                  </TooltipTrigger>
                                  <TooltipContent>{file.label}</TooltipContent>
                                </Tooltip>
                                {editMode && (
                                  <div className="flex shrink-0 items-center gap-1">
                                    <Tooltip>
                                      <TooltipTrigger asChild>
                                        <button
                                          type="button"
                                          onClick={() => handleItemFileDelete(item, file)}
                                          aria-label={`Padam ${file.label}`}
                                          className="inline-flex h-7 w-7 items-center justify-center rounded-lg text-rose-500 transition hover:bg-white hover:text-rose-700"
                                        >
                                          <Trash2 className="h-3.5 w-3.5" />
                                        </button>
                                      </TooltipTrigger>
                                      <TooltipContent>Padam {file.label}</TooltipContent>
                                    </Tooltip>
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
                      <Tooltip>
                        <TooltipTrigger asChild>
                          <a
                            href={file.download_url ?? file.url}
                            download
                            aria-label={`Download gambar ${file.item_label}`}
                            className="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white/95 text-brand-600 shadow-sm transition hover:bg-brand-50"
                          >
                            <Download className="h-4 w-4" />
                          </a>
                        </TooltipTrigger>
                        <TooltipContent>Download gambar</TooltipContent>
                      </Tooltip>
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
                   Tambah fail item
                </h2>
                <p className="mt-1 text-sm text-slate-500">
                  {uploadItem.design?.name || uploadItem.project?.title || 'Design sendiri'} • {uploadItem.size?.name || 'Saiz custom'} • {uploadItem.quantity} pcs
                </p>
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
             </div>
           </div>
          )}

          {editingItem && (
            <div
              className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
              role="dialog"
              aria-modal="true"
              aria-labelledby="item-edit-title"
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
                <h2 id="item-edit-title" className="mt-1 pr-8 text-xl font-bold text-slate-900">Edit item order</h2>
                <p className="mt-1 text-sm text-slate-500">Harga unit semasa: {formatCurrency(editingItem.unit_price)}</p>

                <form onSubmit={handleItemEdit} className="mt-6 space-y-5">
                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <label htmlFor="item-design" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Design</label>
                      <select
                        id="item-design"
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
                      <label htmlFor="item-project" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Project customer</label>
                      <select
                        id="item-project"
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
                      <label htmlFor="item-size" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saiz</label>
                      <select
                        id="item-size"
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
                      <label htmlFor="item-quantity" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Kuantiti</label>
                      <input
                        id="item-quantity"
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
                      <label htmlFor="item-cut-type" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Jenis potongan</label>
                      <select
                        id="item-cut-type"
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
                    <label htmlFor="item-requested-size" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Saiz custom</label>
                    <input
                      id="item-requested-size"
                      type="text"
                      value={itemEditForm.data.requested_size}
                      onChange={(event) => itemEditForm.setData('requested_size', event.target.value)}
                      placeholder="Contoh: 5cm x 5cm"
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {itemEditForm.errors.requested_size && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.requested_size}</p>}
                  </div>
                  <div>
                    <label htmlFor="item-custom-description" className="block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Keterangan design</label>
                    <textarea
                      id="item-custom-description"
                      rows={3}
                      value={itemEditForm.data.custom_design_description}
                      onChange={(event) => itemEditForm.setData('custom_design_description', event.target.value)}
                      placeholder="Nota atau arahan design customer"
                      className="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {itemEditForm.errors.custom_design_description && <p className="mt-1 text-xs text-rose-600">{itemEditForm.errors.custom_design_description}</p>}
                  </div>
                  <div className="flex justify-end gap-2 border-t border-slate-100 pt-5">
                    <button type="button" onClick={closeEditItemModal} className="admin-btn-secondary text-sm">Batal</button>
                    <button type="submit" disabled={itemEditForm.processing} className="admin-btn-primary text-sm disabled:opacity-50">
                      <Pencil className="h-4 w-4" />
                      {itemEditForm.processing ? 'Menyimpan...' : 'Simpan Item'}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}

       </div>
    </AdminLayout>
  );
}
