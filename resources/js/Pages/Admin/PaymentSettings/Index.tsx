import { useState, useEffect } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { Save, Image as ImageIcon, Building2 } from 'lucide-react';

interface PaymentSettingsProps {
  settings: {
    company_name: string | null;
    company_address: string | null;
    company_phone: string | null;
    company_logo_url: string | null;
    bank_name: string;
    bank_account_no: string;
    bank_account_name: string;
    admin_phone: string;
    admin_email: string;
    deposit_amount: number;
    bank_logo_url: string | null;
    qr_image_url: string | null;
  } | null;
}

export default function PaymentSettingsIndex({ settings }: PaymentSettingsProps) {
  const { data, setData, post, processing, errors } = useForm({
    _method: 'PUT',
    company_name: settings?.company_name ?? '',
    company_address: settings?.company_address ?? '',
    company_phone: settings?.company_phone ?? '',
    company_logo: null as File | null,
    bank_name: settings?.bank_name ?? 'Bank Islam',
    bank_account_no: settings?.bank_account_no ?? '',
    bank_account_name: settings?.bank_account_name ?? '',
    admin_phone: settings?.admin_phone ?? '',
    admin_email: settings?.admin_email ?? '',
    deposit_amount: settings?.deposit_amount ?? 20,
    bank_logo: null as File | null,
    qr_image: null as File | null,
  });

  const [companyLogoPreview, setCompanyLogoPreview] = useState<string | null>(settings?.company_logo_url ?? null);
  const [bankLogoPreview, setBankLogoPreview] = useState<string | null>(settings?.bank_logo_url ?? null);
  const [qrPreviewUrl, setQrPreviewUrl] = useState<string | null>(settings?.qr_image_url ?? null);

  useEffect(() => {
    if (data.company_logo) {
      const url = URL.createObjectURL(data.company_logo);
      setCompanyLogoPreview(url);
      return () => URL.revokeObjectURL(url);
    }
  }, [data.company_logo]);

  useEffect(() => {
    if (data.bank_logo) {
      const url = URL.createObjectURL(data.bank_logo);
      setBankLogoPreview(url);
      return () => URL.revokeObjectURL(url);
    }
  }, [data.bank_logo]);

  useEffect(() => {
    if (data.qr_image) {
      const url = URL.createObjectURL(data.qr_image);
      setQrPreviewUrl(url);
      return () => URL.revokeObjectURL(url);
    }
  }, [data.qr_image]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('admin.payment-settings.update'));
  };

  return (
    <AdminLayout>
      <Head title="Maklumat Bayaran & Syarikat" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Maklumat Bayaran & Syarikat</h2>
            <p className="admin-page-copy">Kemaskini maklumat syarikat, bank, QR code & deposit.</p>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="max-w-2xl space-y-6">
          {/* Company Info */}
          <div className="admin-flat-card p-6 space-y-5">
            <h3 className="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500">
              <Building2 className="h-4 w-4" />
              Maklumat Syarikat
            </h3>

            {/* Company Logo */}
            <div>
              <label htmlFor="company_logo">Logo Syarikat</label>
              <div className="mt-1.5 flex items-center gap-4">
                {companyLogoPreview ? (
                  <img src={companyLogoPreview} alt="Logo Syarikat" className="h-16 w-16 rounded-xl object-contain border border-slate-200 p-1" />
                ) : (
                  <div className="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100">
                    <ImageIcon className="h-7 w-7 text-slate-300" />
                  </div>
                )}
                <div>
                  <input
                    id="company_logo"
                    type="file"
                    accept="image/*"
                    onChange={(e) => setData('company_logo', e.target.files?.[0] ?? null)}
                    className="text-sm"
                  />
                  <p className="mt-1 text-xs text-slate-400">JPG, PNG, WebP. Maks 2MB.</p>
                </div>
              </div>
              {errors.company_logo && <p className="mt-1 text-xs text-rose-600">{errors.company_logo}</p>}
            </div>

            <div>
              <label htmlFor="company_name">Nama Syarikat</label>
              <input
                id="company_name"
                type="text"
                value={data.company_name}
                onChange={(e) => setData('company_name', e.target.value)}
                placeholder="cth: SH Best Creative Design"
                className="mt-1.5"
              />
              {errors.company_name && <p className="mt-1 text-xs text-rose-600">{errors.company_name}</p>}
            </div>

            <div>
              <label htmlFor="company_phone">No. Telefon Syarikat</label>
              <input
                id="company_phone"
                type="text"
                value={data.company_phone}
                onChange={(e) => setData('company_phone', e.target.value)}
                placeholder="cth: 011-69409606"
                className="mt-1.5"
              />
              {errors.company_phone && <p className="mt-1 text-xs text-rose-600">{errors.company_phone}</p>}
            </div>

            <div>
              <label htmlFor="company_address">Alamat Syarikat</label>
              <textarea
                id="company_address"
                rows={3}
                value={data.company_address}
                onChange={(e) => setData('company_address', e.target.value)}
                placeholder="Alamat penuh syarikat"
                className="mt-1.5"
              />
              {errors.company_address && <p className="mt-1 text-xs text-rose-600">{errors.company_address}</p>}
            </div>
          </div>

          {/* Payment Info */}
          <div className="admin-flat-card p-6 space-y-5">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
              Maklumat Bayaran
            </h3>

            {/* Bank Logo */}
            <div>
              <label htmlFor="bank_logo">Logo Bank</label>
              <div className="mt-1.5 flex items-center gap-4">
                {bankLogoPreview ? (
                  <img src={bankLogoPreview} alt="Bank Logo" className="h-14 w-auto rounded-lg object-contain border border-slate-200 p-1" />
                ) : (
                  <div className="flex h-14 w-24 items-center justify-center rounded-lg bg-slate-100">
                    <ImageIcon className="h-6 w-6 text-slate-300" />
                  </div>
                )}
                <div>
                  <input
                    id="bank_logo"
                    type="file"
                    accept="image/*"
                    onChange={(e) => setData('bank_logo', e.target.files?.[0] ?? null)}
                    className="text-sm"
                  />
                  <p className="mt-1 text-xs text-slate-400">JPG, PNG, WebP. Maks 2MB.</p>
                </div>
              </div>
              {errors.bank_logo && <p className="mt-1 text-xs text-rose-600">{errors.bank_logo}</p>}
            </div>

            <div>
              <label htmlFor="bank_name">Nama Bank</label>
              <input
                id="bank_name"
                type="text"
                value={data.bank_name}
                onChange={(e) => setData('bank_name', e.target.value)}
                className="mt-1.5"
              />
              {errors.bank_name && <p className="mt-1 text-xs text-rose-600">{errors.bank_name}</p>}
            </div>

            <div>
              <label htmlFor="bank_account_no">No. Akaun Bank</label>
              <input
                id="bank_account_no"
                type="text"
                value={data.bank_account_no}
                onChange={(e) => setData('bank_account_no', e.target.value)}
                className="mt-1.5"
              />
              {errors.bank_account_no && <p className="mt-1 text-xs text-rose-600">{errors.bank_account_no}</p>}
            </div>

            <div>
              <label htmlFor="bank_account_name">Nama Pemilik Akaun</label>
              <input
                id="bank_account_name"
                type="text"
                value={data.bank_account_name}
                onChange={(e) => setData('bank_account_name', e.target.value)}
                className="mt-1.5"
              />
              {errors.bank_account_name && <p className="mt-1 text-xs text-rose-600">{errors.bank_account_name}</p>}
            </div>

            <div>
              <label htmlFor="admin_phone">No. Telefon Admin (WhatsApp)</label>
              <input
                id="admin_phone"
                type="text"
                value={data.admin_phone}
                onChange={(e) => setData('admin_phone', e.target.value)}
                className="mt-1.5"
              />
              {errors.admin_phone && <p className="mt-1 text-xs text-rose-600">{errors.admin_phone}</p>}
            </div>

            <div>
              <label htmlFor="admin_email">Email Admin</label>
              <input
                id="admin_email"
                type="email"
                value={data.admin_email}
                onChange={(e) => setData('admin_email', e.target.value)}
                className="mt-1.5"
              />
              {errors.admin_email && <p className="mt-1 text-xs text-rose-600">{errors.admin_email}</p>}
            </div>

            <div>
              <label htmlFor="deposit_amount">Jumlah Deposit (RM)</label>
              <input
                id="deposit_amount"
                type="number"
                step="0.01"
                min="0"
                value={data.deposit_amount}
                onChange={(e) => setData('deposit_amount', parseFloat(e.target.value))}
                className="mt-1.5 w-auto"
              />
              {errors.deposit_amount && <p className="mt-1 text-xs text-rose-600">{errors.deposit_amount}</p>}
            </div>

            {/* QR Image */}
            <div>
              <label htmlFor="qr_image">Gambar QR</label>
              <div className="mt-1.5 flex items-center gap-4">
                {qrPreviewUrl ? (
                  <img src={qrPreviewUrl} alt="QR Preview" className="h-24 w-24 rounded-xl object-contain border border-slate-200" />
                ) : (
                  <div className="flex h-24 w-24 items-center justify-center rounded-xl bg-slate-100">
                    <ImageIcon className="h-8 w-8 text-slate-300" />
                  </div>
                )}
                <div>
                  <input
                    id="qr_image"
                    type="file"
                    accept="image/*"
                    onChange={(e) => setData('qr_image', e.target.files?.[0] ?? null)}
                    className="text-sm"
                  />
                  <p className="mt-1 text-xs text-slate-400">JPG, PNG. Maks 5MB.</p>
                </div>
              </div>
              {errors.qr_image && <p className="mt-1 text-xs text-rose-600">{errors.qr_image}</p>}
            </div>
          </div>

          {/* Submit */}
          <div className="pt-2">
            <button
              type="submit"
              disabled={processing}
              className="admin-btn-primary"
            >
              <Save className="h-4 w-4" />
              {processing ? 'Menyimpan...' : 'Simpan Maklumat'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}