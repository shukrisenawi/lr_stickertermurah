import { type FormEvent, type MouseEvent, useState } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import ResponsiveDesignImage from '@/Components/ResponsiveDesignImage';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Check, Eye, Hash, Image as ImageIcon, Palette, Pencil, Plus, Trash2, Upload, X } from 'lucide-react';

interface Design {
  id: number;
  name: string;
  image_path: string | null;
  image_url: string | null;
  mobile_image_url: string | null;
  ori_url: string | null;
  category: { name: string } | null;
  tags: string[] | null;
  is_active: boolean;
}

interface DesignsIndexProps {
  designs: {
    data: Design[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  availableTags: string[];
  activeTag: string;
}

interface BulkTagFormData {
  design_ids: number[];
  hashtag: string;
}

export default function DesignsIndex({ designs, availableTags, activeTag }: DesignsIndexProps) {
  const { delete: destroy } = useForm();
  const {
    data: bulkTagData,
    setData: setBulkTagData,
    post: postBulkTag,
    processing: bulkTagProcessing,
    errors: bulkTagErrors,
    reset: resetBulkTag,
  } = useForm<BulkTagFormData>({ design_ids: [], hashtag: '' });
  const [preview, setPreview] = useState<Design | null>(null);
  const [editingTag, setEditingTag] = useState<string | null>(null);
  const [tagEditValue, setTagEditValue] = useState('');
  const [tagProcessing, setTagProcessing] = useState(false);
  const [tagError, setTagError] = useState<string | null>(null);
  const [selectedDesignIds, setSelectedDesignIds] = useState<number[]>([]);
  const [lastSelectedIndex, setLastSelectedIndex] = useState<number | null>(null);

  const closePreview = () => {
    setPreview(null);
    setEditingTag(null);
    setTagEditValue('');
    setTagError(null);
  };

  const updateSelection = (ids: number[]) => {
    const uniqueIds = Array.from(new Set(ids));
    setSelectedDesignIds(uniqueIds);
    setBulkTagData('design_ids', uniqueIds);
  };

  const toggleSelection = (index: number, extendSelection: boolean) => {
    const designId = designs.data[index]?.id;
    if (!designId) return;

    if (extendSelection && lastSelectedIndex !== null) {
      const start = Math.min(lastSelectedIndex, index);
      const end = Math.max(lastSelectedIndex, index);
      const rangeIds = designs.data.slice(start, end + 1).map((design) => design.id);
      updateSelection([...selectedDesignIds, ...rangeIds]);
    } else if (selectedDesignIds.includes(designId)) {
      updateSelection(selectedDesignIds.filter((id) => id !== designId));
    } else {
      updateSelection([...selectedDesignIds, designId]);
    }

    setLastSelectedIndex(index);
  };

  const clearSelection = () => {
    setSelectedDesignIds([]);
    setLastSelectedIndex(null);
    resetBulkTag();
  };

  const handleBulkTag = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    postBulkTag(route('admin.designs.bulk.tag'), {
      preserveScroll: true,
      onSuccess: clearSelection,
    });
  };

  const handleDesignClick = (design: Design, index: number, event: MouseEvent<HTMLDivElement>) => {
    if (event.shiftKey) {
      event.preventDefault();
      toggleSelection(index, true);
      return;
    }

    setPreview(design);
    setEditingTag(null);
  };

  const normalizeTag = (raw: string) => raw
    .replace(/^#+/, '')
    .toLowerCase()
    .replace(/[^a-z0-9_\-]/g, '')
    .trim();

  const startTagRename = (tag: string) => {
    setEditingTag(tag);
    setTagEditValue(tag);
    setTagError(null);
  };

  const cancelTagRename = () => {
    setEditingTag(null);
    setTagEditValue('');
    setTagError(null);
  };

  const handleTagRenameChange = (value: string) => {
    if (!preview || !editingTag) return;

    setTagEditValue(value);
  };

  const handleTagUpdate = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!preview || !editingTag) return;

    const renamedTag = normalizeTag(tagEditValue);
    if (!renamedTag) return;

    const tags = Array.from(new Set((preview.tags ?? []).map((tag) => (
      tag === editingTag ? renamedTag : tag
    ))));
    setTagProcessing(true);
    setTagError(null);
    router.put(route('admin.designs.tags.update', preview.id), { tags }, {
      preserveScroll: true,
      onSuccess: () => {
        setPreview((current) => current ? { ...current, tags } : current);
        cancelTagRename();
      },
      onError: (errors) => setTagError(errors.tags ?? 'Hashtag tidak berjaya dikemaskini.'),
      onFinish: () => setTagProcessing(false),
    });
  };

