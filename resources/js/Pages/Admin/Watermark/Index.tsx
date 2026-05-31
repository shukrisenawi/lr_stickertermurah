import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { Image as ImageIcon, Upload, Trash2, Download, Settings, Save, Eye, X } from 'lucide-react';
import { useState, useEffect } from 'react';

interface WatermarkFile {
  name: string;
  url: string;
  size: number;
  created_at: string;
}

interface WatermarkProps {
  files: WatermarkFile[];
  config: {
    resize_height: string;
    watermark_size: string;
    apply_watermark: string;
  };
}

export default function WatermarkIndex({ files, config }: WatermarkProps) {
  const uploadForm = useForm({
    image: null as File | null,
    resize_height: config.resize_height,
    watermark_size: config.watermark_size,
    apply_watermark: config.apply_watermark,
  });

  const configForm = useForm({
    resize_height: config.resize_height,
    watermark_size: config.watermark_size,
    apply_watermark: config.apply_watermark,
  });

  const handleUpload = (e: React.FormEvent) => {
    e.preventDefault();
    if (!uploadForm.data.image) return;

    uploadForm.post(route('admin.watermark.upload'), {
      forceFormData: true,
      onSuccess: () => uploadForm.setData('image', null),
    });
  };

  const handleSaveConfig = (e: React.FormEvent) => {
    e.preventDefault();

    configForm.post(route('admin.watermark.config'), {
      onSuccess: () => {
        uploadForm.setData('resize_height', configForm.data.resize_height);
        uploadForm.setData('watermark_size', configForm.data.watermark_size);
        uploadForm.setData('apply_watermark', configForm.data.apply_watermark);
      },
    });
  };

  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  useEffect(() => {
    if (!previewUrl) return;
    const handler = (e: KeyboardEvent) => { if (e.key === 'Escape') setPreviewUrl(null); };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, [previewUrl]);

  const handleDelete = (name: string) => {
    if (confirm('Padam gambar ini?')) {
      uploadForm.delete(route('admin.watermark.destroy', { filename: name }));
    }
  };

  return (
    <AdminLayout>
      <Head title="Watermark" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Watermark</h2>
            <p className="admin-page-copy">
              Muat naik gambar untuk diletakkan watermark dan resize. Hanya 3 gambar terkini disimpan.
            </p>
          </div>
        </div>

        {files.length === 0 ? (
          <div className="rounded-2xl border border-slate-200 bg-white py-16 text-center">
            <ImageIcon className="mx-auto h-14 w-14 text-slate-300" />
            <p className="mt-4 text-base font-semibold text-slate-500">Tiada gambar</p>
            <p className="mt-1 text-sm text-slate-400">Muat naik gambar untuk mula.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {files.map((file) => (
              <div
                key={file.name}
                className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
              >
                <div className="flex items-center justify-center bg-slate-100 p-4">
                  <img
                    src={file.url}
                    alt={file.name}
                    className="max-h-72 w-auto max-w-full rounded-lg object-contain"
                  />
                </div>
                <div className="flex items-center justify-between p-3">
                  <div className="text-xs text-slate-500">
                    {(file.size / 1024).toFixed(1)} KB
                  </div>
                  <div className="flex items-center gap-1">
                    <button
                      type="button"
                      onClick={() => setPreviewUrl(file.url)}
                      className="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100"
                    >
                      <Eye className="h-4 w-4" />
                    </button>
                    <a
                      href={file.url}
                      download
                      className="rounded-lg p-1.5 text-slate-500 transition-colors hover:bg-slate-100"
                    >
                      <Download className="h-4 w-4" />
                    </a>
                    <button
                      type="button"
                      onClick={() => handleDelete(file.name)}
                      className="rounded-lg p-1.5 text-red-500 transition-colors hover:bg-red-50"
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-2 text-sm font-semibold text-slate-700">
                <Settings className="h-4 w-4" />
                Konfigurasi
              </div>
              <button
                type="button"
                onClick={handleSaveConfig}
                disabled={configForm.processing}
                className="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50"
              >
                <Save className="h-3.5 w-3.5" />
                {configForm.processing ? 'Menyimpan...' : 'Simpan'}
              </button>
            </div>
            <div className="space-y-3">
              <label className="flex items-center justify-between gap-4 text-sm">
                <span className="whitespace-nowrap text-slate-600">Tinggi Resize (px)</span>
                <input
                  type="number"
                  min="0"
                  max="5000"
                  placeholder="0 = saiz asal"
                  value={configForm.data.resize_height}
                  onChange={(e) => configForm.setData('resize_height', e.target.value)}
                  className="w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none"
                />
              </label>
              <label className="flex items-center justify-between gap-4 text-sm">
                <span className="whitespace-nowrap text-slate-600">Saiz Watermark (px)</span>
                <input
                  type="number"
                  min="1"
                  max="2000"
                  placeholder="200"
                  value={configForm.data.watermark_size}
                  onChange={(e) => configForm.setData('watermark_size', e.target.value)}
                  className="w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none"
                />
              </label>
              <label className="flex items-center gap-2 text-sm cursor-pointer">
                <input
                  type="checkbox"
                  checked={configForm.data.apply_watermark === '1'}
                  onChange={(e) => configForm.setData('apply_watermark', e.target.checked ? '1' : '0')}
                  className="h-4 w-4 rounded border-slate-300 text-slate-700 focus:ring-slate-400"
                />
                <span className="text-slate-600">Tambah Watermark</span>
              </label>
            </div>
          </div>

          <form
            onSubmit={handleUpload}
            className="rounded-2xl border-2 border-dashed border-slate-300 bg-white p-6 text-center flex flex-col items-center justify-center"
          >
            <input
              type="file"
              id="image-input"
              accept="image/jpeg,image/png,image/webp"
              className="hidden"
              onChange={(e) => uploadForm.setData('image', e.target.files?.[0] ?? null)}
            />
            <label htmlFor="image-input" className="block cursor-pointer">
              <Upload className="mx-auto h-10 w-10 text-slate-400" />
              <p className="mt-3 text-sm font-medium text-slate-600">
                {uploadForm.data.image ? uploadForm.data.image.name : 'Pilih Gambar'}
              </p>
              <p className="mt-1 text-xs text-slate-400">JPEG, PNG, WEBP. Maks 10MB.</p>
            </label>
            {uploadForm.data.image && (
              <button
                type="submit"
                disabled={uploadForm.processing}
                className="admin-btn-primary mt-4"
              >
                {uploadForm.processing ? 'Memproses...' : 'Upload & Watermark'}
              </button>
            )}
          </form>
        </div>
      </div>

      {previewUrl && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
          role="presentation"
          onClick={() => setPreviewUrl(null)}
          onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') setPreviewUrl(null); }}
        >
          <div
            className="relative max-h-[90vh] max-w-[90vw] overflow-hidden rounded-2xl bg-white shadow-2xl"
            role="presentation"
            onClick={(e) => e.stopPropagation()}
            onKeyDown={(e) => e.stopPropagation()}
          >
            <button
              type="button"
              onClick={() => setPreviewUrl(null)}
              className="absolute right-3 top-3 z-10 rounded-full bg-black/50 p-1.5 text-white transition-colors hover:bg-black/70"
            >
              <X className="h-5 w-5" />
            </button>
            <img
              src={previewUrl}
              alt="Preview"
              className="max-h-[85vh] w-auto max-w-full object-contain"
            />
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
