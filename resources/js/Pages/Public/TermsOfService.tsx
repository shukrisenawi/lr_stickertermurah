import LegalDocument, { LegalSection } from '@/Components/LegalDocument';
import { usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';

const bulletClass = 'list-disc space-y-2 pl-5';

export default function TermsOfService() {
  const { app } = usePage<PageProps>().props;

  return (
    <LegalDocument
      title="Terma Perkhidmatan"
      eyebrow="Terma & Syarat"
      description="Terma ini menetapkan syarat penggunaan laman web StickerTermurah dan pembelian perkhidmatan cetakan sticker daripada kami."
      updatedAt="19 Ogos 2026"
      icon="terms"
    >
      <LegalSection id="penerimaan" title="1. Penerimaan Terma">
        <p>
          Dengan mengakses laman web, membuat akaun, menghantar tempahan atau menggunakan perkhidmatan StickerTermurah, anda bersetuju untuk mematuhi terma ini serta Polisi Privasi kami. Jika anda bertindak bagi pihak perniagaan atau organisasi, anda mengesahkan bahawa anda mempunyai kuasa untuk berbuat demikian.
        </p>
      </LegalSection>

      <LegalSection id="perkhidmatan" title="2. Perkhidmatan Kami">
        <p>
          StickerTermurah menyediakan perkhidmatan cetakan sticker dan produk berkaitan berdasarkan maklumat, reka bentuk, saiz, bahan, kuantiti dan arahan yang dipilih oleh pelanggan. Contoh reka bentuk di laman web adalah untuk rujukan dan hasil sebenar boleh berbeza mengikut skrin, bahan, kemasan dan proses cetakan.
        </p>
        <p>
          Kami berhak menolak tempahan yang melanggar undang-undang, mengandungi kandungan berbahaya atau tidak dapat dihasilkan dengan selamat dan munasabah.
        </p>
      </LegalSection>

      <LegalSection id="akaun" title="3. Akaun dan Maklumat Pelanggan">
        <p>
          Anda bertanggungjawab memastikan nama, nombor telefon, alamat, e-mel dan maklumat tempahan yang diberikan adalah tepat. Anda juga bertanggungjawab menjaga kata laluan dan semua aktiviti yang berlaku melalui akaun anda.
        </p>
        <p>
          Sila maklumkan kepada kami dengan segera jika anda mengesyaki akaun digunakan tanpa kebenaran. Kami boleh menggantung atau menamatkan akaun yang digunakan untuk penipuan, penyalahgunaan atau pelanggaran terma ini.
        </p>
      </LegalSection>

      <LegalSection id="tempahan-harga" title="4. Tempahan, Harga dan Pembayaran">
        <ul className={bulletClass}>
          <li>Tempahan hanya dianggap diterima selepas maklumat tempahan disahkan dan bayaran atau deposit yang diperlukan diterima.</li>
          <li>Harga, promosi, kos penghantaran dan anggaran siap boleh berubah sebelum pengesahan tempahan.</li>
          <li>Anda bertanggungjawab menyemak nama, nombor telefon, alamat, reka bentuk, saiz, kuantiti dan butiran lain sebelum membuat pengesahan.</li>
          <li>Kelewatan yang berpunca daripada maklumat tidak lengkap, perubahan arahan atau bahan yang perlu dihantar semula mungkin mengubah tarikh siap.</li>
        </ul>
      </LegalSection>

      <LegalSection id="reka-bentuk" title="5. Kandungan dan Hak Reka Bentuk">
        <p>
          Anda mengekalkan hak terhadap kandungan atau reka bentuk yang anda hantar, tetapi anda memberi kami kebenaran terhad untuk menggunakannya bagi tujuan menyediakan, mencetak dan menghantar tempahan anda. Anda menjamin bahawa anda mempunyai kebenaran untuk menggunakan semua logo, gambar, teks, font dan bahan yang dihantar.
        </p>
        <p>
          Anda tidak boleh menghantar kandungan yang melanggar hak cipta, tanda dagangan, privasi, undang-undang atau hak pihak lain. Anda bersetuju untuk menanggung tuntutan yang berpunca daripada kandungan yang anda bekalkan.
        </p>
      </LegalSection>

      <LegalSection id="penghantaran" title="6. Penghantaran dan Pemeriksaan">
        <p>
          Penghantaran dibuat melalui kaedah kurier yang tersedia dan risiko kelewatan selepas barang diserahkan kepada kurier berada di luar kawalan munasabah kami. Sila semak bungkusan dan produk apabila diterima serta hubungi kami secepat mungkin jika terdapat kerosakan atau ketidakpadanan.
        </p>
        <p>
          Tuntutan berkaitan kesilapan cetakan, kerosakan atau kekurangan hendaklah disertakan dengan gambar dan butiran tempahan supaya kami boleh menyiasat dan menentukan penyelesaian yang sesuai.
        </p>
      </LegalSection>

      <LegalSection id="pembatalan" title="7. Pembatalan dan Pemulangan">
        <p>
          Oleh sebab produk cetakan biasanya dibuat mengikut spesifikasi pelanggan, pembatalan atau pemulangan mungkin tidak tersedia selepas proses produksi bermula. Sebarang permintaan pembatalan akan dinilai berdasarkan status produksi, bahan yang telah digunakan dan kos yang telah ditanggung.
        </p>
        <p>
          Jika kesilapan berpunca daripada pihak kami, kami akan menilai pilihan pembetulan, cetakan semula, kredit atau pemulangan yang munasabah berdasarkan keadaan kes.
        </p>
      </LegalSection>

      <LegalSection id="google" title="8. Integrasi Google Contacts">
        <p>
          Integrasi Google Contacts hanya tersedia kepada pentadbir yang diberi kuasa. Penggunaan integrasi tersebut tertakluk kepada kebenaran OAuth, Google API Services User Data Policy dan Polisi Privasi kami. Pentadbir boleh melihat, menambah, mengemaskini atau memadam contact melalui fungsi yang disediakan selepas akaun Google disambungkan.
        </p>
        <p>
          Kami tidak bertanggungjawab terhadap perubahan, kehilangan atau gangguan yang berlaku dalam akaun Google akibat tindakan pengguna, dasar Google, gangguan API atau faktor pihak ketiga. Pengguna hendaklah memastikan tindakan yang dibuat melalui integrasi adalah disengajakan.
        </p>
      </LegalSection>

      <LegalSection id="hak-milik" title="9. Hak Milik Intelektual Laman">
        <p>
          Kandungan laman web seperti teks, logo, susun atur, kod, gambar dan elemen jenama adalah milik StickerTermurah atau digunakan dengan kebenaran. Kandungan tersebut tidak boleh disalin, dijual, diubah suai atau digunakan semula tanpa kebenaran bertulis, kecuali setakat yang dibenarkan oleh undang-undang.
        </p>
      </LegalSection>

      <LegalSection id="had-liabiliti" title="10. Had Tanggungjawab">
        <p>
          Kami akan berusaha menyediakan perkhidmatan yang munasabah dan selamat, tetapi laman web dan perkhidmatan boleh terganggu akibat penyelenggaraan, gangguan rangkaian, kegagalan pembekal atau keadaan di luar kawalan kami. Setakat yang dibenarkan undang-undang, kami tidak bertanggungjawab terhadap kerugian tidak langsung, kehilangan keuntungan atau kehilangan data yang bukan berpunca daripada kecuaian atau salah laku kami.
        </p>
      </LegalSection>

      <LegalSection id="perubahan-undang-undang" title="11. Perubahan dan Undang-Undang">
        <p>
          Kami boleh mengubah terma ini apabila perlu. Versi terkini akan diterbitkan di halaman ini bersama tarikh kemas kini. Terma ini ditadbir oleh undang-undang Malaysia dan sebarang pertikaian hendaklah dirujuk kepada bidang kuasa yang berkaitan di Malaysia.
        </p>
      </LegalSection>

      <LegalSection id="hubungi" title="12. Hubungi Kami">
        <p>Untuk pertanyaan tentang tempahan atau terma ini:</p>
        <ul className={bulletClass}>
          <li>E-mel: <a href={`mailto:${app.admin_email}`} className="font-semibold text-brand-600 hover:text-brand-700">{app.admin_email}</a></li>
          <li>WhatsApp: <a href="https://wa.me/601169409606" className="font-semibold text-brand-600 hover:text-brand-700">011-69409606</a></li>
        </ul>
      </LegalSection>
    </LegalDocument>
  );
}
