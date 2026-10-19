import { useCallback, useEffect, useRef, useState } from 'react';
import { Calculator, Images, X } from 'lucide-react';
import { normalizeWhatsAppPhone } from '@/lib/whatsapp';

interface Design { id: number; name: string; image: string | null; mobile_image: string | null }
interface Quote { a3_sheets: number; price_per_a3: number; line_total: number }

export default function StickerPriceTools({ shapes, types, phone, minimumSheets }: { shapes: string[]; types: string[]; phone: string; minimumSheets: number }) {
    const [panel, setPanel] = useState<'catalog' | 'calculator' | null>(null);
    const dialog = useRef<HTMLDialogElement>(null);
    const scroller = useRef<HTMLDivElement>(null);
    const sentinel = useRef<HTMLDivElement>(null);
    const [designs, setDesigns] = useState<Design[]>([]);
    const [tags, setTags] = useState<string[]>([]);
    const [tag, setTag] = useState('');
    const [hasMore, setHasMore] = useState(true);
    const [loading, setLoading] = useState(false);
    const [catalogError, setCatalogError] = useState('');
    const busy = useRef(false);
    const [shape, setShape] = useState(shapes[0] ?? '');
    const [type, setType] = useState(types[0] ?? '');
    const [width, setWidth] = useState('');
    const [height, setHeight] = useState('');
    const [quantity, setQuantity] = useState('100');
    const [hasDesign, setHasDesign] = useState(false);
    const [quote, setQuote] = useState<Quote | null>(null);
    const [quoteState, setQuoteState] = useState<'idle' | 'loading' | 'done' | 'error'>('idle');
    const wa = (text: string) => `https://wa.me/${normalizeWhatsAppPhone(phone)}?text=${encodeURIComponent(text)}`;
    const loadDesigns = useCallback(async (reset = false, nextTag = tag, random = false) => {
        if (busy.current || (!reset && !hasMore)) return;
        busy.current = true;
        setLoading(true);
        setCatalogError('');
        try {
            const params = new URLSearchParams({ offset: String(reset ? 0 : designs.length), limit: '20', tag: nextTag, random: random ? '1' : '0' });
            const response = await fetch(`${route('sticker.catalog')}?${params}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error();
            const result = await response.json();
            setTags(result.tags ?? []);
            setDesigns(previous => reset ? result.data : [...previous, ...result.data]);
            setHasMore(!random && result.meta.has_more);
        } catch {
            setCatalogError('Katalog tidak dapat dimuatkan. Cuba lagi.');
        } finally {
            busy.current = false;
            setLoading(false);
        }
    }, [designs.length, hasMore, tag]);

    useEffect(() => {
        if (!panel || !dialog.current) return;
        const element = dialog.current;
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        element.showModal();
        return () => { element.close(); document.body.style.overflow = previousOverflow; };
    }, [panel]);

    useEffect(() => {
        if (panel === 'catalog' && designs.length === 0 && !catalogError) void loadDesigns();
    }, [panel, designs.length, catalogError, loadDesigns]);

    useEffect(() => {
        if (panel !== 'catalog' || !sentinel.current || !designs.length || loading || catalogError || !hasMore) return;
        const observer = new IntersectionObserver(entries => {
            if (entries.some(entry => entry.isIntersecting)) void loadDesigns();
        }, { root: scroller.current });
        observer.observe(sentinel.current);
        return () => observer.disconnect();
    }, [panel, designs.length, loading, catalogError, hasMore, loadDesigns]);

    const normalizedShape = shape.trim().toLowerCase().replace(/\s+/g, '');
    const equalSides = normalizedShape === 'bulat' || normalizedShape === 'segiempatsama';
    const effectiveHeight = equalSides ? width : height;
    const valid = !!shape && !!type && Number(width) > 0 && Number(width) <= 1000 && Number(effectiveHeight) > 0 && Number(effectiveHeight) <= 1000 && Number.isInteger(Number(quantity)) && Number(quantity) > 0 && Number(quantity) <= 1000000;
    useEffect(() => {
        setQuote(null);
        if (panel !== 'calculator' || !valid) { setQuoteState('idle'); return; }
        const controller = new AbortController();
        setQuoteState('loading');
        const timer = window.setTimeout(async () => {
            try {
                const params = new URLSearchParams({ shape, type, width, height: effectiveHeight, quantity, has_design: hasDesign ? '1' : '0' });
                const response = await fetch(`${route('sticker.quote')}?${params}`, { signal: controller.signal, headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error();
                const result = await response.json();
                if (!controller.signal.aborted) { setQuote(result.price); setQuoteState('done'); }
            } catch {
                if (!controller.signal.aborted) setQuoteState('error');
            }
        }, 300);
        return () => { window.clearTimeout(timer); controller.abort(); };
    }, [panel, valid, shape, type, width, effectiveHeight, quantity, hasDesign]);

    return <>
        <div className="sticker-price-tools">
            <button type="button" onClick={() => setPanel('catalog')}><Images size={16} /> Lihat Katalog</button>
            <button type="button" onClick={() => setPanel('calculator')}><Calculator size={16} /> Kira Harga</button>
        </div>
        <dialog ref={dialog} className="sticker-price-dialog sticker-price-page" aria-labelledby="sticker-tool-title" onCancel={() => setPanel(null)} onClose={() => setPanel(null)} onClick={event => { if (event.target === event.currentTarget) setPanel(null); }}>
            <header className="sticker-tool-header"><h2 id="sticker-tool-title">{panel === 'catalog' ? 'Katalog sticker' : 'Kira harga sticker'}</h2><button type="button" aria-label="Tutup popup" onClick={() => setPanel(null)}><X size={22} /></button></header>
            <div ref={scroller} className="sticker-tool-body">
                {panel === 'catalog' ? <>
                    <p className="sticker-tool-intro">Pilih design untuk tempah terus di WhatsApp.</p>
                    <div className="sticker-catalog-filters">
                        <label><span className="sr-only">Pilih hashtag</span><select value={tag} disabled={loading} onChange={event => { setTag(event.target.value); void loadDesigns(true, event.target.value); }}>
                            <option value="">Semua hashtag</option>
                            {tags.map(item => <option key={item} value={item}>#{item}</option>)}
                        </select></label>
                        <button type="button" className="sticker-tool-secondary" disabled={loading} onClick={() => void loadDesigns(true, tag, true)}>Random</button>
                    </div>
                    <div className="sticker-catalog-grid">{designs.map(design => <a key={design.id} href={wa(`Assalamualaikum.. nk tempah sticker design ${design.name}..`)}>
                        {design.mobile_image || design.image ? <img src={design.mobile_image || design.image!} alt={design.name} loading="lazy" width="240" height="240" /> : <span className="sticker-catalog-placeholder">Imej belum tersedia</span>}
                        <span>{design.name}</span>
                    </a>)}</div>
                    {loading && <p role="status">Memuatkan design…</p>}
                    {catalogError && <div role="alert"><p>{catalogError}</p><button type="button" className="sticker-tool-secondary" onClick={() => void loadDesigns()}>Cuba lagi</button></div>}
                    {!loading && !catalogError && designs.length === 0 && <p>Design sedang dikemas kini. Hubungi admin untuk katalog.</p>}
                    <div ref={sentinel} className="sticker-catalog-sentinel" aria-hidden="true" />
                    {!hasMore && designs.length > 0 && <p className="sticker-tool-intro">Semua design telah dipaparkan.</p>}
                </> : panel === 'calculator' ? <form onSubmit={event => event.preventDefault()}>
                    <p className="sticker-tool-intro">Pilih bentuk dan masukkan saiz dalam cm. Harga dikira automatik, tidak termasuk pos.</p>
                    <label>Bentuk sticker<select value={shape} onChange={event => setShape(event.target.value)}>{shapes.map(item => <option key={item}>{item}</option>)}<option value="Lain-lain">Lain-lain</option></select></label>
                    <label>Jenis sticker<select value={type} onChange={event => setType(event.target.value)}>{types.map(item => <option key={item}>{item}</option>)}</select></label>
                    {equalSides ? <label>{normalizedShape === 'bulat' ? 'Diameter (cm)' : 'Saiz sisi (cm)'}<input type="number" min="0.1" max="1000" step="0.1" required value={width} onChange={event => setWidth(event.target.value)} inputMode="decimal" /></label> : <div className="sticker-tool-dimensions">
                        <label>Lebar (cm)<input type="number" min="0.1" max="1000" step="0.1" required value={width} onChange={event => setWidth(event.target.value)} inputMode="decimal" /></label>
                        <label>Tinggi (cm)<input type="number" min="0.1" max="1000" step="0.1" required value={height} onChange={event => setHeight(event.target.value)} inputMode="decimal" /></label>
                    </div>}
                    <label>Kuantiti (pcs)<input type="number" min="1" max="1000000" step="1" required value={quantity} onChange={event => setQuantity(event.target.value)} inputMode="numeric" /></label>
                    <label>Status design<select value={hasDesign ? '1' : '0'} onChange={event => setHasDesign(event.target.value === '1')}><option value="0">Belum ada design (minimum {minimumSheets} A3)</option><option value="1">Sudah ada design (minimum 1 A3)</option></select></label>
                    <div className="sticker-quote-result" aria-live="polite">
                        {quoteState === 'idle' && <p>Lengkapkan saiz dan kuantiti untuk melihat harga.</p>}
                        {quoteState === 'loading' && <p>Mengira harga…</p>}
                        {quoteState === 'error' && <p role="alert">Kiraan tidak dapat dimuatkan. Sila hubungi admin.</p>}
                        {quoteState === 'done' && (quote ? <><span>Anggaran harga</span><strong>RM{quote.line_total.toFixed(2)}</strong><p>{quote.a3_sheets} helai A3 · Tidak termasuk pos</p></> : <p>Saiz atau harga ini belum tersedia dalam kalkulator.</p>)}
                    </div>
                    {valid && (quoteState === 'done' || quoteState === 'error') && <a className="sticker-tool-order" href={wa(`Assalamualaikum.. ${quote ? 'nk tempah sticker' : 'boleh kira harga sticker saiz ini'}.. Bentuk ${shape}, saiz ${width} x ${effectiveHeight}cm, ${quantity}pcs, jenis ${type}, ${hasDesign ? 'sudah ada design' : 'belum ada design'}..`)}>{quote ? 'Tempah di WhatsApp' : 'WhatsApp Admin untuk Kira Saiz Ini'}</a>}
                </form> : null}
            </div>
        </dialog>
    </>;
}
