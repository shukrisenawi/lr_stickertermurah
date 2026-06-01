import { Head } from '@inertiajs/react';

export default function UnderConstruction() {
  return (
    <>
      <Head title="Under Construction" />
      <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4">
        <div className="w-full max-w-md text-center">
          <div className="mb-8 flex justify-center">
            <img src="/images/logo-baru.png" alt="Logo" className="h-[200px] w-[200px] object-contain" />
          </div>

          <h1 className="text-4xl font-bold tracking-tight text-white">
            Sedang Dalam Pembinaan
          </h1>
          <p className="mt-4 text-base leading-relaxed text-slate-400">
            Laman web ini sedang dinaik taraf. Sila datang semula sebentar lagi.
            Buat masa ini, akses adalah terhad kepada pentadbir sahaja.
          </p>

          <div className="mt-10">
            <a
              href="/admin/login"
              className="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/25"
            >
              <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Login Admin
            </a>
          </div>

          <p className="mt-6 text-xs text-slate-600">
            &copy; {new Date().getFullYear()} StickerTermurah. All rights reserved.
          </p>
        </div>
      </div>
    </>
  );
}
