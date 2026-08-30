import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import {
  Truck,
  Search,
  FileText,
  AlertCircle,
  CheckCircle,
} from 'lucide-react';

interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  tracking_no: string | null;
}

interface Waybill {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  tracking_no: string;
  created_at: string;
}

interface JntIndexProps {
  orders: Order[];
  selectedOrder: Order | null;
  waybillResult: Record<string, unknown> | null;
  trackingResult: Record<string, unknown> | null;
  activeTab: string;
  waybills: {
    data: Waybill[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  waybillSearch: string;
}

export default function JntIndex({
  orders,
  selectedOrder,
  waybillResult,
  trackingResult,
  activeTab,
  waybills,
  waybillSearch: initialWaybillSearch,
}: JntIndexProps) {
  const [tab, setTab] = useState(activeTab);

  const {
    data: waybillData,
    setData: setWaybillData,
    post: postWaybill,
    processing: waybillProcessing,
  } = useForm({
    order_id: selectedOrder?.id || '',
    txlogistic_id: '',
    express_type: 'EZ',
    service_type: '1',
    pay_type: 'PP_PM',
    receiver_name: selectedOrder?.customer_name || '',
    receiver_phone: selectedOrder?.customer_phone || '',
    receiver_country_code: 'MY',
    receiver_postcode: '',
    receiver_address: selectedOrder?.customer_address || '',
    item_name: 'Sticker',
    item_quantity: 1,
    item_weight: 0.1,
    item_value: 10,
    package_quantity: 1,
    package_weight: 0.1,
    package_value: 10,
    goods_type: 'ITN2',
    package_length: '',
    package_width: '',
    package_height: '',
    remark: '',
  });

  const {
    data: trackData,
    setData: setTrackData,
    post: postTrack,
    processing: trackProcessing,
  } = useForm({
    bill_code: '',
    txlogistic_id: '',
  });

  const { data: searchData, setData: setSearchData, get: searchGet } = useForm({
    waybill_q: initialWaybillSearch,
  });

  const previousWaybillSearch = useRef(initialWaybillSearch);

  useEffect(() => {
    if (previousWaybillSearch.current === searchData.waybill_q) return;

    previousWaybillSearch.current = searchData.waybill_q;
    const timeout = window.setTimeout(() => {
      searchGet(route('admin.jnt.index', { tab: 'list' }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      });
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [searchData.waybill_q, searchGet]);

  const handleWaybillSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    postWaybill(route('admin.jnt.waybill'));
  };

  const handleTrackSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    postTrack(route('admin.jnt.tracking'));
  };

  const tabs = [
    { id: 'create', label: 'Cipta Waybill', icon: Truck },
    { id: 'tracking', label: 'Semak Tracking', icon: Search },
    { id: 'list', label: 'Senarai Waybill', icon: FileText },
  ];

  return (
    <AdminLayout>
      <Head title="J&T Express" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">J&T Express</h2>
            <p className="admin-page-copy">Cipta waybill dan semak tracking J&T.</p>
          </div>
        </div>

        {/* Tabs */}
        <div className="flex gap-1 rounded-xl bg-white p-1 shadow-sm border border-slate-200">
          {tabs.map((t) => (
            <button
              key={t.id}
              type="button"
              onClick={() => {
                setTab(t.id);
                window.history.replaceState(null, '', route('admin.jnt.index', { tab: t.id }));
              }}
              className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition ${
                tab === t.id
                  ? 'bg-brand-600 text-white shadow-sm'
                  : 'text-slate-600 hover:bg-slate-50'
              }`}
            >
              <t.icon className="h-4 w-4" />
              {t.label}
            </button>
          ))}
        </div>

        {/* Create Waybill */}
        {tab === 'create' && (
          <form onSubmit={handleWaybillSubmit} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
              <div className="space-y-4 lg:col-span-2">
                <div className="admin-flat-card p-6 space-y-4">
                  <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Maklumat Order
                  </h3>
                  <div>
                    <label htmlFor="order_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                      Pilih Order
                    </label>
                    <select
                      id="order_id"
                      value={waybillData.order_id}
                      onChange={(e) => {
                        const id = parseInt(e.target.value);
                        const o = orders.find((x) => x.id === id);
                        setWaybillData('order_id', e.target.value);
                        if (o) {
                          setWaybillData('receiver_name', o.customer_name);
                          setWaybillData('receiver_phone', o.customer_phone);
                          setWaybillData('receiver_address', o.customer_address);
                        }
                      }}
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    >
                      <option value="">Pilih order...</option>
                      {orders.map((o) => (
                        <option key={o.id} value={o.id}>
                          {o.order_no} — {o.customer_name}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                <div className="admin-flat-card p-6 space-y-4">
                  <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Maklumat Penerima
                  </h3>
                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <label htmlFor="receiver_name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Nama Penerima
                      </label>
                      <input
                        id="receiver_name"
                        type="text"
                        value={waybillData.receiver_name}
                        onChange={(e) => setWaybillData('receiver_name', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="receiver_phone" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        No. Telefon
                      </label>
                      <input
                        id="receiver_phone"
                        type="text"
                        value={waybillData.receiver_phone}
                        onChange={(e) => setWaybillData('receiver_phone', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                  </div>
                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                      <label htmlFor="receiver_country_code" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Kod Negara
                      </label>
                      <input
                        id="receiver_country_code"
                        type="text"
                        value={waybillData.receiver_country_code}
                        onChange={(e) => setWaybillData('receiver_country_code', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div className="sm:col-span-2">
                      <label htmlFor="receiver_postcode" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Poskod
                      </label>
                      <input
                        id="receiver_postcode"
                        type="text"
                        value={waybillData.receiver_postcode}
                        onChange={(e) => setWaybillData('receiver_postcode', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                  </div>
                  <div>
                    <label htmlFor="receiver_address" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                      Alamat Penuh
                    </label>
                    <textarea
                      id="receiver_address"
                      value={waybillData.receiver_address}
                      onChange={(e) => setWaybillData('receiver_address', e.target.value)}
                      rows={3}
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      required
                    />
                  </div>
                </div>

                <div className="admin-flat-card p-6 space-y-4">
                  <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Maklumat Bungkusan
                  </h3>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label htmlFor="express_type" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Jenis Express
                      </label>
                      <select
                        id="express_type"
                        value={waybillData.express_type}
                        onChange={(e) => setWaybillData('express_type', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      >
                        <option value="EZ">EZ</option>
                        <option value="EX">EX</option>
                        <option value="FD">FD</option>
                        <option value="DO">DO</option>
                        <option value="JS">JS</option>
                      </select>
                    </div>
                    <div>
                      <label htmlFor="service_type" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Jenis Perkhidmatan
                      </label>
                      <select
                        id="service_type"
                        value={waybillData.service_type}
                        onChange={(e) => setWaybillData('service_type', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      >
                        <option value="1">1</option>
                        <option value="6">6</option>
                      </select>
                    </div>
                    <div>
                      <label htmlFor="pay_type" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Kaedah Bayaran
                      </label>
                      <select
                        id="pay_type"
                        value={waybillData.pay_type}
                        onChange={(e) => setWaybillData('pay_type', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      >
                        <option value="PP_PM">PP_PM</option>
                        <option value="PP_CASH">PP_CASH</option>
                        <option value="CC_CASH">CC_CASH</option>
                      </select>
                    </div>
                    <div>
                      <label htmlFor="goods_type" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Jenis Barangan
                      </label>
                      <select
                        id="goods_type"
                        value={waybillData.goods_type}
                        onChange={(e) => setWaybillData('goods_type', e.target.value)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      >
                        <option value="ITN2">ITN2</option>
                        <option value="ITN8">ITN8</option>
                      </select>
                    </div>
                  </div>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label htmlFor="item_quantity" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Kuantiti Item
                      </label>
                      <input
                        id="item_quantity"
                        type="number"
                        min={1}
                        value={waybillData.item_quantity}
                        onChange={(e) => setWaybillData('item_quantity', parseInt(e.target.value) || 0)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="item_weight" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Berat Item (kg)
                      </label>
                      <input
                        id="item_weight"
                        type="number"
                        step="0.01"
                        min="0.01"
                        value={waybillData.item_weight}
                        onChange={(e) => setWaybillData('item_weight', parseFloat(e.target.value) || 0)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="item_value" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Nilai Item (RM)
                      </label>
                      <input
                        id="item_value"
                        type="number"
                        step="0.01"
                        min="0.01"
                        value={waybillData.item_value}
                        onChange={(e) => setWaybillData('item_value', parseFloat(e.target.value) || 0)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="package_quantity" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Kuantiti Bungkusan
                      </label>
                      <input
                        id="package_quantity"
                        type="number"
                        min={1}
                        value={waybillData.package_quantity}
                        onChange={(e) => setWaybillData('package_quantity', parseInt(e.target.value) || 0)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="package_weight" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Berat Bungkusan (kg)
                      </label>
                      <input
                        id="package_weight"
                        type="number"
                        step="0.01"
                        min="0.01"
                        value={waybillData.package_weight}
                        onChange={(e) => setWaybillData('package_weight', parseFloat(e.target.value) || 0)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="package_value" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Nilai Bungkusan (RM)
                      </label>
                      <input
                        id="package_value"
                        type="number"
                        step="0.01"
                        min="0.01"
                        value={waybillData.package_value}
                        onChange={(e) => setWaybillData('package_value', parseFloat(e.target.value) || 0)}
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                  </div>
                  <div>
                    <label htmlFor="remark" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                      Catatan
                    </label>
                    <input
                      id="remark"
                      type="text"
                      value={waybillData.remark}
                      onChange={(e) => setWaybillData('remark', e.target.value)}
                      className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                  </div>
                </div>
              </div>

              <div className="space-y-4">
                <div className="admin-flat-card p-6">
                  <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">
                    Tindakan
                  </h3>
                  <button
                    type="submit"
                    disabled={waybillProcessing}
                    className="admin-btn-primary w-full text-sm"
                  >
                    <Truck className="h-4 w-4" />
                    {waybillProcessing ? 'Mencipta...' : 'Cipta Waybill'}
                  </button>
                </div>

                {waybillResult && (
                  <div className={`rounded-xl border p-4 text-sm ${
                    String(waybillResult.code) === '1' || String(waybillResult.code) === '11'
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                      : 'border-rose-200 bg-rose-50 text-rose-800'
                  }`}>
                    <div className="flex items-start gap-2">
                      {String(waybillResult.code) === '1' || String(waybillResult.code) === '11' ? (
                        <CheckCircle className="h-5 w-5 shrink-0" />
                      ) : (
                        <AlertCircle className="h-5 w-5 shrink-0" />
                      )}
                      <div>
                        <p className="font-medium">{(waybillResult.msg as string) ?? 'Tiada mesej'}</p>
                        {!!waybillResult.data && (
                          <pre className="mt-2 overflow-auto rounded-lg bg-white/60 p-2 text-xs">
                            {JSON.stringify(waybillResult.data as object, null, 2)}
                          </pre>
                        )}
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </form>
        )}

        {/* Tracking */}
        {tab === 'tracking' && (
          <form onSubmit={handleTrackSubmit} className="space-y-6">
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
              <div className="lg:col-span-2 space-y-4">
                <div className="admin-flat-card p-6 space-y-4">
                  <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                    Semak Tracking
                  </h3>
                  <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <label htmlFor="bill_code" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Bill Code
                      </label>
                      <input
                        id="bill_code"
                        type="text"
                        value={trackData.bill_code}
                        onChange={(e) => setTrackData('bill_code', e.target.value)}
                        placeholder="Contoh: JNT123456"
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      />
                    </div>
                    <div>
                      <label htmlFor="txlogistic_id" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        TxLogistic ID
                      </label>
                      <input
                        id="txlogistic_id"
                        type="text"
                        value={trackData.txlogistic_id}
                        onChange={(e) => setTrackData('txlogistic_id', e.target.value)}
                        placeholder="Contoh: ORD20240101120000"
                        className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      />
                    </div>
                  </div>
                  <button
                    type="submit"
                    disabled={trackProcessing}
                    className="admin-btn-primary text-sm"
                  >
                    <Search className="h-4 w-4" />
                    {trackProcessing ? 'Menyemak...' : 'Semak Tracking'}
                  </button>
                </div>

                {trackingResult && (
                  <div className={`rounded-xl border p-4 text-sm ${
                    String(trackingResult.code) === '1' || String(trackingResult.code) === '11'
                      ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                      : 'border-rose-200 bg-rose-50 text-rose-800'
                  }`}>
                    <div className="flex items-start gap-2">
                      {String(trackingResult.code) === '1' || String(trackingResult.code) === '11' ? (
                        <CheckCircle className="h-5 w-5 shrink-0" />
                      ) : (
                        <AlertCircle className="h-5 w-5 shrink-0" />
                      )}
                      <div>
                        <p className="font-medium">{(trackingResult.msg as string) ?? 'Tiada mesej'}</p>
                        {!!trackingResult.data && (
                          <pre className="mt-2 overflow-auto rounded-lg bg-white/60 p-2 text-xs">
                            {JSON.stringify(trackingResult.data as object, null, 2)}
                          </pre>
                        )}
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </form>
        )}

        {/* Waybill List */}
        {tab === 'list' && (
          <div className="space-y-6">
            <div className="admin-toolbar-card">
              <div className="flex w-full max-w-md flex-1 items-center">
                <div className="relative w-full">
                  <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                  <input
                    type="search"
                    value={searchData.waybill_q}
                    onChange={(e) => setSearchData('waybill_q', e.target.value)}
                    placeholder="Cari waybill..."
                    className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  />
                </div>
              </div>
            </div>

            <div className="admin-table-card">
              <div className="admin-table-wrap">
                <table className="admin-table">
                  <thead>
                    <tr>
                      <th>No. Order</th>
                      <th>Pelanggan</th>
                      <th>No. Telefon</th>
                      <th>No. Tracking</th>
                    </tr>
                  </thead>
                  <tbody>
                    {waybills.data.length === 0 ? (
                      <tr>
                        <td colSpan={4} className="py-16 text-center">
                          <div className="admin-table-empty">
                            <FileText className="mx-auto h-12 w-12 text-slate-300" />
                            <p className="admin-table-empty-title">Tiada Waybill</p>
                          </div>
                        </td>
                      </tr>
                    ) : (
                      waybills.data.map((w) => (
                        <tr key={w.id}>
                          <td className="font-medium text-slate-900">{w.order_no}</td>
                          <td>{w.customer_name}</td>
                          <td>{w.customer_phone}</td>
                          <td className="font-medium text-brand-600">{w.tracking_no}</td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>

              {waybills.links.length > 3 && (
                <div className="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                  <div className="flex items-center gap-2">
                    {waybills.links.map((link) => {
                      const label = link.label.replace(/&laquo;|&raquo;/g, '').trim();
                      return link.url ? (
                        <Link
                          key={link.label}
                          href={link.url}
                          className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                            link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'
                          }`}
                        >
                          {label}
                        </Link>
                      ) : (
                        <span
                          key={link.label}
                          className="rounded-lg px-3 py-1.5 text-sm text-slate-400"
                        >
                          {label}
                        </span>
                      );
                    })}
                  </div>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </AdminLayout>
  );
}
