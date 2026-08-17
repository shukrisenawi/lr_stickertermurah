import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Eye, FolderKanban, RotateCcw, ShoppingCart, X } from 'lucide-react';
import { formatDate } from '@/lib/utils';

interface Project {
  id: number;
  title: string;
  notes: string | null;
  preview_files: Array<{ url: string }>;
  order_id: number | null;
  order_no: string | null;
  created_at: string;
}

export default function MemberProjectsIndex({ projects }: { projects: Project[] }) {
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);

  useEffect(() => {
    if (!selectedProject) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setSelectedProject(null);
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [selectedProject]);

  return (
    <MemberLayout>
      <Head title="Design Saya" />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8">
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-slate-900">Design Saya</h1>
          <p className="mt-1 text-sm text-slate-500">Preview design tempahan anda.</p>
        </div>

        {projects.length === 0 ? (
          <div className="frontend-flat-card py-16 text-center">
            <FolderKanban className="mx-auto h-12 w-12 text-slate-300" />
            <p className="mt-3 font-semibold text-slate-900">Belum ada design</p>
            <p className="mt-1 text-sm text-slate-500">
              Design yang telah dimasukkan oleh admin akan muncul di sini.
            </p>
          </div>
        ) : (
          <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {projects.map((project) => (
              <article key={project.id} className="frontend-flat-card overflow-hidden">
                <div className="grid min-h-[250px] grid-cols-2 gap-1 bg-slate-100">
                  {project.preview_files.map((preview) => (
                    <img
                      key={preview.url}
                      src={preview.url}
                      alt={`${project.title} preview`}
                      loading="lazy"
                      decoding="async"
                      width="500"
                      height="250"
                      className="h-[250px] w-full object-contain"
                    />
                  ))}
                </div>
                <div className="space-y-3 p-5">
                  <div>
                    <h2 className="font-bold text-slate-900">{project.title}</h2>
                    <p className="mt-1 text-xs text-slate-500">
                      {project.order_no ? `Order ${project.order_no}` : 'Project customer'} ·{' '}
                      {formatDate(project.created_at)}
                    </p>
                  </div>
                  {project.notes && <p className="text-sm text-slate-600">{project.notes}</p>}
                  <div className="flex flex-wrap gap-2">
                    {project.order_id && (
                      <Link
                        href={route('member.orders.repeat', { order: project.order_id, project_id: project.id })}
                        method="post"
                        as="button"
                        type="button"
                        className="frontend-btn-primary text-xs"
                      >
                        <RotateCcw className="h-3.5 w-3.5" />
                        Order Design Ini Lagi
                      </Link>
                    )}
                    <button
                      type="button"
                      aria-haspopup="dialog"
                      onClick={() => setSelectedProject(project)}
                      className="frontend-btn-secondary text-xs"
                    >
                      <Eye className="h-3.5 w-3.5" />
                      Lihat Preview
                    </button>
                  </div>
                </div>
              </article>
            ))}
          </div>
        )}
      </div>

      {selectedProject && (
        <div
          className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-3 backdrop-blur-sm sm:p-6"
          role="presentation"
        >
          <button
            type="button"
            aria-label="Tutup preview"
            className="absolute inset-0 cursor-default"
            onClick={() => setSelectedProject(null)}
          />
          <div
            className="relative z-10 flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="project-preview-title"
          >
            <div className="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
              <div className="min-w-0">
                <p className="text-xs font-bold uppercase tracking-[0.16em] text-brand-600">Preview design</p>
                <h2 id="project-preview-title" className="mt-1 truncate text-lg font-bold text-slate-900">
                  {selectedProject.title}
                </h2>
                <p className="mt-1 text-xs text-slate-500">
                  {selectedProject.preview_files.length} preview
                  {selectedProject.preview_files.length === 1 ? '' : 's'}
                </p>
              </div>
              <button
                type="button"
                onClick={() => setSelectedProject(null)}
                aria-label="Tutup preview"
                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <div className="grid min-h-0 flex-1 gap-4 overflow-y-auto bg-slate-100 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">
              {selectedProject.preview_files.map((preview, index) => (
                <div
                  key={preview.url}
                  className="flex min-h-[220px] items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm"
                >
                  <img
                    src={preview.url}
                    alt={`${selectedProject.title} preview ${index + 1}`}
                    loading={index === 0 ? 'eager' : 'lazy'}
                    decoding="async"
                    className="max-h-[60vh] w-full object-contain"
                  />
                </div>
              ))}
            </div>

            <div className="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
              <button
                type="button"
                onClick={() => setSelectedProject(null)}
                className="frontend-btn-secondary text-xs"
              >
                Tutup
              </button>
              <Link
                href={route('member.orders.create', { project_id: selectedProject.id })}
                className="frontend-btn-primary text-xs"
              >
                <ShoppingCart className="h-3.5 w-3.5" />
                Pilih Design &amp; Order
              </Link>
            </div>
          </div>
        </div>
      )}
    </MemberLayout>
  );
}
