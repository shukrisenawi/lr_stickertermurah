import { useEffect, useState } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
  Activity,
  ArrowLeft,
  BarChart3,
  CircleAlert,
  ExternalLink,
  Megaphone,
  Pause,
  Pencil,
  Play,
  Plus,
  RefreshCw,
  Target,
  Users,
  X,
} from 'lucide-react';

type CampaignStatus = 'ACTIVE' | 'PAUSED';

interface MetaAdsConfiguration {
  configured: boolean;
  appIdConfigured: boolean;
  accessTokenConfigured: boolean;
  adAccountConfigured: boolean;
  appId: string | null;
  adAccountId: string | null;
  apiVersion: string;
  currency: string;
}

interface CampaignInsights {
  spend: number;
  impressions: number;
  reach: number;
  clicks: number;
  ctr: number;
  cpc: number;
}

interface MetaCampaign {
  id: string;
  name: string;
  objective: string;
  status: string;
  effectiveStatus: string;
  createdTime: string | null;
  insights: CampaignInsights;
}

interface MetaAdsSummary {
  campaigns: number;
  activeCampaigns: number;
  spend: number;
  impressions: number;
  clicks: number;
}

interface MetaAdsProps {
  configuration: MetaAdsConfiguration;
  campaigns: MetaCampaign[];
  summary: MetaAdsSummary;
  datePreset: string;
  reportError: string | null;
}

const objectives = [
  { value: 'OUTCOME_AWARENESS', label: 'Kesedaran' },
  { value: 'OUTCOME_TRAFFIC', label: 'Traffic' },
  { value: 'OUTCOME_ENGAGEMENT', label: 'Engagement' },
  { value: 'OUTCOME_LEADS', label: 'Leads' },
  { value: 'OUTCOME_APP_PROMOTION', label: 'Promosi aplikasi' },
  { value: 'OUTCOME_SALES', label: 'Sales' },
];

const objectiveLabels = Object.fromEntries(objectives.map((objective) => [objective.value, objective.label]));

function formatNumber(value: number): string {
  return value.toLocaleString('ms-MY');
}

