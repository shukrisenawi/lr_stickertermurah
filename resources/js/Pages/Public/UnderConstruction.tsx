import { Head } from '@inertiajs/react';

export default function UnderConstruction() {
  return (
    <>
      <Head title="Under Construction" />
      <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4">
        <div className="w-full max-w-md text-center">
          <div className="mb-8 flex justify-center">
            <div className="relative">
              <div className="h-24 w-24 animate-pulse rounded-3xl bg-brand-500/20 flex items-center justify-center">
                <svg className="h-12 w-12 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                  <path strokeLinecap="round" strokeLinejoin="round" d="M11.42 15.17l-5.46-5.46a2 2 0 010-2.83l2.83-2.83a2 2 0 012.83 0l5.46 5.46M13.17 11.42l5.46 5.46a2 2 0 010 2.83l-2.83 2.83a2 2 0 01-2.83 0l-5.46-5.46" />
                </svg>
              </div>
              <div className="absolute -right-2 -top-2 h-6 w-6 animate-bounce rounded-full bg-amber-400 flex items-center justify-center">
                <span className="text-[10px] font-bold text-slate-900">!</span>
              </div>
            </div>
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
