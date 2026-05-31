import { useState } from 'react';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Send, CheckCircle, XCircle, RefreshCw } from 'lucide-react';

const TEST_URL = 'https://n8n-mt8umikivytz.n8x.biz.id/webhook-test/368036b6-64ef-43a1-b296-3dc3ec12ebef';
const LIVE_URL = 'https://n8n-mt8umikivytz.n8x.biz.id/webhook/368036b6-64ef-43a1-b296-3dc3ec12ebef';

interface N8nProps {
  webhookUrl: string;
}

export default function N8nSettings({ webhookUrl }: N8nProps) {
  const { data, setData, put, processing, errors } = useForm({ webhook_url: webhookUrl });
  const [testResult, setTestResult] = useState<{ success: boolean; message: string } | null>(null);
  const [testing, setTesting] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(route('admin.settings.n8n.update'));
  };

  const handleTest = async () => {
    setTesting(true);
    setTestResult(null);
    try {
      const res = await fetch(route('admin.settings.n8n.test'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content },
        body: JSON.stringify({ webhook_url: data.webhook_url }),
      });
      const json = await res.json();
      setTestResult({
        success: json.success,
        message: json.success ? `Berjaya (HTTP ${json.status})` : `Gagal: ${json.error || json.body || 'Tiada respon'}`,
      });
    } catch {
      setTestResult({ success: false, message: 'Ralat sambungan ke pelayan' });
    } finally {
      setTesting(false);
    }
  };

  return (
    <AdminLayout>
      <Head title="Tetapan N8n" />
      <div className="space-y-6 max-w-xl">
        <Link href={route('admin.dashboard')} className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-brand-600 transition">
          <ArrowLeft className="h-4 w-4" />
          Dashboard
        </Link>

        <div>
          <h2 className="text-2xl font-bold text-slate-900">Tetapan N8n Webhook</h2>
          <p className="admin-page-copy">Konfigurasi webhook N8n untuk penghantaran notifikasi tempahan.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-flat-card p-6 space-y-5">
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Webhook URL</label>
            <input
              type="url"
              value={data.webhook_url}
              onChange={(e) => setData('webhook_url', e.target.value)}
              placeholder="https://n8n.example.com/webhook/xxx"
              className="admin-input w-full text-sm"
            />
            {errors.webhook_url && <p className="mt-1 text-sm text-rose-600">{errors.webhook_url}</p>}
            <div className="mt-2 flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => setData('webhook_url', TEST_URL)}
                className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
              >
                Guna URL Test
              </button>
              <button
                type="button"
                onClick={() => setData('webhook_url', LIVE_URL)}
                className="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
              >
                Guna URL Live
              </button>
            </div>
          </div>

          <div className="flex items-center gap-3 pt-2">
            <button
              type="button"
              onClick={handleTest}
              disabled={testing}
              className="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
            >
              {testing ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4" />}
              {testing ? 'Menghantar...' : 'Uji Webhook'}
            </button>
            <button type="submit" disabled={processing} className="admin-btn-primary flex-1 text-sm">
              {processing ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>

          {testResult && (
            <div className={`flex items-start gap-3 rounded-xl p-4 text-sm ${testResult.success ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>
              {testResult.success ? <CheckCircle className="mt-0.5 h-4 w-4 shrink-0" /> : <XCircle className="mt-0.5 h-4 w-4 shrink-0" />}
              <span>{testResult.message}</span>
            </div>
          )}
        </form>
      </div>
    </AdminLayout>
  );
}
