import { Head, Link } from '@inertiajs/react';
import { MessageCircle, MoveRight, Truck, Check, Palette } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { normalizeWhatsAppPhone } from '@/lib/whatsapp';
import StickerPriceTools from '@/Components/StickerPriceTools';
import '../../../css/sticker-price-list.css';

interface Props {
    quantities: number[];
    tables: { type: string; rows: { id: number; name: string; shape: string | null; prices: ({ original: number; discounted: number } | null)[] }[] }[];
    minimumSheets: number;
    calculatorShapes: string[];
    adminPhone: string;
    shipping: { peninsular: number; sabahSarawak: number; freeThreshold: number };
}

const money = (value: number) => value.toLocaleString('en-MY', { minimumFractionDigits: Number.isInteger(value) ? 0 : 2, maximumFractionDigits: 2 });

export default function StickerPriceList({ quantities, tables, adminPhone, shipping, minimumSheets, calculatorShapes }: Props) {
    const [selectedType, setSelectedType] = useState(tables[0]?.type ?? '');
    const table = tables.find((item) => item.type === selectedType);
    const scrollRef = useRef<HTMLDivElement>(null);
    const [hasOverflow, setHasOverflow] = useState(false);
    const pageRef = useRef<HTMLDivElement>(null);
    const dockRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const dock = dockRef.current;
        if (!dock) return;
        const observer = new ResizeObserver(() => {
            pageRef.current?.style.setProperty('--price-dock-height', `${dock.getBoundingClientRect().height}px`);
        });
        observer.observe(dock);
        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        const container = scrollRef.current;
        if (!container) {
            setHasOverflow(false);
            return;
        }

        const checkOverflow = () => setHasOverflow(container.scrollWidth > container.clientWidth + 1);
        const observer = new ResizeObserver(checkOverflow);
        observer.observe(container);
        if (container.firstElementChild) observer.observe(container.firstElementChild);
        checkOverflow();
        return () => observer.disconnect();
    }, [table]);
    const whatsappUrl = `https://wa.me/${normalizeWhatsAppPhone(adminPhone)}?text=${encodeURIComponent('Assalamualaikum.. Blh sy tahu lebih lanjut tentang sticker ini..')}`;

    return (
        <div ref={pageRef} className="sticker-price-page">
            <Head title="Senarai Harga Sticker">
                <meta name="description" content="Semak harga sticker mengikut saiz dan kuantiti. Lihat katalog dan buat tempahan melalui WhatsApp tanpa perlu log masuk." />
            </Head>
            <main className="sticker-price-main">
                <header className="sticker-price-heading">
                    <div className="sticker-price-hero-copy">
                    <img src="/images/logo-baru.webp" alt="StickerTermurah" width="180" height="70" />
                    <span className="sticker-price-eyebrow">STICKER KECIL. IMPAK BESAR.</span>
                    <h1>Biar produk anda<br /><span>lebih menyerlah.</span></h1>
                    <p>Sticker bulat 3–10cm untuk bisnes anda. Semak harga, pilih design dan tempah terus melalui WhatsApp.</p>
                    <a className="sticker-price-hero-link" href="#senarai-harga">Semak harga <MoveRight size={18} aria-hidden="true" /></a>
                    </div>
                    <div className="sticker-price-hero-visual" aria-label="Contoh design sticker">
                        <div className="sticker-price-samples">
                            <img src="/images/showcase/sticker-01.webp" alt="Contoh sticker Donut Ketagih" width="240" height="240" fetchPriority="high" />
                            <img src="/images/showcase/sticker-24.webp" alt="Contoh sticker Sambal Padu Mak Teh" width="240" height="240" />
                            <img src="/images/showcase/sticker-07.webp" alt="Contoh sticker Butter Bliss Bakery" width="240" height="240" />
                        </div>
                        <p>Untuk balang, kotak & bungkusan anda.</p>
                        <span className="sticker-price-sample-note"><Check size={15} aria-hidden="true" /> Design sendiri atau pilih dari katalog</span>
                    </div>
                </header>

                <div className="sticker-price-section-heading" id="senarai-harga"><h2>Saiz tepat. Harga jelas.</h2><p>Pilih jenis sticker, kemudian padankan saiz dan kuantiti.</p></div>
                {tables.length > 1 && (
                    <div className="sticker-price-tabs" role="group" aria-label="Jenis sticker">
                        {tables.map(({ type }) => (
                            <button key={type} type="button" aria-pressed={type === selectedType} onClick={() => setSelectedType(type)}>{type}</button>
                        ))}
                    </div>
                )}

                <section className="sticker-price-sheet" aria-label="Jadual harga sticker">
                    <div className="sticker-price-sheet-title">
                        <h2>Sticker {selectedType || 'Custom'}</h2>
                        <span>Harga dalam RM</span>
                    </div>
                    {table && table.rows.length > 0 ? (
                        <>
                            <div ref={scrollRef} className="sticker-price-scroll" tabIndex={0} role="region" aria-label="Jadual harga, leret ke kanan untuk kuantiti lain" aria-describedby={hasOverflow ? 'price-scroll-hint' : undefined}>
                                <table>
                                    <caption className="sr-only">Harga {selectedType} mengikut saiz dan kuantiti, tidak termasuk pos.</caption>
                                    <thead><tr><th scope="col">Saiz</th>{quantities.map((qty) => <th key={qty} scope="col">{qty.toLocaleString()}<small>pcs</small></th>)}</tr></thead>
                                    <tbody>{table.rows.map((row) => (
                                        <tr key={row.id}>
                                            <th scope="row">{row.name.replace(/\bbulat\b\s*:?\s*/gi, '').trim()}</th>
                                            {row.prices.map((price, index) => <td key={quantities[index]} className={price === null ? 'sticker-price-unavailable' : undefined} aria-label={price === null ? 'Tidak dijual untuk kuantiti ini' : undefined}>{price === null ? '-' : price.discounted < price.original ? (
                                                <span className="sticker-price-discount"><del aria-label="Harga asal">{money(price.original)}</del><strong aria-label="Harga selepas diskaun">{money(price.discounted)}</strong></span>
                                            ) : money(price.original)}</td>)}
                                        </tr>
                                    ))}</tbody>
                                </table>
                            </div>
                            {table.rows.some((row) => row.prices.some((price) => price === null)) && <p className="sticker-price-table-note">- : tidak dijual untuk kuantiti tersebut.</p>}
                            {hasOverflow && <p id="price-scroll-hint" className="sticker-price-table-note">Leret jadual ke tepi untuk melihat harga kuantiti lain.<MoveRight size={15} aria-hidden="true" /></p>}
                        </>
                    ) : <p className="sticker-price-empty">Harga sedang dikemas kini. Hubungi kami di WhatsApp untuk sebut harga.</p>}
                </section>

                <p className="sticker-price-explanation">Untuk harga saiz, bentuk atau kuantiti lain, sila hubungi admin melalui <a href={whatsappUrl} target="_blank" rel="noopener noreferrer" className="font-semibold underline underline-offset-2">WhatsApp</a>. Kami sedia membantu anda mendapatkan sebut harga yang sesuai.</p>

                <section className="sticker-price-details" aria-label="Maklumat tempahan">
                    <div className="sticker-price-delivery"><Truck size={23} aria-hidden="true" /><div><h2>Penghantaran seluruh Malaysia</h2><p>Semenanjung <strong>RM{money(shipping.peninsular)}</strong> · Sabah & Sarawak <strong>RM{money(shipping.sabahSarawak)}</strong></p><p>Pos percuma untuk subtotal RM{money(shipping.freeThreshold)} ke atas.</p></div></div>
                    <div className="sticker-price-delivery"><Palette size={23} aria-hidden="true" /><div><h2>Design ikut gaya bisnes anda</h2><p>Pilih daripada katalog atau berikan contoh sendiri. Saiz dan bentuk lain? Kami bantu semak.</p></div></div>
                </section>

                <section className="sticker-price-how" aria-labelledby="sticker-how-title">
                    <h2 id="sticker-how-title">Dari pilihan ke tempahan.</h2>
                    <ol><li><span>01</span><div><h3>Pilih sticker</h3><p>Semak saiz, jenis dan kuantiti dalam jadual.</p></div></li><li><span>02</span><div><h3>Tentukan design</h3><p>Lihat katalog atau sediakan design anda.</p></div></li><li><span>03</span><div><h3>WhatsApp kami</h3><p>Kami bantu sahkan harga dan butiran tempahan.</p></div></li></ol>
                </section>

                <div ref={dockRef} className="sticker-price-contact-dock">
                <section className="sticker-price-contact">
                    <StickerPriceTools shapes={calculatorShapes} types={tables.map(item => item.type)} phone={adminPhone} minimumSheets={minimumSheets} />
                    <a className="sticker-price-whatsapp" href={whatsappUrl} target="_blank" rel="noopener noreferrer"><MessageCircle size={22} aria-hidden="true" /> Tempah Sekarang <MoveRight size={18} aria-hidden="true" /></a>
                </section>
                <footer>Dengan menghubungi kami, anda bersetuju dengan <Link href={route('privacy-policy')}>dasar privasi</Link> StickerTermurah.</footer>
                </div>
            </main>
        </div>
    );
}
