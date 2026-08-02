import AdminLayout from '@/Components/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Download, Eye, FolderKanban, Plus, Search, Trash2 } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Project {
  id: number;
  title: string;
  notes: string | null;
  preview_url: string | null;
  source_files: Array<{ name: string; url: string }>;
  created_at: string;
  user: { name: string; email: string } | null;
  order: { order_no: string } | null;
}

interface PaginationLink { url: string | null; label: string; active: boolean }

export default function ProjectsIndex({ projects, search }: { projects: { data: Project[]; links: PaginationLink[] }; search: string }) {
  const { data, setData, get } = useForm({ q: search });
  const submit = (event: React.FormEvent) => {
    event.preventDefault();
    get(route('admin.projects.index'), { preserveState: true });
  };

  return (
    <AdminLayout>
      <Head title="Projects Customer" />
      <div className="space-y-6">
        <div className="admin-page-head">
          <div><h2 className="text-2xl font-bold text-slate-900">Projects Customer</h2><p className="admin-page-copy">Cari design yang pernah disiapkan untuk setiap customer.</p></div>
          <Link href={route('admin.projects.create')} className="admin-btn-primary"><Plus className="h-4 w-4" />Tambah Project</Link>
        </div>
        <form onSubmit={submit} className="admin-toolbar-card">
          <div className="relative max-w-lg flex-1"><Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input value={data.q} onChange={(e) => setData('q', e.target.value)} placeholder="Cari nama design, customer atau no. order..." className="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none focus:border-brand-300 focus:ring-2 focus:ring-brand-100" /></div>
          <button type="submit" className="admin-btn-secondary text-sm">Cari</button>
        </form>
        <div className="admin-table-card">
          <div className="admin-table-wrap"><table className="admin-table"><thead><tr><th>Preview</th><th>Project</th><th>Customer</th><th>Order</th><th>Source</th><th>Tarikh</th><th></th></tr></thead>
            <tbody>{projects.data.length === 0 ? <tr><td colSpan={7} className="py-16 text-center"><FolderKanban className="mx-auto h-12 w-12 text-slate-300" /><p className="mt-3 font-semibold text-slate-900">Tiada project</p></td></tr> : projects.data.map((project) => <tr key={project.id}>
              <td>{project.preview_url ? <img src={project.preview_url} alt={project.title} className="h-16 w-24 rounded-lg border border-slate-200 object-contain bg-slate-50" /> : <span className="text-xs text-slate-400">Tiada gambar</span>}</td>
              <td><p className="font-semibold text-slate-900">{project.title}</p>{project.notes && <p className="max-w-xs truncate text-xs text-slate-500">{project.notes}</p>}</td>
              <td><p>{project.user?.name ?? '-'}</p><p className="text-xs text-slate-500">{project.user?.email}</p></td>
              <td>{project.order?.order_no ?? '-'}</td>
              <td><div className="flex max-w-48 flex-col gap-1">{project.source_files.map((file) => <a key={file.url} href={file.url} className="inline-flex items-center gap-1 truncate text-xs font-medium text-brand-600 hover:text-brand-800"><Download className="h-3.5 w-3.5 shrink-0" />{file.name}</a>)}</div></td>
              <td className="text-slate-500">{formatDate(project.created_at)}</td>
              <td><div className="flex items-center gap-1">{project.preview_url && <a href={project.preview_url} target="_blank" rel="noreferrer" className="rounded-lg p-2 text-slate-500 hover:bg-slate-50"><Eye className="h-4 w-4" /></a>}<Link href={route('admin.projects.destroy', project.id)} method="delete" as="button" type="button" className="rounded-lg p-2 text-rose-500 hover:bg-rose-50"><Trash2 className="h-4 w-4" /></Link></div></td>
            </tr>)}</tbody>
          </table></div>
          {projects.links.length > 3 && <div className="flex gap-2 border-t border-slate-200 px-6 py-4">{projects.links.map((link) => {
            const label = link.label.replace(/&laquo;/g, 'Prev').replace(/&raquo;/g, 'Next');
            return link.url ? <Link key={`${link.label}-${link.url}`} href={link.url} className={`rounded-lg px-3 py-1.5 text-sm ${link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-50'}`}>{label}</Link> : <span key={`disabled-${link.label}`} className="rounded-lg px-3 py-1.5 text-sm text-slate-400">{label}</span>;
          })}</div>}
        </div>
      </div>
    </AdminLayout>
  );
}
