import { Head, Link } from '@inertiajs/react';
import { MessageCircle, MoveRight, Truck } from 'lucide-react';
import { useState } from 'react';
import { normalizeWhatsAppPhone } from '@/lib/whatsapp';
import '../../../css/sticker-price-list.css';

interface Props {
    quantities: number[];
    tables: { type: string; rows: { id: number; name: string; shape: string | null; prices: (number | null)[] }[] }[];
    minimumSheets: number;
    adminPhone: string;
    shipping: { peninsular: number; sabahSarawak: number; freeThreshold: number };
}

const money = (value: number) => value.toLocaleString('en-MY', { minimumFractionDigits: Number.isInteger(value) ? 0 : 2, maximumFractionDigits: 2 });

export default function StickerPriceList({ quantities, tables, minimumSheets, adminPhone, shipping }: Props) {
    const [selectedType, setSelectedType] = useState(tables[0]?.type ?? '');
    const table = tables.find((item) => item.type === selectedType);
    const whatsappUrl = `https://wa.me/${normalizeWhatsAppPhone(adminPhone)}?text=${encodeURIComponent('Assalamualaikum/Hi, saya berminat untuk membuat tempahan sticker. Boleh saya lihat katalog?')}`;

    return (
        <div className="sticker-price-page">
            <Head title="Senarai Harga Sticker">
                <meta name="description" content="Semak harga sticker mengikut saiz dan kuantiti. Lihat katalog dan buat tempahan melalui WhatsApp tanpa perlu log masuk." />
            </Head>
            <main className="sticker-price-main">
                <header className="sticker-price-heading">
                    <img src="/images/logo-baru.webp" alt="StickerTermurah" width="180" height="70" />
                    <h1>Harga sticker bulat 1–10cm.</h1>
                    <p>Pilih saiz & kuantiti anda. Kami bantu uruskan tempahan melalui WhatsApp.</p>
                </header>

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
                            <div className="sticker-price-scroll" tabIndex={0} role="region" aria-label="Jadual harga, leret ke kanan untuk kuantiti lain">
                                <table>
                                    <caption className="sr-only">Harga {selectedType} mengikut saiz dan kuantiti, tidak termasuk pos.</caption>
                                    <thead><tr><th scope="col">Saiz</th>{quantities.map((qty) => <th key={qty} scope="col">{qty.toLocaleString()}<small>pcs</small></th>)}</tr></thead>
                                    <tbody>{table.rows.map((row) => (
                                        <tr key={row.id}>
                                            <th scope="row">{row.name.replace(/\bbulat\b\s*:?\s*/gi, '').trim()}</th>
                                            {row.prices.map((price, index) => <td key={quantities[index]}>{price === null ? <span aria-label="Hubungi kami untuk harga">—</span> : money(price)}</td>)}
                                        </tr>
                                    ))}</tbody>
                                </table>
                            </div>
                        </>
                    ) : <p className="sticker-price-empty">Harga sedang dikemas kini. Hubungi kami di WhatsApp untuk sebut harga.</p>}
                </section>

                <p className="sticker-price-explanation">Harga dikira mengikut helaian A3, termasuk diskaun yang layak. Minimum {minimumSheets} helai A3 tanpa design; 1 helai A3 dengan design sedia ada. Harga tidak termasuk pos.</p>

                <section className="sticker-price-details" aria-label="Maklumat tempahan">
                    <div className="sticker-price-delivery"><Truck size={23} aria-hidden="true" /><div><h2>Penghantaran seluruh Malaysia</h2><p>Semenanjung <strong>RM{money(shipping.peninsular)}</strong> · Sabah & Sarawak <strong>RM{money(shipping.sabahSarawak)}</strong></p><p>Pos percuma untuk subtotal RM{money(shipping.freeThreshold)} ke atas.</p></div></div>
                    <p>Design boleh pilih daripada katalog kami atau berikan contoh sendiri. Untuk saiz lain, hubungi kami di WhatsApp.</p>
                </section>

                <section className="sticker-price-contact">
                    <h2>Nak lihat katalog & buat tempahan?</h2>
                    <p>Klik butang di bawah untuk berbincang dengan kami.</p>
                    <a className="sticker-price-whatsapp" href={whatsappUrl} target="_blank" rel="noopener noreferrer"><MessageCircle size={22} aria-hidden="true" /> Buka WhatsApp <MoveRight size={18} aria-hidden="true" /></a>
                </section>
                <footer>Dengan menghubungi kami, anda bersetuju dengan <Link href={route('privacy-policy')}>dasar privasi</Link> StickerTermurah.</footer>
            </main>
        </div>
    );
}
