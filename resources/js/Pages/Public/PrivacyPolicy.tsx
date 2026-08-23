import LegalDocument, { LegalSection } from '@/Components/LegalDocument';
import { usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';

const bulletClass = 'list-disc space-y-2 pl-5';

export default function PrivacyPolicy() {
  const { app } = usePage<PageProps>().props;

  return (
    <LegalDocument
      title="Polisi Privasi"
      eyebrow="Privasi & Data"
      description="Polisi ini menerangkan cara StickerTermurah mengumpul, menggunakan dan melindungi maklumat anda apabila anda menggunakan laman web dan perkhidmatan kami."
      updatedAt="19 Ogos 2026"
      icon="privacy"
    >
      <LegalSection id="pengenalan" title="1. Pengenalan">
        <p>
          StickerTermurah ("kami") menghormati privasi pelanggan, pelawat dan pengguna laman web ini. Polisi ini terpakai kepada laman web StickerTermurah, proses tempahan sticker, komunikasi sokongan pelanggan dan ciri Google Contacts yang digunakan oleh pentadbir yang diberi kuasa.
        </p>
        <p>
          Dengan menggunakan laman web ini, anda bersetuju dengan amalan yang diterangkan dalam polisi ini. Jika anda tidak bersetuju, sila hentikan penggunaan perkhidmatan kami.
        </p>
      </LegalSection>

      <LegalSection id="maklumat-dikumpul" title="2. Maklumat yang Kami Kumpul">
        <p>Kami mungkin menerima maklumat berikut apabila anda membuat tempahan, mendaftar akaun atau menghubungi kami:</p>
        <ul className={bulletClass}>
          <li>Nama, nombor telefon dan alamat e-mel.</li>
          <li>Alamat penghantaran, nama penerima dan maklumat hubungan penerima.</li>
          <li>Butiran tempahan, reka bentuk, saiz, kuantiti dan arahan cetakan.</li>
          <li>Fail atau gambar yang anda muat naik untuk tujuan reka bentuk dan tempahan.</li>
          <li>Maklumat pembayaran atau bukti bayaran yang dihantar kepada kami.</li>
          <li>Maklumat teknikal asas seperti alamat IP, sesi log masuk dan maklumat pelayar yang diperlukan untuk keselamatan laman.</li>
        </ul>
      </LegalSection>

      <LegalSection id="google-contacts" title="3. Google Contacts dan Data Google">
        <p>
          Ciri Google Contacts hanya digunakan oleh pentadbir yang diberi kuasa selepas kebenaran OAuth diberikan kepada akaun Google. Apabila ciri ini digunakan, kami boleh mengakses maklumat contact yang diperlukan seperti nama, nombor telefon, alamat e-mel, alamat dan pengenalan resource contact.
        </p>
        <p>Data tersebut digunakan secara terhad untuk:</p>
        <ul className={bulletClass}>
          <li>Memaparkan senarai contact dalam panel pentadbir.</li>
          <li>Menyemak nombor telefon pendua sebelum contact baharu disimpan.</li>
          <li>Menambah, mengemaskini atau memadam contact atas arahan pentadbir.</li>
        </ul>
        <p>
          Data Google tidak dijual, tidak digunakan untuk pengiklanan diperibadikan, tidak digunakan untuk menentukan kelayakan kredit dan tidak dipindahkan kepada pihak ketiga untuk tujuan yang tidak berkaitan dengan fungsi yang diminta. Penggunaan data Google mematuhi Google API Services User Data Policy, termasuk keperluan Limited Use.
        </p>
        <p>
          Data contact disimpan dalam cache pangkalan data tempatan untuk mempercepatkan paparan panel pentadbir. Cache disegerakkan dengan Google tidak lebih daripada sekali sehari, manakala tindakan tambah, kemaskini atau padam akan disegerakkan apabila diarahkan oleh pentadbir. Token OAuth disimpan secara terenkripsi dan digunakan hanya untuk menghubungi Google bagi pihak akaun yang telah memberikan kebenaran.
        </p>
      </LegalSection>

      <LegalSection id="tujuan" title="4. Tujuan Penggunaan Maklumat">
        <p>Maklumat digunakan untuk:</p>
        <ul className={bulletClass}>
          <li>Memproses sebut harga, tempahan, pembayaran dan penghantaran.</li>
          <li>Menghubungi anda tentang status tempahan atau pertanyaan sokongan.</li>
          <li>Menyediakan akaun pelanggan, sejarah tempahan dan ciri pengurusan contact.</li>
          <li>Mencegah penipuan, penyalahgunaan dan akses tanpa kebenaran.</li>
          <li>Memperbaiki operasi, pengalaman pengguna dan kualiti perkhidmatan kami.</li>
        </ul>
      </LegalSection>

      <LegalSection id="perkongsian" title="5. Perkongsian dengan Pihak Lain">
        <p>
          Kami hanya berkongsi maklumat apabila diperlukan untuk menyediakan perkhidmatan, contohnya dengan penyedia pembayaran, pihak kurier atau pembekal teknikal yang membantu operasi laman. Pihak tersebut hanya menerima maklumat yang diperlukan untuk melaksanakan tugasnya.
        </p>
        <p>
          Kami tidak menjual maklumat peribadi anda. Kami juga boleh mendedahkan maklumat jika diwajibkan oleh undang-undang, perintah mahkamah atau untuk melindungi keselamatan pengguna dan perkhidmatan kami.
        </p>
      </LegalSection>

      <LegalSection id="keselamatan-penyimpanan" title="6. Keselamatan dan Penyimpanan">
        <p>
          Kami mengambil langkah munasabah seperti kawalan akses, perlindungan sesi, penyulitan token dan pemisahan akses pentadbir untuk melindungi maklumat. Walau bagaimanapun, tiada kaedah penghantaran atau penyimpanan elektronik yang boleh dijamin selamat sepenuhnya.
        </p>
        <p>
          Maklumat disimpan selama diperlukan untuk memenuhi tujuan pengumpulan, menyelesaikan pertikaian, mematuhi rekod perniagaan dan memenuhi keperluan undang-undang. Anda boleh membatalkan kebenaran Google pada bila-bila masa melalui tetapan akaun Google atau dengan memutuskan sambungan dalam panel pentadbir.
        </p>
      </LegalSection>

      <LegalSection id="hak-anda" title="7. Hak Anda">
        <p>
          Anda boleh meminta semakan, pembetulan atau pemadaman maklumat peribadi yang kami simpan, tertakluk kepada keperluan rekod dan undang-undang yang terpakai. Untuk permintaan berkaitan data Google, anda juga boleh membatalkan akses melalui Google Account permissions.
        </p>
      </LegalSection>

      <LegalSection id="cookies" title="8. Cookies dan Sesi">
        <p>
          Laman ini menggunakan cookies dan storan sesi yang diperlukan untuk log masuk, keselamatan, penghantaran borang dan fungsi asas laman. Menyekat cookies tertentu mungkin menyebabkan sebahagian fungsi tidak dapat digunakan dengan sempurna.
        </p>
      </LegalSection>

      <LegalSection id="perubahan" title="9. Perubahan Polisi">
        <p>
          Kami boleh mengemas kini polisi ini dari semasa ke semasa untuk mencerminkan perubahan perkhidmatan, teknologi atau keperluan undang-undang. Tarikh kemas kini di bahagian atas akan berubah apabila pindaan dibuat. Penggunaan berterusan laman selepas pindaan bermaksud anda menerima polisi yang dikemas kini.
        </p>
      </LegalSection>

      <LegalSection id="hubungi" title="10. Hubungi Kami">
        <p>Jika anda mempunyai soalan atau permintaan berkaitan privasi, hubungi kami melalui:</p>
        <ul className={bulletClass}>
          <li>E-mel: <a href={`mailto:${app.admin_email}`} className="font-semibold text-brand-600 hover:text-brand-700">{app.admin_email}</a></li>
          <li>WhatsApp: <a href="https://wa.me/601169409606" className="font-semibold text-brand-600 hover:text-brand-700">011-69409606</a></li>
        </ul>
      </LegalSection>
    </LegalDocument>
  );
}