  const handleDelete = (id: number) => {
    if (confirm('Adakah anda pasti mahu memadam design ini?')) {
      destroy(route('admin.designs.destroy', id));
    }
  };

  const handlePaginationClick = () => setLastSelectedIndex(null);

  return (
    <AdminLayout>
      <Head title="Senarai Design" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Galeri Design</h2>
            <p className="admin-page-copy">Urus design sticker.</p>
          </div>
          <div className="flex items-center gap-3">
            <Link href={route('admin.designs.bulk.create')} className="admin-btn-secondary">
              <Upload className="h-4 w-4" />
              Muat Naik Pukal
            </Link>
            <Link href={route('admin.designs.create')} className="admin-btn-primary">
              <Plus className="h-4 w-4" />
            Tambah Design
          </Link>
          </div>
        </div>

        <div className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="mb-3 flex items-center gap-2">
            <Hash className="h-4 w-4 text-brand-600" />
            <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">Filter Hashtag</p>
          </div>
          <nav aria-label="Filter hashtag" className="flex flex-wrap gap-2">
            <Link
              href={route('admin.designs.index')}
              className={`rounded-full px-3 py-1.5 text-xs font-bold transition ${
                !activeTag ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700'
              }`}
            >
              Semua
            </Link>
            {availableTags.map((tag) => (
              <Link
                key={tag}
                href={route('admin.designs.index', { tag })}
                className={`rounded-full px-3 py-1.5 text-xs font-bold transition ${
                  activeTag === tag ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700'
                }`}
              >
                #{tag}
              </Link>
            ))}
          </nav>
        </div>

        {selectedDesignIds.length > 0 && (
          <form onSubmit={handleBulkTag} className="rounded-2xl border border-brand-200 bg-brand-50/60 p-4">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p className="text-sm font-bold text-brand-900">{selectedDesignIds.length} design dipilih</p>
                <p className="mt-1 text-xs text-brand-700">Shift+klik design untuk pilih julat dengan cepat.</p>
              </div>
              <button
                type="button"
                onClick={clearSelection}
                className="rounded-lg px-3 py-1.5 text-xs font-bold text-brand-700 transition hover:bg-white"
              >
                Kosongkan pilihan
              </button>
            </div>
            <div className="mt-3 flex flex-col gap-2 sm:flex-row">
              <div className="relative flex-1">
                <Hash className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <label htmlFor="bulk-hashtag" className="sr-only">Hashtag</label>
                <input
                  id="bulk-hashtag"
                  type="text"
                  value={bulkTagData.hashtag}
                  onChange={(event) => setBulkTagData('hashtag', event.target.value)}
                  placeholder="contoh: makanan atau #makanan"
                  className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                />
              </div>
              <button
                type="submit"
                disabled={bulkTagProcessing}
                className="admin-btn-primary justify-center disabled:opacity-50"
              >
                {bulkTagProcessing ? 'Menyimpan...' : 'Tambah Hashtag'}
              </button>
            </div>
            {bulkTagErrors.hashtag && <p className="mt-2 text-xs font-medium text-rose-600">{bulkTagErrors.hashtag}</p>}
          </form>
        )}

        {designs.data.length === 0 ? (
          <div className="rounded-2xl border border-slate-200 bg-white py-20 text-center">
            <Palette className="mx-auto h-16 w-16 text-slate-300" />
            <p className="mt-4 text-lg font-semibold text-slate-600">Tiada Design</p>
            <p className="mt-1 text-sm text-slate-400">Tambah design pertama anda untuk bermula.</p>
            <Link href={route('admin.designs.create')} className="admin-btn-primary mt-6 inline-flex">
              <Plus className="h-4 w-4" />
              Tambah Design
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-3 gap-2 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-8">
            {designs.data.map((design, index) => (
              <div
                key={design.id}
                onClick={(event) => handleDesignClick(design, index, event)}
                className={`group relative cursor-pointer overflow-hidden rounded-xl border bg-white shadow-sm transition hover:shadow-md sm:rounded-2xl ${
                  selectedDesignIds.includes(design.id)
                    ? 'border-brand-600 ring-2 ring-brand-200'
                    : 'border-slate-200'
                }`}
              >
                {/* Image */}
                <div className="aspect-square w-full overflow-hidden bg-slate-100">
                  {design.image_path ? (
                    <ResponsiveDesignImage
                      src={design.image_url ?? ''}
                      mobileSrc={design.mobile_image_url}
                      alt={design.name}
                      loading="lazy"
                      decoding="async"
                      className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center">
                      <ImageIcon className="h-12 w-12 text-slate-300" />
                    </div>
                  )}
                  <button
                    type="button"
                    aria-label={`${selectedDesignIds.includes(design.id) ? 'Nyahpilih' : 'Pilih'} ${design.name}`}
                    aria-pressed={selectedDesignIds.includes(design.id)}
                    onClick={(event) => {
                      event.stopPropagation();
                      toggleSelection(index, event.shiftKey);
                    }}
                    className={`absolute bottom-2 right-2 z-10 flex h-6 w-6 items-center justify-center rounded-md border-2 shadow-sm transition ${
                      selectedDesignIds.includes(design.id)
                        ? 'border-brand-600 bg-brand-600 text-white'
                        : 'border-white bg-white/90 text-transparent hover:border-brand-300'
                    }`}
                  >
                    <Check className="h-4 w-4" />
                  </button>
                  {design.image_path && (
                    <div className="absolute right-2 top-2 rounded-lg bg-black/50 p-1.5 text-white opacity-0 transition group-hover:opacity-100 pointer-events-none">
                      <Eye className="h-4 w-4" />
                    </div>
                  )}
                </div>

                {/* Overlay on hover */}
                <div className="absolute inset-x-0 bottom-0 flex items-center justify-center gap-2 bg-gradient-to-t from-black/60 to-transparent pb-20 pt-8 opacity-0 transition group-hover:opacity-100 pointer-events-none">
                  <Link
                    href={route('admin.designs.edit', design.id)}
                    onClick={(e) => e.stopPropagation()}
                    className="pointer-events-auto inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-lg transition hover:bg-brand-50 hover:text-brand-700"
                  >
                    <Pencil className="h-4 w-4" />
                  </Link>
                  {design.ori_url && (
                    <a
                      href={design.ori_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      onClick={(e) => e.stopPropagation()}
                      className="pointer-events-auto inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-lg transition hover:bg-amber-50 hover:text-amber-700"
                    >
                      <ImageIcon className="h-4 w-4" />
                    </a>
                  )}
                  <button
                    type="button"
                    onClick={(e) => { e.stopPropagation(); handleDelete(design.id); }}
                    className="pointer-events-auto inline-flex items-center gap-1 rounded-xl bg-white px-3 py-2 text-sm font-medium text-rose-600 shadow-lg transition hover:bg-rose-50"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>

                {/* Info */}
                <div className="p-2 sm:p-3">
                  <h3 className="truncate text-[11px] font-semibold text-slate-900 sm:text-sm">{design.name}</h3>
                </div>
              </div>
            ))}
          </div>
        )}

        {designs.links.length > 3 && (
          <div className="flex items-center justify-center gap-2">
            {designs.links.map((link, i) => (
              link.url ? (
                <Link key={i} href={link.url} onClick={handlePaginationClick} preserveState preserveScroll className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
              ) : (
                <span key={i} className="rounded-lg px-3 py-1.5 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
              )
            ))}
          </div>
        )}
      </div>

      {/* Lightbox Modal */}
      {preview && (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
          onClick={closePreview}
        >
          {/* Close button */}
          <button
            type="button"
            onClick={closePreview}
            className="absolute right-4 top-4 z-10 rounded-xl bg-white/10 p-2 text-white backdrop-blur-md transition hover:bg-white/20"
          >
            <X className="h-6 w-6" />
          </button>

          {/* Image */}
          <div
            className="relative max-h-[85vh] max-w-[85vw]"
            onClick={(e) => e.stopPropagation()}
          >
            {preview.image_url ? (
              <img
                src={preview.image_url}
                alt={preview.name}
                className="max-h-[85vh] max-w-[85vw] rounded-2xl object-contain shadow-2xl"
              />
            ) : (
              <div className="flex h-64 w-64 items-center justify-center rounded-2xl bg-slate-800">
                <ImageIcon className="h-16 w-16 text-slate-600" />
              </div>
            )}

            {/* Caption */}
            <div className="absolute bottom-0 left-0 right-0 rounded-b-2xl bg-gradient-to-t from-black/70 to-transparent p-6 pt-12">
              <p className="text-lg font-bold text-white">{preview.name}</p>
              {preview.tags && preview.tags.length > 0 && (
                <div className="mt-2 flex flex-wrap gap-1.5">
                  {preview.tags.map((tag) => (
                    editingTag === tag ? (
                      <form
                        key={tag}
                        onSubmit={handleTagUpdate}
                        className="inline-flex items-center gap-1 rounded-full bg-white p-1 pl-2 shadow-sm"
                      >
                        <Hash className="h-3 w-3 text-brand-600" />
                        <input
                          type="text"
                          value={tagEditValue}
                          onChange={(event) => handleTagRenameChange(event.target.value)}
                          onKeyDown={(event) => {
                            if (event.key === 'Escape') {
                              event.preventDefault();
                              cancelTagRename();
                            }
                          }}
                          aria-label={`Nama baharu untuk #${tag}`}
                          className="w-28 bg-transparent text-xs font-semibold text-slate-900 outline-none"
                        />
                        <button
                          type="submit"
                          disabled={tagProcessing || !normalizeTag(tagEditValue)}
                          aria-label={`Simpan nama #${tag}`}
                          className="rounded-full bg-brand-600 p-1 text-white transition hover:bg-brand-700 disabled:opacity-50"
                        >
                          <Check className="h-3 w-3" />
                        </button>
                        <button
                          type="button"
                          onClick={cancelTagRename}
                          aria-label={`Batal sunting #${tag}`}
                          className="rounded-full p-1 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        >
                          <X className="h-3 w-3" />
                        </button>
                      </form>
                    ) : (
                      <div key={tag} className="inline-flex items-center overflow-hidden rounded-full bg-white/90 text-xs font-bold text-brand-700 shadow-sm">
                        <Link
                          href={route('admin.designs.index', { tag })}
                          onClick={(event) => {
                            event.stopPropagation();
                            closePreview();
                          }}
                          className="px-2.5 py-1 transition hover:bg-brand-600 hover:text-white"
                        >
                          #{tag}
                        </Link>
                        <button
                          type="button"
                          onClick={(event) => {
                            event.stopPropagation();
                            startTagRename(tag);
                          }}
                          aria-label={`Sunting nama #${tag}`}
                          title={`Sunting nama #${tag}`}
                          className="inline-flex items-center gap-1 border-l border-brand-100 px-2 py-1 text-brand-600 transition hover:bg-brand-600 hover:text-white"
                        >
                          <Pencil className="h-3 w-3" />
                          <span>Sunting</span>
                        </button>
                      </div>
                    )
                  ))}
                </div>
              )}
              {tagError && <p className="mt-2 text-xs font-medium text-rose-200">{tagError}</p>}
            </div>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
