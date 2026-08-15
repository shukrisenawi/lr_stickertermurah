import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

type AddressTab = 'members' | 'non-members';

interface CustomerOption {
  id: number;
  name: string;
  email: string | null;
  no_tel: string | null;
}

interface AddressValue {
  id: number;
  user_id: number | null;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

interface CustomerAddressFormProps {
  address: AddressValue | null;
  customers: CustomerOption[];
  tab: AddressTab;
}

export default function CustomerAddressForm({ address, customers, tab }: CustomerAddressFormProps) {
  const { data, setData, post, put, processing, errors } = useForm({
    user_id: address?.user_id ? String(address.user_id) : '',
    recipient_name: address?.recipient_name ?? '',
    address: address?.address ?? '',
    no_hp: address?.no_hp ?? '',
    is_default: address?.is_default ?? false,
  });

  const isEditing = address !== null;

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();

    if (isEditing) {
      put(route('admin.customer-addresses.update', address.id));
    } else {
      post(route('admin.customer-addresses.store'));
    }
  };

  return (
    <AdminLayout>
      <Head title={isEditing ? 'Kemaskini Customer Address' : 'Tambah Customer Address'} />
      <div className="max-w-2xl space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">
              {isEditing ? 'Kemaskini Customer Address' : 'Tambah Customer Address'}
            </h2>
            <p className="admin-page-copy">Urus maklumat alamat penghantaran customer.</p>
          </div>
          <Link href={route('admin.customer-addresses.index', { tab })} className="admin-btn-secondary text-sm">
            <ArrowLeft className="h-4 w-4" />
            Kembali
          </Link>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card space-y-5 p-6">
          <div>
            <label htmlFor="user_id" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              Pautkan Kepada Ahli
            </label>
            <select
              id="user_id"
              value={data.user_id}
              onChange={(event) => {
                setData('user_id', event.target.value);
                if (!event.target.value) setData('is_default', false);
              }}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            >
              <option value="">Bukan Ahli (tidak dipautkan)</option>
              {customers.map((customer) => (
                <option key={customer.id} value={customer.id}>
                  {customer.name}{customer.email ? ` - ${customer.email}` : ''}
                </option>
              ))}
            </select>
            {errors.user_id && <p className="mt-1 text-sm text-rose-600">{errors.user_id}</p>}
          </div>

          <div>
            <label htmlFor="recipient_name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              Nama Penerima
            </label>
            <input
              id="recipient_name"
              type="text"
              value={data.recipient_name}
              onChange={(event) => setData('recipient_name', event.target.value)}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              required
            />
            {errors.recipient_name && <p className="mt-1 text-sm text-rose-600">{errors.recipient_name}</p>}
          </div>

          <div>
            <label htmlFor="no_hp" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              No. Telefon
            </label>
            <input
              id="no_hp"
              type="text"
              value={data.no_hp}
              onChange={(event) => setData('no_hp', event.target.value)}
              maxLength={20}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
            />
            {errors.no_hp && <p className="mt-1 text-sm text-rose-600">{errors.no_hp}</p>}
          </div>

          <div>
            <label htmlFor="address" className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">
              Alamat
            </label>
            <textarea
              id="address"
              value={data.address}
              onChange={(event) => setData('address', event.target.value)}
              rows={5}
              maxLength={500}
              className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
              required
            />
            {errors.address && <p className="mt-1 text-sm text-rose-600">{errors.address}</p>}
          </div>

          <label className="flex items-center gap-3 text-sm text-slate-700">
            <input
              type="checkbox"
              checked={data.is_default}
              disabled={!data.user_id}
              onChange={(event) => setData('is_default', event.target.checked)}
              className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
            />
            Jadikan alamat default ahli ini
          </label>
          {errors.is_default && <p className="text-sm text-rose-600">{errors.is_default}</p>}

          <div className="flex items-center gap-3 pt-2">
            <Link href={route('admin.customer-addresses.index', { tab })} className="admin-btn-secondary flex-1 text-sm">
              Batal
            </Link>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              <Save className="h-4 w-4" />
              {processing ? 'Menyimpan...' : isEditing ? 'Kemaskini' : 'Simpan'}
            </button>
          </div>
        </form>
      </div>
    </AdminLayout>
  );
}
