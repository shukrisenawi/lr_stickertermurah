import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head } from '@inertiajs/react';

export default function MemberDashboard() {
  return (
    <MemberLayout>
      <Head title="Dashboard" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="text-center">
          <h1 className="text-3xl font-bold text-slate-900">Dashboard Ahli</h1>
          <p className="mt-2 text-slate-500">Selamat datang ke panel ahli StickerTermurah.</p>
        </div>
      </div>
    </MemberLayout>
  );
}