function formatMoney(value: number, currency: string): string {
  return new Intl.NumberFormat('ms-MY', {
    style: 'currency',
    currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

function formatDate(value: string | null): string {
  if (!value) return '-';

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;

  return new Intl.DateTimeFormat('ms-MY', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(date);
}

function statusLabel(status: string): string {
  if (status === 'ACTIVE') return 'Aktif';
  if (status === 'PAUSED') return 'Dijeda';
  if (status === 'ARCHIVED') return 'Diarkib';
  if (status === 'DELETED') return 'Dipadam';
  return status.replace(/_/g, ' ');
}

function statusClass(status: string): string {
  if (status === 'ACTIVE') return 'bg-emerald-100 text-emerald-700';
  if (status === 'PAUSED') return 'bg-amber-100 text-amber-700';
  return 'bg-slate-100 text-slate-600';
}

function MetaMetric({ label, value, copy, icon: Icon }: {
  label: string;
  value: string;
  copy: string;
  icon: React.ComponentType<{ className?: string }>;
}) {
  return (
    <div className="admin-kpi-card">
      <div className="flex items-center justify-between gap-3">
        <p className="admin-kpi-label">{label}</p>
        <Icon className="h-4 w-4 text-brand-600" />
      </div>
      <p className="admin-kpi-value">{value}</p>
      <p className="mt-1 text-xs text-slate-500">{copy}</p>
    </div>
  );
}

export default function MetaAdsIndex({
  configuration,
  campaigns,
  summary,
  datePreset,
  reportError,
}: MetaAdsProps) {
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const createForm = useForm({
    name: '',
    objective: 'OUTCOME_TRAFFIC',
  });
  const updateForm = useForm<{ name: string; status: CampaignStatus }>({
    name: '',
    status: 'PAUSED',
  });
  const [editingCampaign, setEditingCampaign] = useState<MetaCampaign | null>(null);
  const [actionCampaignId, setActionCampaignId] = useState<string | null>(null);

  const managerUrl = configuration.adAccountId
    ? `https://www.facebook.com/adsmanager/manage/campaigns?act=${configuration.adAccountId.replace(/^act_/, '')}`
    : null;

  const handleCreate = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    createForm.post(route('admin.meta-ads.campaigns.store'), {
      preserveScroll: true,
      onSuccess: closeCreate,
    });
  };

  const openCreate = () => {
    createForm.reset();
    createForm.clearErrors();
    setCreateModalOpen(true);
  };

  const closeCreate = () => {
    if (createForm.processing) return;

    setCreateModalOpen(false);
    createForm.reset();
    createForm.clearErrors();
  };

  const openEdit = (campaign: MetaCampaign) => {
    setEditingCampaign(campaign);
    updateForm.setData('name', campaign.name);
    updateForm.setData('status', campaign.status === 'ACTIVE' ? 'ACTIVE' : 'PAUSED');
    updateForm.clearErrors();
  };

  const closeEdit = () => {
    if (updateForm.processing) return;

    setEditingCampaign(null);
    updateForm.reset();
    updateForm.clearErrors();
  };

  useEffect(() => {
    if (!createModalOpen && !editingCampaign) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const handleEscape = (event: KeyboardEvent) => {
      if (event.key !== 'Escape' || createForm.processing || updateForm.processing) return;

      if (editingCampaign) {
        setEditingCampaign(null);
      } else {
        setCreateModalOpen(false);
      }
    };

    window.addEventListener('keydown', handleEscape);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleEscape);
    };
  }, [createModalOpen, editingCampaign, createForm.processing, updateForm.processing]);

  const handleEdit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!editingCampaign) return;

    if (updateForm.data.status === 'ACTIVE' && editingCampaign.status !== 'ACTIVE' && !confirm('Aktifkan kempen ini? Tindakan ini boleh mula membelanjakan bajet iklan.')) {
      return;
    }

    updateForm.put(route('admin.meta-ads.campaigns.update', editingCampaign.id), {
      preserveScroll: true,
      onSuccess: closeEdit,
    });
  };

  const toggleCampaign = (campaign: MetaCampaign) => {
    const nextStatus: CampaignStatus = campaign.status === 'ACTIVE' ? 'PAUSED' : 'ACTIVE';

    if (nextStatus === 'ACTIVE' && !confirm('Aktifkan kempen ini? Tindakan ini boleh mula membelanjakan bajet iklan.')) {
      return;
    }

    setActionCampaignId(campaign.id);
    router.put(route('admin.meta-ads.campaigns.update', campaign.id), {
      name: campaign.name,
      status: nextStatus,
    }, {
      preserveScroll: true,
      onFinish: () => setActionCampaignId(null),
    });
  };

  return (
    <AdminLayout>
      <Head title="Iklan Meta" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div>
            <h2 className="text-2xl font-bold text-slate-900">Iklan Meta</h2>
            <p className="admin-page-copy">Urus kempen Facebook dan semak prestasi iklan melalui Meta Marketing API.</p>
          </div>
          <div className="flex flex-wrap items-center gap-2">
            <button
              type="button"
              onClick={openCreate}
              disabled={!configuration.configured || createForm.processing}
              className="admin-btn-primary text-sm disabled:cursor-not-allowed disabled:opacity-50"
            >
              <Plus className="h-4 w-4" />
              Cipta kempen
            </button>
            <Link href={route('admin.dashboard')} className="admin-btn-secondary text-sm">
              <ArrowLeft className="h-4 w-4" />
              Dashboard
            </Link>
            {managerUrl && (
              <a href={managerUrl} target="_blank" rel="noreferrer" className="admin-btn-primary text-sm">
                Buka Ads Manager
                <ExternalLink className="h-4 w-4" />
              </a>
            )}
          </div>
        </div>

        <div className="grid gap-3 md:grid-cols-3">
          <div className="admin-kpi-card">
            <div className="flex items-center gap-3">
              <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${configuration.configured ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'}`}>
                <Megaphone className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="admin-kpi-value">{configuration.configured ? 'Bersambung' : 'Belum set'}</p>
                <p className="truncate text-xs font-medium text-slate-500">Status Meta API</p>
              </div>
            </div>
            <p className="mt-3 text-xs text-slate-500">Graph API {configuration.apiVersion}</p>
          </div>

          <div className="admin-kpi-card">
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                <Target className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="break-all text-xl font-bold tracking-tight text-slate-900">{configuration.adAccountId ?? 'Belum set'}</p>
                <p className="truncate text-xs font-medium text-slate-500">Ad Account</p>
              </div>
            </div>
            <p className="mt-3 text-xs text-slate-500">Token disimpan dalam konfigurasi server dan tidak dipaparkan.</p>
          </div>

          <div className="admin-kpi-card">
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <BarChart3 className="h-5 w-5" />
              </div>
              <div className="min-w-0">
                <p className="admin-kpi-value">{formatNumber(summary.campaigns)}</p>
                <p className="truncate text-xs font-medium text-slate-500">Kempen dipaparkan</p>
              </div>
            </div>
            <p className="mt-3 text-xs text-slate-500">Data disegerakkan daripada Meta Ads.</p>
          </div>
        </div>

        {reportError && (
          <div className="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <CircleAlert className="mt-0.5 h-5 w-5 shrink-0" />
            <div>
              <p className="font-semibold">Sambungan Meta Ads belum tersedia</p>
              <p className="mt-1 leading-5">{reportError}</p>
            </div>
          </div>
        )}

        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <MetaMetric label="Kempen aktif" value={formatNumber(summary.activeCampaigns)} copy="Status ACTIVE sekarang" icon={Activity} />
          <MetaMetric label="Belanja" value={formatMoney(summary.spend, configuration.currency)} copy="30 hari terakhir" icon={BarChart3} />
          <MetaMetric label="Impressions" value={formatNumber(summary.impressions)} copy="30 hari terakhir" icon={Users} />
          <MetaMetric label="Clicks" value={formatNumber(summary.clicks)} copy="30 hari terakhir" icon={Target} />
        </div>

        <section className="admin-table-card">
          <div className="admin-card-header flex-col items-start gap-3 sm:flex-row sm:items-center">
            <div className="flex items-center gap-2.5">
              <div className="admin-icon-badge">
                <Megaphone className="h-4 w-4" />
              </div>
              <div>
                <h3 className="text-base font-bold text-slate-900">Senarai kempen</h3>
                <p className="text-xs text-slate-500">Prestasi {datePreset === 'last_30d' ? '30 hari terakhir' : datePreset} daripada Meta Insights.</p>
              </div>
            </div>
            <Link href={`${route('admin.meta-ads.index')}?refresh=1`} preserveScroll className="admin-btn-secondary text-xs">
              <RefreshCw className="h-3.5 w-3.5" />
              Segar data
            </Link>
          </div>

          <div className="admin-table-wrap">
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Kempen</th>
                  <th>Objektif</th>
                  <th>Status</th>
                  <th>Belanja</th>
                  <th>Impressions</th>
                  <th>Clicks</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {campaigns.length === 0 ? (
                  <tr>
                    <td colSpan={7} className="py-16 text-center">
                      <div className="admin-table-empty">
                        <Megaphone className="mx-auto h-12 w-12 text-slate-300" />
                        <p className="admin-table-empty-title">Tiada kempen</p>
                        <p className="admin-table-empty-copy">Cipta kempen pertama atau semak konfigurasi Meta API.</p>
                      </div>
                    </td>
                  </tr>
                ) : campaigns.map((campaign) => (
                  <tr key={campaign.id}>
                    <td className="min-w-[15rem]">
                      <p className="font-semibold text-slate-900">{campaign.name}</p>
                      <p className="mt-1 text-[11px] text-slate-400">ID {campaign.id} · {formatDate(campaign.createdTime)}</p>
                    </td>
                    <td>{objectiveLabels[campaign.objective] ?? campaign.objective.replace(/_/g, ' ')}</td>
                    <td>
                      <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${statusClass(campaign.status)}`}>
                        {statusLabel(campaign.status)}
                      </span>
                      {campaign.effectiveStatus !== campaign.status && (
                        <p className="mt-1 text-[10px] text-slate-400">Efektif: {statusLabel(campaign.effectiveStatus)}</p>
                      )}
                    </td>
                    <td className="whitespace-nowrap font-medium">{formatMoney(campaign.insights.spend, configuration.currency)}</td>
                    <td>{formatNumber(campaign.insights.impressions)}</td>
                    <td>{formatNumber(campaign.insights.clicks)}</td>
                    <td>
                      <div className="flex items-center gap-1">
                        <button
                          type="button"
                          onClick={() => openEdit(campaign)}
                          className="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                          aria-label={`Edit ${campaign.name}`}
                        >
                          <Pencil className="h-3.5 w-3.5" />
                          Edit
                        </button>
                        <button
                          type="button"
                          onClick={() => toggleCampaign(campaign)}
                          disabled={actionCampaignId === campaign.id}
                          className={`inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium transition disabled:cursor-not-allowed disabled:opacity-50 ${campaign.status === 'ACTIVE' ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50'}`}
                          aria-label={`${campaign.status === 'ACTIVE' ? 'Jeda' : 'Aktifkan'} ${campaign.name}`}
                        >
                          {actionCampaignId === campaign.id ? <RefreshCw className="h-3.5 w-3.5 animate-spin" /> : campaign.status === 'ACTIVE' ? <Pause className="h-3.5 w-3.5" /> : <Play className="h-3.5 w-3.5" />}
                          {campaign.status === 'ACTIVE' ? 'Jeda' : 'Aktifkan'}
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      </div>

      {createModalOpen && (
        <div className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm" role="presentation">
          <div className="max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="create-meta-campaign-title">
            <div className="flex items-start justify-between gap-4">
              <div>
                <h3 id="create-meta-campaign-title" className="text-lg font-bold text-slate-900">Cipta kempen</h3>
                <p className="mt-1 text-xs leading-5 text-slate-500">Kempen baharu sentiasa dicipta sebagai PAUSED untuk semakan sebelum live.</p>
              </div>
              <button type="button" onClick={closeCreate} disabled={createForm.processing} className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50" aria-label="Tutup">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="mt-6 space-y-5">
              <div>
                <label htmlFor="campaign_name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama kempen</label>
                <input
                  id="campaign_name"
                  type="text"
                  value={createForm.data.name}
                  onChange={(event) => createForm.setData('name', event.target.value)}
                  placeholder="Contoh: Promo Sticker September"
                  disabled={createForm.processing}
                  required
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 disabled:cursor-not-allowed disabled:bg-slate-50"
                />
                {createForm.errors.name && <p className="mt-1 text-sm text-rose-600">{createForm.errors.name}</p>}
              </div>

              <div>
                <label htmlFor="campaign_objective" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Objektif</label>
                <select
                  id="campaign_objective"
                  value={createForm.data.objective}
                  onChange={(event) => createForm.setData('objective', event.target.value)}
                  disabled={createForm.processing}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 disabled:cursor-not-allowed disabled:bg-slate-50"
                >
                  {objectives.map((objective) => (
                    <option key={objective.value} value={objective.value}>{objective.label}</option>
                  ))}
                </select>
                {createForm.errors.objective && <p className="mt-1 text-sm text-rose-600">{createForm.errors.objective}</p>}
              </div>

              <div className="flex items-center gap-3 pt-1">
                <button type="button" onClick={closeCreate} disabled={createForm.processing} className="admin-btn-secondary flex-1 text-sm">Batal</button>
                <button type="submit" disabled={createForm.processing} className="admin-btn-primary flex-1 text-sm disabled:cursor-not-allowed disabled:opacity-50">
                  {createForm.processing ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Plus className="h-4 w-4" />}
                  {createForm.processing ? 'Mencipta...' : 'Cipta sebagai PAUSED'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {editingCampaign && (
        <div className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/45 p-4" role="dialog" aria-modal="true" aria-labelledby="edit-meta-campaign-title">
          <div className="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl">
            <div className="flex items-start justify-between gap-4">
              <div>
                <h3 id="edit-meta-campaign-title" className="text-lg font-bold text-slate-900">Edit kempen</h3>
                <p className="mt-1 text-xs text-slate-500">ID {editingCampaign.id}</p>
              </div>
              <button type="button" onClick={closeEdit} className="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleEdit} className="mt-6 space-y-5">
              <div>
                <label htmlFor="edit_campaign_name" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama kempen</label>
                <input
                  id="edit_campaign_name"
                  type="text"
                  value={updateForm.data.name}
                  onChange={(event) => updateForm.setData('name', event.target.value)}
                  disabled={updateForm.processing}
                  required
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 disabled:bg-slate-50"
                />
                {updateForm.errors.name && <p className="mt-1 text-sm text-rose-600">{updateForm.errors.name}</p>}
              </div>

              <div>
                <label htmlFor="edit_campaign_status" className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                <select
                  id="edit_campaign_status"
                  value={updateForm.data.status}
                  onChange={(event) => updateForm.setData('status', event.target.value as CampaignStatus)}
                  disabled={updateForm.processing}
                  className="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 disabled:bg-slate-50"
                >
                  <option value="PAUSED">Dijeda</option>
                  <option value="ACTIVE">Aktif</option>
                </select>
                {updateForm.errors.status && <p className="mt-1 text-sm text-rose-600">{updateForm.errors.status}</p>}
              </div>

              <div className="flex items-center gap-3 pt-1">
                <button type="button" onClick={closeEdit} className="admin-btn-secondary flex-1 text-sm">Batal</button>
                <button type="submit" disabled={updateForm.processing} className="admin-btn-primary flex-1 text-sm disabled:cursor-not-allowed disabled:opacity-50">
                  {updateForm.processing ? 'Menyimpan...' : 'Simpan perubahan'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </AdminLayout>
  );
}
