import { Download, Image, Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface DesignResult {
    id: number;
    name: string;
    category: string | null;
    image: string | null;
    preview: string | null;
    download: string | null;
    is_active: boolean;
}

export default function AdminDesignSearch() {
    const [query, setQuery] = useState('');
    const [open, setOpen] = useState(false);
    const [results, setResults] = useState<DesignResult[]>([]);
    const [status, setStatus] = useState<'loading' | 'done' | 'error'>('done');
    const container = useRef<HTMLFormElement>(null);
    const [selected, setSelected] = useState<DesignResult | null>(null);
    const [imageError, setImageError] = useState(false);
    const dialog = useRef<HTMLDialogElement>(null);

    useEffect(() => {
        if (!selected || !dialog.current) return;
        const element = dialog.current;
        const overflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        element.showModal();
        return () => { element.close(); document.body.style.overflow = overflow; };
    }, [selected]);

    useEffect(() => {
        const closeOutside = (event: PointerEvent) => {
            if (!container.current?.contains(event.target as Node)) setOpen(false);
        };
        document.addEventListener('pointerdown', closeOutside);
        return () => document.removeEventListener('pointerdown', closeOutside);
    }, []);

    useEffect(() => {
        setResults([]);
        if (!query.trim()) { setStatus('done'); return; }
        const controller = new AbortController();
        setStatus('loading');
        const timeout = window.setTimeout(async () => {
            try {
                const response = await fetch(`${route('admin.designs.search')}?q=${encodeURIComponent(query.trim())}`, {
                    signal: controller.signal, headers: { Accept: 'application/json' },
                });
                if (!response.ok) throw new Error();
                const result = await response.json();
                if (!controller.signal.aborted) { setResults(result.data); setStatus('done'); }
            } catch {
                if (!controller.signal.aborted) setStatus('error');
            }
        }, 300);
        return () => { window.clearTimeout(timeout); controller.abort(); };
    }, [query]);

    return <><form ref={container} role="search" aria-label="Carian design" onSubmit={event => { event.preventDefault(); setOpen(true); }}
        onKeyDown={event => { if (event.key === 'Escape') setOpen(false); }}
        onBlur={event => { if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setOpen(false); }}
        className="relative order-4 min-w-0 basis-[calc(50%-6px)] flex-1 sm:order-none sm:max-w-xs sm:basis-auto">
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input type="search" aria-label="Cari design" placeholder="Cari design..." maxLength={100} value={query}
            aria-expanded={open && !!query.trim()} aria-controls="admin-design-search-results"
            onChange={event => { setQuery(event.target.value); setOpen(true); }} onFocus={() => setOpen(true)}
            className="w-full rounded-full border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100" />
        {open && query.trim() && <div id="admin-design-search-results" aria-label="Hasil carian design"
            className="absolute right-0 top-full z-50 mt-2 max-h-[65dvh] w-72 max-w-[calc(100vw-2rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
            {status === 'loading' ? <p role="status" className="px-4 py-3 text-xs text-slate-500">Mencari design...</p>
                : status === 'error' ? <p role="alert" className="px-4 py-3 text-xs text-red-700">Carian gagal. Sila cuba lagi.</p>
                    : results.length ? <ul className="divide-y divide-slate-100">{results.map(design => <li key={design.id}>
                        <button type="button" onClick={() => { setOpen(false); setImageError(false); setSelected(design); }} className="flex w-full items-center gap-3 px-3 py-3 text-left transition hover:bg-slate-50 focus-visible:bg-brand-50 focus-visible:outline-none">
                            {design.image ? <img src={design.image} alt={design.name} width={64} height={64} className="h-16 w-16 shrink-0 rounded-lg bg-slate-50 object-contain" /> : <Image aria-hidden="true" className="h-16 w-16 shrink-0 rounded-lg bg-slate-50 p-4 text-slate-400" />}
                            <span className="min-w-0"><span className="block break-words text-sm font-semibold text-slate-900">{design.name}</span><span className="block text-xs text-slate-500">{design.category ?? 'Lain-lain'}{!design.is_active && ' · Tidak aktif'}</span></span>
                        </button>
                    </li>)}</ul> : <p role="status" className="px-4 py-3 text-xs text-slate-500">Design tidak ditemui.</p>}
        </div>}
    </form>
        <dialog ref={dialog} aria-labelledby="admin-design-preview-title" onCancel={() => setSelected(null)} onClose={() => setSelected(null)}
            onClick={event => { if (event.target === event.currentTarget) setSelected(null); }}
            className="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-xl backdrop:bg-slate-900/60">
            {selected && <>
                <div className="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <h2 id="admin-design-preview-title" className="break-words text-base font-bold">{selected.name}</h2>
                    <button type="button" aria-label="Tutup gambar design" onClick={() => setSelected(null)} className="rounded-lg p-2 hover:bg-slate-100"><X className="h-5 w-5" /></button>
                </div>
                <div className="p-4">
                    {selected.preview && !imageError ? <img src={selected.preview} alt={selected.name} onError={() => setImageError(true)} className="mx-auto max-h-[65dvh] w-full object-contain" /> : <p role="status" className="py-12 text-center text-sm text-slate-500">Gambar design tidak tersedia.</p>}
                    {selected.download ? <a href={selected.download} download className="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white hover:bg-brand-700"><Download className="h-4 w-4" /> Download Tanpa Watermark</a> : <p className="mt-4 text-center text-sm text-slate-500">Fail original tanpa watermark tidak tersedia.</p>}
                </div>
            </>}
        </dialog>
    </>;
}
