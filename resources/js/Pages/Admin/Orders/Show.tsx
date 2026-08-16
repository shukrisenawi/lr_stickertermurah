import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, BadgeCheck, Clock3, Download, FileText, FolderKanban, MapPin, Package, Phone, Receipt, Trash2, Truck, UploadCloud, User, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { formatDateTime } from '@/lib/utils';

interface ProjectFile {
  index: number;
  name: string;
  url: string;
  is_image: boolean;
  preview_url: string | null;
}

interface CustomerProject {
  id: number;
  title: string;
  preview_url: string | null;
  source_files: ProjectFile[];
  created_at: string;
  order_no: string | null;
}

interface OrderItem {
  id: number;
  customer_project_source_index: number | null;
  customer_project_source_indices: number[] | null;
  design: { name: string } | null;
  project: { id: number; title: string } | null;
  size: { name: string } | null;
  quantity: number;
  unit_price: number;
  subtotal: number;
  line_total?: number;
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
  customerProjects: CustomerProject[];
}

export default function OrderShow({ order, customerProjects }: OrderShowProps) {
  const [imagePreview, setImagePreview] = useState<ProjectFile | null>(null);
  const { data, setData, put, processing } = useForm({
    status: order.status,
    tracking_no: order.tracking_no || '',
  });
  const quoteForm = useForm({
    amount: order.total > 0 ? String(order.total) : '',
    price_note: order.price_note || '',
  });
  const projectUploadForm = useForm<{ title: string; files: File[] }>({
    title: `Design ${order.order_no}`,
    files: [],
  });
  const projectSelectForm = useForm<{ project_id: string; source_indices: string[] }>({ project_id: '', source_indices: [] });

  useEffect(() => {
    if (!imagePreview) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setImagePreview(null);
    };
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [imagePreview]);

  const currentProjectItem = order.items.find((item) => item.project) ?? null;
  const currentProjectId = currentProjectItem?.project?.id ?? null;
  const currentProjectSourceIndices = currentProjectItem?.customer_project_source_indices
    ?? (currentProjectItem?.customer_project_source_index !== null && currentProjectItem?.customer_project_source_index !== undefined
      ? [currentProjectItem.customer_project_source_index]
      : null);
  const currentProject = customerProjects.find((project) => project.id === currentProjectId) ?? null;
  const currentProjectFiles = currentProject
    ? currentProjectSourceIndices === null
      ? currentProject.source_files
      : currentProject.source_files.filter((file) => currentProjectSourceIndices.includes(file.index))
    : [];
  const selectedPreviousProject = customerProjects.find((project) => String(project.id) === projectSelectForm.data.project_id) ?? null;
  const selectedPreviousFiles = selectedPreviousProject?.source_files.filter((file) => projectSelectForm.data.source_indices.includes(String(file.index))) ?? [];
  const canRemoveImagePreview = imagePreview !== null
    && selectedPreviousProject?.source_files.some((file) => file.url === imagePreview.url) === true
    && projectSelectForm.data.source_indices.includes(String(imagePreview.index));

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

  const handleQuote = (e: React.FormEvent) => {
    e.preventDefault();
    quoteForm.post(route('admin.orders.quote', order.id), { preserveScroll: true });
  };

  const handleProjectUpload = (e: React.FormEvent) => {
    e.preventDefault();
    projectUploadForm.post(route('admin.orders.projects.store', order.id), {
      forceFormData: true,
      preserveScroll: true,
    });
  };

  const handleProjectSelect = (e: React.FormEvent) => {
    e.preventDefault();
    projectSelectForm.post(route('admin.orders.projects.select', order.id), { preserveScroll: true });
  };

  const handleRemovePreviewFile = () => {
    if (!imagePreview || !canRemoveImagePreview || !selectedPreviousProject) return;

    const sourceIndices = projectSelectForm.data.source_indices.filter((index) => index !== String(imagePreview.index));
    projectSelectForm.setData('source_indices', sourceIndices);

    const nextPreview = selectedPreviousProject.source_files.find((file) =>
      sourceIndices.includes(String(file.index)) && file.is_image && file.preview_url,
    );
    setImagePreview(nextPreview ?? null);
  };

  const renderProjectFiles = (files: ProjectFile[]) => (
    <div className="flex flex-wrap gap-2">
      {files.map((file) => (
        file.is_image && file.preview_url ? (
          <button
            key={file.url}
            type="button"
            onClick={() => setImagePreview(file)}
            className="group flex w-28 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white text-left shadow-sm transition hover:border-brand-300 hover:shadow-md"
          >
            <img
              src={file.preview_url}
              alt={file.name}
              loading="lazy"
              className="h-20 w-full bg-slate-100 object-contain"
            />
            <span className="flex items-center gap-1 px-2 py-1.5 text-[11px] font-medium text-slate-600">
              <span className="truncate">{file.name}</span>
            </span>
          </button>
        ) : (
          <a
            key={file.url}
            href={file.url}
            className="inline-flex max-w-full items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs font-medium text-slate-600 shadow-sm transition hover:border-brand-300 hover:text-brand-700"
          >
            <FileText className="h-5 w-5 shrink-0 text-brand-500" />
            <span className="max-w-40 truncate">{file.name}</span>
            <Download className="h-3.5 w-3.5 shrink-0 text-slate-400" />
          </a>
        )
      ))}
    </div>
  );

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
            {order.status}
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
                  <option value="pending">Pending</option>
                  <option value="paid">Paid</option>
                  <option value="processing">Processing</option>
                  <option value="shipped">Shipped</option>
                  <option value="completed">Completed</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div>
                <label htmlFor="tracking_no" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">No. Tracking</label>
                <input
                  id="tracking_no"
                  type="text"
                  value={data.tracking_no}
                  onChange={(e) => setData('tracking_no', e.target.value)}
                  placeholder="Contoh: JNT123456"
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
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
                  <th>Design</th>
                  <th>Saiz</th>
                  <th>Kuantiti</th>
                  <th>Harga Unit</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                {order.items.map((item) => (
                  <tr key={item.id}>
                    <td>{item.design?.name || item.project?.title || 'Design sendiri'}</td>
                    <td>{item.size?.name || 'Saiz custom'}</td>
                    <td>{item.quantity}</td>
                    <td>{formatCurrency(item.unit_price)}</td>
                    <td className="font-medium">{formatCurrency(item.line_total ?? item.subtotal)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Customer Project Files */}
        <section className="admin-flat-card p-6">
          <div className="flex items-start gap-3">
            <div className="admin-icon-badge">
              <FolderKanban className="h-5 w-5" />
            </div>
            <div>
              <h3 className="text-lg font-bold text-slate-900">Fail / Project Customer</h3>
              <p className="mt-1 text-sm text-slate-500">
                Upload fail untuk order ini atau pilih fail yang pernah digunakan oleh customer yang sama.
              </p>
            </div>
          </div>

          {!order.user ? (
            <div className="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
              Order ini tiada akaun customer. Fail project memerlukan customer ID untuk disimpan dan digunakan semula.
            </div>
          ) : (
            <div className="mt-5 space-y-5">
              <p className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                Customer ID: {order.user.id}
              </p>

              {currentProject && (
                <div className="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4">
                  <div className="flex items-start gap-3">
                    <FileText className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                    <div className="min-w-0">
                      <p className="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">Project digunakan untuk order ini</p>
                      <p className="mt-1 truncate font-bold text-emerald-900">{currentProject.title}</p>
                    </div>
                    {currentProject.preview_url && (
                      <a
                        href={currentProject.preview_url}
                        target="_blank"
                        rel="noreferrer"
                        className="ml-auto shrink-0 text-xs font-bold text-emerald-700 hover:text-emerald-900"
                      >
                        Lihat Preview
                      </a>
                    )}
                  </div>
                  <div className="mt-3">{renderProjectFiles(currentProjectFiles)}</div>
                </div>
              )}

              {customerProjects.length > 0 && (
                <form onSubmit={handleProjectSelect} className="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                  <div>
                    <label htmlFor="previous-project" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                      Pilih project / fail terdahulu
                    </label>
                    <div className="flex flex-col gap-2 sm:flex-row">
                      <select
                        id="previous-project"
                        value={projectSelectForm.data.project_id}
                        onChange={(event) => {
                          projectSelectForm.setData('project_id', event.target.value);
                          projectSelectForm.setData('source_indices', []);
                        }}
                        className="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      >
                        <option value="">Pilih project customer ini...</option>
                        {customerProjects.map((project) => (
                          <option key={project.id} value={project.id}>
                            {project.title} ({project.source_files.length} fail){project.order_no ? ` - ${project.order_no}` : ''}
                          </option>
                        ))}
                      </select>
                      <button
                        type="submit"
                        disabled={!projectSelectForm.data.project_id || projectSelectForm.data.source_indices.length === 0 || projectSelectForm.processing}
                        className="admin-btn-primary shrink-0 text-sm disabled:opacity-50"
                      >
                        {projectSelectForm.processing ? 'Memilih...' : 'Guna Fail Ini'}
                      </button>
                    </div>
                    {projectSelectForm.errors.project_id && <p className="mt-1 text-xs text-rose-600">{projectSelectForm.errors.project_id}</p>}
                    {projectSelectForm.errors.source_indices && <p className="mt-1 text-xs text-rose-600">{projectSelectForm.errors.source_indices}</p>}
                  </div>

                  {selectedPreviousProject && (
                    <div className="border-t border-slate-200 pt-3">
                      <p className="mb-2 text-xs font-semibold text-slate-500">Pilih satu atau lebih fail daripada project ini. Klik gambar untuk live preview.</p>
                      <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {selectedPreviousProject.source_files.map((file) => {
                          const isSelected = projectSelectForm.data.source_indices.includes(String(file.index));

                          return (
                            <div
                              key={file.url}
                              className={`relative flex min-w-0 items-center gap-2 rounded-xl border-2 bg-white p-2 text-left transition ${
                                isSelected ? 'border-brand-600 ring-2 ring-brand-100' : 'border-slate-200 hover:border-brand-300'
                              }`}
                            >
                              {file.is_image && file.preview_url ? (
                                <button
                                  type="button"
                                  onClick={() => {
                                    setImagePreview(file);
                                    if (!isSelected) {
                                      projectSelectForm.setData('source_indices', [...projectSelectForm.data.source_indices, String(file.index)]);
                                    }
                                  }}
                                  className="flex min-w-0 flex-1 items-center gap-2 text-left"
                                >
                                  <img src={file.preview_url} alt={file.name} className="h-12 w-14 shrink-0 rounded-lg bg-slate-100 object-contain" />
                                  <span className="min-w-0 truncate text-xs font-medium text-slate-700">{file.name}</span>
                                </button>
                              ) : (
                                <a href={file.url} className="flex min-w-0 flex-1 items-center gap-2 text-left">
                                  <span className="flex h-12 w-14 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                                    <FileText className="h-6 w-6 text-brand-500" />
                                  </span>
                                  <span className="min-w-0 truncate text-xs font-medium text-slate-700">{file.name}</span>
                                </a>
                              )}
                              <label className="absolute right-2 top-2 flex h-6 w-6 cursor-pointer items-center justify-center rounded-md bg-white/90 shadow-sm">
                                <input
                                  type="checkbox"
                                  checked={isSelected}
                                  onChange={() => {
                                    const sourceIndices = isSelected
                                      ? projectSelectForm.data.source_indices.filter((index) => index !== String(file.index))
                                      : [...projectSelectForm.data.source_indices, String(file.index)];
                                    projectSelectForm.setData('source_indices', sourceIndices);
                                    if (!isSelected && file.is_image && file.preview_url) {
                                      setImagePreview(file);
                                    }
                                  }}
                                  aria-label={`Pilih ${file.name}`}
                                  className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                />
                              </label>
                            </div>
                          );
                        })}
                      </div>
                      {selectedPreviousFiles.length > 0 && (
                        <p className="mt-2 text-xs font-semibold text-brand-700">
                          {selectedPreviousFiles.length} fail dipilih: {selectedPreviousFiles.map((file) => file.name).join(', ')}
                        </p>
                      )}
                    </div>
                  )}
                </form>
              )}

              <form onSubmit={handleProjectUpload} className="space-y-4 border-t border-slate-200 pt-5">
                <div className="grid gap-4 md:grid-cols-2">
                  <div>
                    <label htmlFor="project-title" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                      Nama project / design
                    </label>
                    <input
                      id="project-title"
                      type="text"
                      value={projectUploadForm.data.title}
                      onChange={(event) => projectUploadForm.setData('title', event.target.value)}
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Contoh: Logo Kedai Ali"
                    />
                    {projectUploadForm.errors.title && <p className="mt-1 text-xs text-rose-600">{projectUploadForm.errors.title}</p>}
                  </div>
                  <div>
                    <label htmlFor="project-files" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
                      Upload fail baharu
                    </label>
                    <input
                      id="project-files"
                      type="file"
                      multiple
                      accept="image/jpeg,image/png,image/webp,.zip,.rar,.7z,.ai,.psd,.eps,.pdf,.svg"
                      onChange={(event) => projectUploadForm.setData('files', Array.from(event.target.files ?? []).slice(0, 20))}
                      className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900"
                    />
                    <p className="mt-1 text-xs text-slate-400">Maksimum 20 fail, setiap satu sehingga 50MB.</p>
                  </div>
                </div>

                {projectUploadForm.data.files.length > 0 && (
                  <div className="space-y-1 rounded-xl bg-slate-50 p-3">
                    {projectUploadForm.data.files.map((file, index) => (
                      <div key={`${file.name}-${file.size}-${file.lastModified}`} className="flex items-center justify-between gap-2 text-xs text-slate-600">
                        <span className="truncate">{file.name}</span>
                        <button
                          type="button"
                          onClick={() => projectUploadForm.setData('files', projectUploadForm.data.files.filter((_, fileIndex) => fileIndex !== index))}
                          className="shrink-0 rounded-md p-1 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                          aria-label={`Buang ${file.name}`}
                        >
                          <X className="h-3.5 w-3.5" />
                        </button>
                      </div>
                    ))}
                  </div>
                )}

                {projectUploadForm.errors.files && <p className="text-xs text-rose-600">{projectUploadForm.errors.files}</p>}
                <div className="flex justify-end">
                  <button
                    type="submit"
                    disabled={projectUploadForm.processing || projectUploadForm.data.files.length === 0}
                    className="admin-btn-primary text-sm disabled:opacity-50"
                  >
                    <UploadCloud className="h-4 w-4" />
                    {projectUploadForm.processing ? 'Memuat naik...' : 'Upload & Kaitkan Project'}
                  </button>
                </div>
              </form>
            </div>
          )}
        </section>

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

        {imagePreview && (
          <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm" role="presentation">
            <button
              type="button"
              aria-label="Tutup preview gambar"
              className="absolute inset-0 cursor-default"
              onClick={() => setImagePreview(null)}
            />
            <div
              className="relative z-10 flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
              role="dialog"
              aria-modal="true"
              aria-labelledby="order-file-preview-title"
            >
              <div className="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <p id="order-file-preview-title" className="truncate text-sm font-bold text-slate-900">
                  {imagePreview.name}
                </p>
                <div className="flex shrink-0 items-center gap-2">
                  {canRemoveImagePreview && (
                    <button
                      type="button"
                      onClick={handleRemovePreviewFile}
                      aria-label={`Buang ${imagePreview.name} daripada pilihan`}
                      title="Buang fail daripada pilihan"
                      className="flex h-9 w-9 items-center justify-center rounded-full bg-rose-50 text-rose-600 transition hover:bg-rose-100 hover:text-rose-700"
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  )}
                  <button
                    type="button"
                    onClick={() => setImagePreview(null)}
                    aria-label="Tutup preview gambar"
                    className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>
              </div>
              <div className="flex min-h-0 flex-1 items-center justify-center overflow-auto bg-slate-100 p-4 sm:p-8">
                <img
                  src={imagePreview.preview_url ?? imagePreview.url}
                  alt={imagePreview.name}
                  className="max-h-[70vh] max-w-full object-contain"
                />
              </div>
              <div className="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
                <button
                  type="button"
                  onClick={() => setImagePreview(null)}
                  className="admin-btn-secondary text-sm"
                >
                  Tutup
                </button>
                <a href={imagePreview.url} className="admin-btn-primary text-sm">
                  <Download className="h-4 w-4" />
                  Download Gambar
                </a>
              </div>
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
