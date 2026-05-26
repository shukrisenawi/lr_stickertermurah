import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head } from '@inertiajs/react';

export default function OrderThankYou({ order }: { order: Record<string, unknown> }) {
  return (
    <FrontendLayout>
      <Head title="Terima Kasih" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8 text-center">
        <h1 className="text-3xl font-bold text-slate-900">Terima Kasih!</h1>
        <p className="mt-2 text-slate-500">Order anda telah berjaya dihantar.</p>
        <p className="mt-4 text-lg font-semibold text-brand-600">No. Order: {order.order_no as string}</p>
      </div>
    </FrontendLayout>
  );
}
