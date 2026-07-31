import MemberLayout from '@/Components/Layouts/MemberLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { ArrowRight, Images, MessageCircle, Package, Star } from 'lucide-react';

export default function MemberDashboard() {
  const { auth } = usePage<PageProps>().props;

  const quickActions = [
    {
      label: 'Pilih Design',
      copy: 'Semak galeri 35+ design eksklusif',
      icon: Images,
      href: '/#pilih-design',
      tint: 'bg-brand-50 text-brand-600',
    },
    {
      label: 'Order Saya',
      copy: 'Semak status tempahan anda',
      icon: Package,
      href: route('member.orders.index'),
      tint: 'bg-blue-50 text-blue-600',
    },
    {
      label: 'Testimoni',
      copy: 'Kongsi pengalaman anda',
      icon: Star,
      href: route('member.testimonials.index'),
      tint: 'bg-amber-50 text-amber-600',
    },
    {
      label: 'WhatsApp Kami',
      copy: 'Respon pantas dari kami',
      icon: MessageCircle,
      href: 'https://wa.me/601169409606',
      tint: 'bg-emerald-50 text-emerald-600',
      external: true,
    },
  ];

  return (
    <MemberLayout>
      <Head title="Dashboard" />
      <div className="mx-auto max-w-[1280px] px-4 py-8 lg:px-8">
        {/* Selamat Datang */}
        <div className="relative overflow-hidden rounded-[2rem] bg-brand-900 px-6 py-8 lg:px-10">
          <img
            src="/images/showcase/sticker-01.webp"
            alt=""
            aria-hidden="true"
            className="absolute -right-8 -top-8 w-36 rotate-[12deg] rounded-full opacity-15"
          />
          <img
            src="/images/showcase/sticker-26.webp"
            alt=""
            aria-hidden="true"
            className="absolute -bottom-10 right-[22%] w-32 rotate-[-8deg] rounded-full opacity-10"
          />
          <div className="relative z-10">
            <h1 className="font-display text-2xl font-bold text-white lg:text-3xl">
              Selamat Datang, {auth.user?.name?.split(' ')[0]}!
            </h1>
            <p className="mt-1.5 max-w-md text-sm leading-relaxed text-brand-100">
              Sedia untuk tempah sticker baharu? Pilih design kegemaran anda dan WhatsApp kami hari ini.
            </p>
            <div className="mt-5 flex flex-wrap gap-2.5">
              <Link
                href="/#pilih-design"
                className="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-xs font-bold text-brand-800 shadow-lg transition hover:bg-brand-50 active:scale-[0.98]"
              >
                Pilih Design
                <ArrowRight className="h-3.5 w-3.5" />
              </Link>
              <a
                href="https://wa.me/601169409606"
                target="_blank"
                rel="noreferrer"
                className="inline-flex items-center gap-2 rounded-full border-2 border-white/25 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-white/10 active:scale-[0.98]"
              >
                <MessageCircle className="h-3.5 w-3.5" />
                WhatsApp Kami
              </a>
            </div>
          </div>
        </div>

        {/* Tindakan Pantas */}
        <div className="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
          {quickActions.map((action) => {
            const inner = (
              <>
                <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ${action.tint}`}>
                  <action.icon className="h-5 w-5" />
                </div>
                <div className="min-w-0 flex-1">
                  <p className="truncate font-display text-sm font-bold text-slate-900">{action.label}</p>
                  <p className="truncate text-xs text-slate-500">{action.copy}</p>
                </div>
                <ArrowRight className="h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-brand-500" />
              </>
            );

            return action.external ? (
              <a
                key={action.label}
                href={action.href}
                target="_blank"
                rel="noreferrer"
                className="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md"
              >
                {inner}
              </a>
            ) : (
              <Link
                key={action.label}
                href={action.href}
                className="group flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md"
              >
                {inner}
              </Link>
            );
          })}
        </div>
      </div>
    </MemberLayout>
  );
}
