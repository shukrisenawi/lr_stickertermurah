import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head } from '@inertiajs/react';

export default function LookupOrder() {
  return (
    <FrontendLayout>
      <Head title="Semak Status Tempahan Sticker" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="mx-auto max-w-lg text-center">
          <h1 className="text-3xl font-bold text-slate-900">Semak Status Order</h1>
          <p className="mt-2 text-slate-500">Masukkan nombor order untuk menyemak status.</p>
        </div>
      </div>
    </FrontendLayout>
  );
}
