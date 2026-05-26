import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import { Head } from '@inertiajs/react';

export default function MemberRegister() {
  return (
    <FrontendLayout>
      <Head title="Daftar Ahli" />
      <div className="mx-auto max-w-[1280px] px-4 py-12 lg:px-8">
        <div className="mx-auto max-w-md">
          <h1 className="text-2xl font-bold text-slate-900 text-center">Daftar Ahli</h1>
        </div>
      </div>
    </FrontendLayout>
  );
}
