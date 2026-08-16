import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import PublicHeader from '@/Components/PublicHeader';
import ResponsiveDesignImage from '@/Components/ResponsiveDesignImage';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';
import {
  Check,
  ChevronRight,
  AlertCircle,
  FolderKanban,
  Image as ImageIcon,
  Info,
  LoaderCircle,
  MapPin,
  MessageCircle,
  RotateCcw,
  Search,
  ShoppingCart,
  X,
} from 'lucide-react';

interface DesignOption {
  id: number;
  name: string;
  image_url: string | null;
  mobile_image_url: string | null;
  category: string | null;
  tags: string[];
}

interface DesignsApiResponse {
  data: Array<{
    id: number;
    name: string;
    image: string | null;
    mobile_image: string | null;
    category: string | null;
    tags: string[];
  }>;
  meta: {
    offset: number;
    limit: number;
    total: number;
    has_more: boolean;
  };
}

interface RepeatOrderItem {
  id: number;
  sticker_design_id: number | null;
  custom_design_description: string | null;
  sticker_size_id: number | null;
  requested_size: string | null;
  quantity: number;
  cut_type: string | null;
}

interface RepeatOrder {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  items: RepeatOrderItem[];
}

interface ProjectOption {
  id: number;
  title: string;
  notes: string | null;
  preview_url: string | null;
  created_at: string;
}

interface OrderFormProps extends PageProps {
  initialDesign: DesignOption | null;
  initialProject: ProjectOption | null;
  sizes: Array<{
    id: number;
    name: string;
    width_cm: number;
    height_cm: number;
    qty_per_a3: number | null;
    is_active: boolean;
  }>;
  previousDesigns: DesignOption[];
  previousProjects: ProjectOption[];
  catalogTags: string[];
  priceSettings: Array<{
    id: number;
    sticker_type: string;
    qty_from: number;
    qty_to: number | null;
    price_per_a3: number | string;
  }>;
  paymentSettings: {
    bank_name: string;
    bank_account_no: string;
    bank_account_name: string;
    deposit_amount: number;
  } | null;
  repeatOrder: RepeatOrder | null;
}

export default function OrderForm() {
  const { initialDesign, initialProject, previousDesigns, previousProjects, catalogTags, sizes, priceSettings, paymentSettings, repeatOrder, auth, app, flash } = usePage<OrderFormProps>().props;

  const repeatItem = repeatOrder?.items?.[0] ?? null;
  const initialDesignId = initialDesign?.id ?? null;
  const configuredWhatsappPhone = app.whatsapp_phone.replace(/\D/g, '');
  const whatsappPhone = configuredWhatsappPhone
    ? (configuredWhatsappPhone.startsWith('0') ? `60${configuredWhatsappPhone.slice(1)}` : configuredWhatsappPhone)
    : '601169409606';
  const whatsappLink = `https://wa.me/${whatsappPhone}?text=${encodeURIComponent('Assalamualaikum, saya perlukan bantuan untuk tempahan sticker.')}`;
  const defaultCustomerAddress = auth.customerAddresses.find((address) => address.is_default)
    ?? auth.customerAddresses[0]
    ?? null;

  const [selectedDesign, setSelectedDesign] = useState<number | 'custom' | 'project'>(
    initialProject ? 'project' : initialDesignId ? initialDesignId : 'custom'
  );
  const [selectedDesignInfo, setSelectedDesignInfo] = useState<DesignOption | null>(initialDesign);
  const [selectedProject, setSelectedProject] = useState<ProjectOption | null>(initialProject);
  const [customDesc, setCustomDesc] = useState(repeatItem?.custom_design_description ?? '');
  const [selectedSize, setSelectedSize] = useState<number | null>(repeatItem?.sticker_size_id ?? null);
  const [quantity, setQuantity] = useState(repeatItem?.quantity ?? 100);
  const [requestCustomSize, setRequestCustomSize] = useState(!!repeatItem?.requested_size && !repeatItem?.sticker_size_id);
  const [customSizeDesc, setCustomSizeDesc] = useState(repeatItem?.requested_size ?? '');
  const [cutType, setCutType] = useState<'standard' | 'die-cut'>(
    repeatItem?.cut_type === 'die-cut' ? 'die-cut' : 'standard'
  );
  const [designPreview, setDesignPreview] = useState<string | null>(null);
  const [isDesignPickerOpen, setIsDesignPickerOpen] = useState(false);
  const [catalogPreview, setCatalogPreview] = useState<DesignOption | null>(null);
  const [isMobileViewport, setIsMobileViewport] = useState(() =>
    typeof window !== 'undefined' && window.matchMedia('(max-width: 767px)').matches
  );
  const [catalogDesigns, setCatalogDesigns] = useState<DesignOption[]>([]);
  const [catalogSearch, setCatalogSearch] = useState('');
  const [catalogTag, setCatalogTag] = useState<string | null>(null);
  const [catalogOffset, setCatalogOffset] = useState(0);
  const [catalogTotal, setCatalogTotal] = useState(0);
  const [catalogLoading, setCatalogLoading] = useState(false);
  const [catalogLoaded, setCatalogLoaded] = useState(false);
  const [catalogError, setCatalogError] = useState(false);
  const [submitErrorMessages, setSubmitErrorMessages] = useState<string[]>([]);
  const [accountTab, setAccountTab] = useState<'register' | 'login'>('register');
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(defaultCustomerAddress?.id ?? null);
  const [loginPhoneCustomized, setLoginPhoneCustomized] = useState(false);
  const [registerPasswordCustomized, setRegisterPasswordCustomized] = useState(false);
  const [loginPasswordCustomized, setLoginPasswordCustomized] = useState(false);
  const catalogAbortRef = useRef<AbortController | null>(null);

  const { data, setData, post, processing, errors } = useForm({
    design_id: initialProject ? null : initialDesignId,
    project_id: initialProject?.id ?? null,
    custom_description: repeatItem?.custom_design_description ?? '',
    order_note: '',
    size_id: repeatItem?.sticker_size_id ?? null,
    requested_size: repeatItem?.requested_size ?? '',
    quantity: repeatItem?.quantity ?? 100,
    cut_type: (repeatItem?.cut_type === 'die-cut' ? 'die-cut' : 'standard') as 'standard' | 'die-cut',
    customer_design_image: null as File | null,
    customer_name: repeatOrder?.customer_name ?? auth.user?.name ?? '',
    customer_phone: repeatOrder?.customer_phone ?? auth.user?.no_tel ?? '',
    customer_address: repeatOrder?.customer_address ?? defaultCustomerAddress?.address ?? '',
    repeat_from_order_id: repeatOrder?.id ?? null,
  });

  const {
    data: registerData,
    setData: setRegisterData,
    post: postRegister,
    processing: registerProcessing,
    errors: registerErrors,
    transform: transformRegister,
  } = useForm({
    no_tel: repeatOrder?.customer_phone ?? auth.user?.no_tel ?? '',
    delivery_phone: repeatOrder?.customer_phone ?? '',
    recipient_name: repeatOrder?.customer_name ?? auth.user?.name ?? '',
    address: repeatOrder?.customer_address ?? defaultCustomerAddress?.address ?? '',
    mode: 'new' as 'matched' | 'new',
    password: repeatOrder?.customer_phone ?? '',
    password_confirmation: repeatOrder?.customer_phone ?? '',
    from_order: true,
  });

  const {
    data: loginData,
    setData: setLoginData,
    post: postLogin,
    processing: loginProcessing,
    errors: loginErrors,
    transform: transformLogin,
    clearErrors: clearLoginErrors,
  } = useForm({
    login: repeatOrder?.customer_phone ?? auth.user?.no_tel ?? '',
    password: repeatOrder?.customer_phone ?? auth.user?.no_tel ?? '',
    remember: false,
    from_order: true,
  });

  const registerErrorMessages = [
    registerErrors.no_tel,
    registerErrors.delivery_phone,
    registerErrors.recipient_name,
    registerErrors.address,
    registerErrors.password,
    registerErrors.password_confirmation,
  ].filter((message): message is string => Boolean(message));

  const handleCustomerPhoneChange = (phone: string) => {
    setData('customer_phone', phone);
    setRegisterData('delivery_phone', phone);

    if (!loginPhoneCustomized) {
      setRegisterData('no_tel', phone);
      if (!registerPasswordCustomized) {
        setRegisterData('password', phone);
        setRegisterData('password_confirmation', phone);
      }
    }

    setLoginData('login', phone);

    if (!loginPasswordCustomized) setLoginData('password', phone);
  };

  const handleRegisterPhoneChange = (phone: string) => {
    setLoginPhoneCustomized(phone.trim().length > 0);
    setRegisterData('no_tel', phone);

    if (!registerPasswordCustomized) {
      const defaultPassword = phone || data.customer_phone;
      setRegisterData('password', defaultPassword);
      setRegisterData('password_confirmation', defaultPassword);
    }
  };

  const handleRegisterPasswordChange = (password: string) => {
    setRegisterPasswordCustomized(password !== (registerData.no_tel || data.customer_phone));
    setRegisterData('password', password);
    setRegisterData('password_confirmation', password);
  };

  const handleRegisterPasswordConfirmationChange = (password: string) => {
    setRegisterData('password_confirmation', password);
  };

  const handleRegisterNameChange = (name: string) => {
    setRegisterData('recipient_name', name);
    setData('customer_name', name);
  };

  const handleRegisterAddressChange = (address: string) => {
    setRegisterData('address', address);
    setData('customer_address', address);
  };

  const handleLoginPasswordChange = (password: string) => {
    setLoginPasswordCustomized(password !== data.customer_phone);
    setLoginData('password', password);
  };

  const handleRegister = () => {
    transformRegister((form) => ({
      ...form,
      no_tel: form.no_tel.trim() || data.customer_phone,
      delivery_phone: data.customer_phone,
      recipient_name: data.customer_name,
      address: data.customer_address,
      from_order: true,
    }));
    postRegister(route('member.register.store'), {
      preserveScroll: true,
    });
  };

  const openLoginTab = () => {
    clearLoginErrors();
    setLoginData('login', data.customer_phone);
    if (!loginPasswordCustomized) setLoginData('password', data.customer_phone);
    setAccountTab('login');
  };

  const handleLoginSubmit = () => {
    transformLogin((form) => ({ ...form, from_order: true }));
    postLogin(route('member.login.attempt'), {
      preserveScroll: true,
    });
  };

  const handleAddressSelect = (addressId: number) => {
    const address = auth.customerAddresses.find((item) => item.id === addressId);
    if (!address) return;

    setSelectedAddressId(address.id);
    setData('customer_name', address.recipient_name || auth.user?.name || '');
    handleCustomerPhoneChange(address.no_hp ?? auth.user?.no_tel ?? '');
    setData('customer_address', address.address);
  };

  const selectedCustomerAddress = auth.customerAddresses.find((address) => address.id === selectedAddressId)
    ?? defaultCustomerAddress;

  const loadCatalog = async (nextOffset: number, reset: boolean, search: string, tag = catalogTag) => {
    catalogAbortRef.current?.abort();
    const controller = new AbortController();
    catalogAbortRef.current = controller;
    setCatalogLoading(true);
    setCatalogError(false);

    try {
      const url = new URL('/api/designs', window.location.origin);
      url.searchParams.set('offset', String(nextOffset));
      url.searchParams.set('limit', '12');
      if (search) url.searchParams.set('search', search);
      if (tag) url.searchParams.set('tag', tag);

      const response = await fetch(url.toString(), {
        signal: controller.signal,
        headers: { Accept: 'application/json' },
      });

      if (!response.ok) throw new Error('Gagal memuatkan katalog design.');

      const payload = (await response.json()) as DesignsApiResponse;
      const nextDesigns = payload.data.map((design) => ({
        id: design.id,
        name: design.name,
        image_url: design.image,
        mobile_image_url: design.mobile_image ?? design.image,
        category: design.category,
        tags: design.tags ?? [],
      }));

      setCatalogDesigns((current) => (reset ? nextDesigns : [...current, ...nextDesigns]));
      setCatalogOffset(payload.meta.offset + payload.data.length);
      setCatalogTotal(payload.meta.total);
      setCatalogLoaded(true);
    } catch (error) {
      if ((error as Error).name !== 'AbortError') setCatalogError(true);
    } finally {
      if (catalogAbortRef.current === controller) setCatalogLoading(false);
    }
  };

  const openDesignPicker = () => {
    setIsDesignPickerOpen(true);
    setCatalogPreview(null);
    if (!catalogLoaded && !catalogLoading) void loadCatalog(0, true, '');
  };

  const closeDesignPicker = () => {
    catalogAbortRef.current?.abort();
    setCatalogPreview(null);
    setIsDesignPickerOpen(false);
  };

  const chooseDesign = (design: DesignOption) => {
    setSelectedDesign(design.id);
    setSelectedDesignInfo(design);
    setSelectedProject(null);
    setData('design_id', design.id);
    setData('project_id', null);
    setData('customer_design_image', null);
    setDesignPreview(null);
    closeDesignPicker();
  };

  const chooseProject = (project: ProjectOption) => {
    setSelectedDesign('project');
    setSelectedDesignInfo(null);
    setSelectedProject(project);
    setData('design_id', null);
    setData('project_id', project.id);
    setData('customer_design_image', null);
    setDesignPreview(null);
    closeDesignPicker();
  };

  const handleCatalogDesignClick = (design: DesignOption) => {
    if (isMobileViewport && design.image_url) {
      setCatalogPreview(design);
      return;
    }

    chooseDesign(design);
  };

  const chooseCustomDesign = () => {
    setSelectedDesign('custom');
    setSelectedDesignInfo(null);
    setSelectedProject(null);
    setData('design_id', null);
    setData('project_id', null);
  };

  const handleCatalogSearch = () => {
    void loadCatalog(0, true, catalogSearch.trim(), catalogTag);
  };

  const handleCatalogTag = (tag: string | null) => {
    setCatalogTag(tag);
    void loadCatalog(0, true, catalogSearch.trim(), tag);
  };

  const hasMoreCatalogDesigns = catalogOffset < catalogTotal;

  useEffect(() => {
    const mediaQuery = window.matchMedia('(max-width: 767px)');
    const handleViewportChange = (event: MediaQueryListEvent) => setIsMobileViewport(event.matches);

    setIsMobileViewport(mediaQuery.matches);
    mediaQuery.addEventListener('change', handleViewportChange);

    return () => mediaQuery.removeEventListener('change', handleViewportChange);
  }, []);

  useEffect(() => {
    if (!isDesignPickerOpen) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        if (catalogPreview) {
          setCatalogPreview(null);
          return;
        }

        catalogAbortRef.current?.abort();
        setIsDesignPickerOpen(false);
      }
    };
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [catalogPreview, isDesignPickerOpen]);

  useEffect(() => () => catalogAbortRef.current?.abort(), []);

  useEffect(() => {
    if (flash.error) setSubmitErrorMessages([flash.error]);
  }, [flash.error]);

  useEffect(() => {
    if (submitErrorMessages.length === 0) return;

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setSubmitErrorMessages([]);
    };
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [submitErrorMessages.length]);

  const selectedSizeObj = useMemo(() => sizes.find((s) => s.id === selectedSize) ?? null, [sizes, selectedSize]);
  const isDieCutTooSmall = cutType === 'die-cut' && selectedSizeObj && Math.max(selectedSizeObj.width_cm, selectedSizeObj.height_cm) < 5;

  const priceCalculation = useMemo(() => {
    if (requestCustomSize || !selectedSize || !selectedSizeObj) return null;

    const qtyPerA3 = selectedSizeObj.qty_per_a3;
    if (!qtyPerA3) return null;

    const a3Sheets = Math.ceil(quantity / qtyPerA3);

    const match = priceSettings.find(
      (ps) => a3Sheets >= ps.qty_from && (ps.qty_to === null || a3Sheets <= ps.qty_to)
    );

    if (!match) return null;

    const pricePerA3 = Number(match.price_per_a3);
    if (!Number.isFinite(pricePerA3)) return null;

    return {
      a3Sheets,
      pricePerA3,
      total: a3Sheets * pricePerA3,
    };
  }, [selectedSize, selectedSizeObj, quantity, priceSettings, requestCustomSize]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitErrorMessages([]);
    post(route('orders.store'), {
      onError: (validationErrors) => {
        const messages = Object.values(validationErrors).filter(
          (message): message is string => typeof message === 'string' && message.length > 0,
        );
        setSubmitErrorMessages(messages.length > 0 ? Array.from(new Set(messages)) : ['Sila semak maklumat tempahan dan cuba lagi.']);
      },
    });
  };

  return (
    <FrontendLayout hideNavbar>
      <Head title="Tempah Sticker" />
      <PublicHeader active="design" />
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-[1280px] px-4 py-10 lg:px-8">
          <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">Tempah Sticker</h1>
          <p className="mt-2 text-sm text-slate-500">Pilih design, saiz & kuantiti sticker anda.</p>

          <div className="mt-5 flex flex-col gap-4 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div className="flex items-start gap-3">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <MessageCircle className="h-5 w-5" />
              </div>
              <div>
                <p className="text-sm font-bold text-emerald-900">Tak pasti cara nak order?</p>
                <p className="mt-1 text-xs leading-relaxed text-emerald-800">Nak tanya apa-apa atau perlukan bantuan, boleh terus WhatsApp admin untuk tempahan.</p>
              </div>
            </div>
            <a
              href={whatsappLink}
              target="_blank"
              rel="noreferrer"
              aria-label="WhatsApp admin untuk bantuan tempahan"
              className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600 active:scale-[0.98] sm:w-auto sm:shrink-0"
            >
              <MessageCircle className="h-4 w-4" />
              WhatsApp Admin
            </a>
          </div>

          {repeatOrder && (
            <div className="mt-4 flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-3.5">
              <RotateCcw className="h-5 w-5 shrink-0 text-brand-600" />
              <div>
                <p className="text-sm font-bold text-brand-900">Ulang Tempahan {repeatOrder.order_no}</p>
                <p className="text-xs text-brand-700">Butangan dari tempahan lama telah diisi. Sila semak dan ubah jika perlu.</p>
              </div>
            </div>
          )}

          <form onSubmit={handleSubmit} className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            {/* Left: Design Selection */}
            <div className="lg:col-span-2 space-y-8">
              {/* Step 1: Design */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">1</div>
                  <h2 className="text-lg font-bold text-slate-900">Pilih Design</h2>
                </div>

                <div className="mt-4 space-y-3">
                  <button
                    type="button"
                    onClick={openDesignPicker}
                    className="flex w-full items-center gap-4 rounded-2xl border-2 border-slate-200 bg-white p-3 text-left transition hover:border-brand-300 hover:bg-brand-50/40"
                  >
                    <div className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white">
                      {selectedProject?.preview_url ? (
                        <img
                          src={selectedProject.preview_url}
                          alt=""
                          className="h-full w-full object-contain"
                        />
                      ) : selectedDesignInfo?.image_url ? (
                        <ResponsiveDesignImage
                          src={selectedDesignInfo.image_url}
                          mobileSrc={selectedDesignInfo.mobile_image_url}
                          alt=""
                          className="h-full w-full object-contain"
                        />
                      ) : selectedDesign === 'project' ? (
                        <FolderKanban className="h-7 w-7 text-brand-300" />
                      ) : (
                        <ImageIcon className="h-7 w-7 text-slate-300" />
                      )}
                    </div>
                    <div className="min-w-0 flex-1">
                      <p className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Design dipilih</p>
                      <p className="mt-1 truncate text-sm font-bold text-slate-900">
                        {selectedDesign === 'custom'
                          ? 'Pilih Design'
                          : selectedDesign === 'project'
                            ? selectedProject?.title ?? 'Design project'
                            : selectedDesignInfo?.name ?? 'Pilih daripada katalog'}
                      </p>
                      <p className="mt-0.5 text-xs text-slate-500">
                        {selectedDesign === 'project' ? 'Design yang pernah dibuat' : selectedDesignInfo?.category ?? 'Katalog design sticker'}
                      </p>
                    </div>
                    <span className="shrink-0 rounded-xl bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700">Pilih Design</span>
                  </button>

                  <button
                    type="button"
                    onClick={chooseCustomDesign}
                    className={`flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left transition ${
                      selectedDesign === 'custom'
                        ? 'border-brand-300 bg-brand-50'
                        : 'border-slate-200 bg-slate-50 hover:border-brand-200'
                    }`}
                  >
                    <span className="flex items-center gap-2 text-sm font-semibold text-slate-700">
                      <ImageIcon className="h-4 w-4 text-slate-400" />
                      Saya nak custom design
                    </span>
                    {selectedDesign === 'custom' && <Check className="h-4 w-4 text-brand-600" />}
                  </button>
                </div>
                <p className="mt-3 text-xs text-slate-400">Katalog dibuka apabila diperlukan. Imej dimuatkan secara berperingkat.</p>

                {selectedDesign === 'custom' && (
                  <div className="mt-4">
                    <label htmlFor="custom-desc" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Keterangan Design</label>
                    <textarea
                      id="custom-desc"
                      value={customDesc}
                      onChange={(e) => { setCustomDesc(e.target.value); setData('custom_description', e.target.value); }}
                      rows={3}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Terangkan design yang anda mahukan..."
                    />
                  </div>
                )}

                 <div className="mt-5">
                   <label htmlFor="design-upload" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Hantar Design Sendiri (Pilihan)</label>
                   <div className="mt-1 flex items-center gap-4">
                     {designPreview ? (
                       <img src={designPreview} alt="Design preview" className="h-20 w-20 rounded-xl border border-slate-200 bg-white object-contain" />
                     ) : (
                       <div className="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-100">
                         <ImageIcon className="h-8 w-8 text-slate-300" />
                       </div>
                     )}
                     <div>
                       <input
                         id="design-upload"
                         type="file"
                         accept="image/*,application/pdf"
                         onChange={(e) => {
                           const file = e.target.files?.[0] ?? null;
                           setData('customer_design_image', file);
                           if (file) {
                             setSelectedDesign('custom');
                             setSelectedDesignInfo(null);
                             setSelectedProject(null);
                             setData('design_id', null);
                             setData('project_id', null);
                           }
                           if (file) {
                             setDesignPreview(URL.createObjectURL(file));
                           } else {
                             setDesignPreview(null);
                           }
                         }}
                         className="text-sm"
                       />
                       <p className="mt-1 text-xs text-slate-400">JPG, PNG, PDF. Maks 10MB.</p>
                     </div>
                   </div>
                 </div>

                 <div className="mt-5">
                   <label htmlFor="order-note" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Catatan (Pilihan)</label>
                   <textarea
                     id="order-note"
                     value={data.order_note}
                     onChange={(e) => setData('order_note', e.target.value)}
                     rows={3}
                     className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                     placeholder="Tambah catatan untuk order anda..."
                   />
                   {errors.order_note && <p className="mt-1 text-xs text-rose-600">{errors.order_note}</p>}
                 </div>
               </section>

              {/* Step 2: Size & Quantity */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">2</div>
                  <h2 className="text-lg font-bold text-slate-900">Saiz & Kuantiti</h2>
                </div>

                <div className="mt-4 space-y-4">
                  <div className="flex items-center gap-3">
                    <input
                      id="custom-size"
                      type="checkbox"
                      checked={requestCustomSize}
                      onChange={(e) => { setRequestCustomSize(e.target.checked); setSelectedSize(null); setData('size_id', null); }}
                      className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                    />
                    <label htmlFor="custom-size" className="text-sm font-medium text-slate-700">
                      Tiada saiz yang sesuai? Request saiz sendiri
                    </label>
                  </div>

                  {!requestCustomSize ? (
                    <>
                      <div className="md:hidden">
                        <label htmlFor="sticker-size" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                          Pilih Saiz
                        </label>
                        <select
                          id="sticker-size"
                          value={selectedSize ?? ''}
                          onChange={(e) => {
                            const sizeId = e.target.value ? Number(e.target.value) : null;
                            setSelectedSize(sizeId);
                            setData('size_id', sizeId);
                          }}
                          className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        >
                          <option value="">Pilih saiz sticker...</option>
                          {sizes.map((size) => (
                            <option key={size.id} value={size.id}>
                              {size.name} ({size.width_cm}cm x {size.height_cm}cm)
                            </option>
                          ))}
                        </select>
                      </div>

                      <div className="hidden grid-cols-2 gap-3 sm:grid-cols-3 md:grid">
                        {sizes.map((size) => (
                          <button
                            key={size.id}
                            type="button"
                            onClick={() => { setSelectedSize(size.id); setData('size_id', size.id); }}
                            className={`rounded-xl border-2 px-4 py-3 text-left transition ${
                              selectedSize === size.id
                                ? 'border-brand-600 bg-brand-50'
                                : 'border-slate-200 bg-white hover:border-brand-200'
                            }`}
                          >
                            <p className="text-sm font-bold text-slate-900">{size.name}</p>
                            <p className="text-xs text-slate-500">{size.width_cm}cm x {size.height_cm}cm</p>
                            {size.qty_per_a3 && (
                              <p className="mt-0.5 text-xs text-slate-400">{size.qty_per_a3} sticker/A3</p>
                            )}
                          </button>
                        ))}
                      </div>
                    </>
                  ) : (
                    <div>
                      <label htmlFor="req-size" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Saiz & Kuantiti yang Diinginkan</label>
                      <textarea
                        id="req-size"
                        value={customSizeDesc}
                        onChange={(e) => { setCustomSizeDesc(e.target.value); setData('requested_size', e.target.value); }}
                        rows={2}
                        className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        placeholder="cth: 5cm x 5cm, 500 pcs"
                      />
                    </div>
                  )}

                  <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Jenis Potong</p>
                    <div className="mt-2 grid grid-cols-2 gap-3">
                      <button
                        type="button"
                        onClick={() => { setCutType('standard'); setData('cut_type', 'standard'); }}
                        className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                          cutType === 'standard'
                            ? 'border-brand-600 bg-brand-50'
                            : 'border-slate-200 bg-white hover:border-brand-200'
                        }`}
                      >
                        <p className="text-sm font-bold text-slate-900">Standard</p>
                        <p className="text-xs text-slate-500">Potong segi empat</p>
                      </button>
                      <button
                        type="button"
                        onClick={() => { setCutType('die-cut'); setData('cut_type', 'die-cut'); }}
                        className={`rounded-xl border-2 px-4 py-3 text-center transition ${
                          cutType === 'die-cut'
                            ? 'border-brand-600 bg-brand-50'
                            : 'border-slate-200 bg-white hover:border-brand-200'
                        }`}
                      >
                        <p className="text-sm font-bold text-slate-900">Potong Ikon / Bentuk</p>
                        <p className="text-xs text-slate-500">Mengikut bentuk sticker</p>
                      </button>
                    </div>
                    {isDieCutTooSmall && (
                      <p className="mt-2 flex items-start gap-1.5 text-xs text-rose-600">
                        <Info className="h-3.5 w-3.5 shrink-0" />
                        Potong ikut bentuk hanya boleh untuk saiz 5cm ke atas.
                      </p>
                    )}
                  </div>

                  <div>
                    <label htmlFor="qty" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Kuantiti</label>
                    <input
                      id="qty"
                      type="number"
                      min={1}
                      value={quantity}
                      onChange={(e) => { const q = parseInt(e.target.value) || 1; setQuantity(q); setData('quantity', q); }}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                    />
                    {selectedSizeObj && selectedSizeObj.qty_per_a3 && (
                      <p className="mt-1 text-xs text-slate-500">
                        ~{Math.ceil(quantity / selectedSizeObj.qty_per_a3)} helai A3
                      </p>
                    )}
                  </div>
                </div>
              </section>

              {/* Step 3: Member Account */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">3</div>
                  <h2 className="text-lg font-bold text-slate-900">{auth.user ? 'Alamat Penghantaran' : 'Create Akaun'}</h2>
                </div>

                {!auth.user && (
                  <div className="mt-4 flex rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Pilihan akaun">
                    <button
                      type="button"
                      role="tab"
                      aria-selected={accountTab === 'register'}
                      onClick={() => setAccountTab('register')}
                      className={`flex-1 rounded-lg px-3 py-2.5 text-sm font-bold transition ${
                        accountTab === 'register' ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'
                      }`}
                    >
                      Create Akaun
                    </button>
                    <button
                      type="button"
                      role="tab"
                      aria-selected={accountTab === 'login'}
                      onClick={openLoginTab}
                      className={`flex-1 rounded-lg px-3 py-2.5 text-sm font-bold transition ${
                        accountTab === 'login' ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'
                      }`}
                    >
                      Login
                    </button>
                  </div>
                )}

                {auth.user ? (
                  <div className="mt-4">
                    {auth.customerAddresses.length > 0 ? (
                      <div className="rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                        <div className="flex items-center gap-2">
                          <MapPin className="h-4 w-4 text-brand-600" />
                          <p className="text-sm font-bold text-brand-900">Alamat penghantaran</p>
                        </div>

                        {auth.customerAddresses.length > 1 && (
                          <div className="mt-3">
                            <label htmlFor="account-address" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Pilih alamat</label>
                            <select
                              id="account-address"
                              value={selectedCustomerAddress?.id ?? ''}
                              onChange={(e) => handleAddressSelect(Number(e.target.value))}
                              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                            >
                              {auth.customerAddresses.map((address) => (
                                <option key={address.id} value={address.id}>
                                  {address.recipient_name} - {address.address}
                                </option>
                              ))}
                            </select>
                          </div>
                        )}

                        {selectedCustomerAddress && (
                          <div className="mt-3 rounded-xl border border-brand-100 bg-white p-3 text-sm text-slate-700">
                            <p className="font-bold text-slate-900">{selectedCustomerAddress.recipient_name}</p>
                            <p className="mt-1 text-xs text-slate-500">{selectedCustomerAddress.no_hp ?? auth.user.no_tel ?? '-'}</p>
                            <p className="mt-2 text-sm leading-relaxed">{selectedCustomerAddress.address}</p>
                          </div>
                        )}
                      </div>
                    ) : (
                      <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Akaun ini belum mempunyai alamat penghantaran. Sila tambah alamat di profil sebelum checkout.
                      </div>
                    )}
                  </div>
                ) : accountTab === 'register' ? (
                  <div className="mt-4 space-y-4" role="tabpanel" aria-label="Create Akaun">
                    <p className="text-sm leading-relaxed text-slate-500">Isi maklumat di bawah untuk daftar sebagai user. Password awal akan menggunakan No. HP.</p>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                      <div>
                        <label htmlFor="account-name" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama</label>
                        <input
                          id="account-name"
                          type="text"
                          value={registerData.recipient_name}
                          onChange={(e) => handleRegisterNameChange(e.target.value)}
                          className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        />
                        {registerErrors.recipient_name && <p className="mt-1 text-xs text-rose-600">{registerErrors.recipient_name}</p>}
                      </div>
                      <div>
                        <label htmlFor="account-delivery-phone" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. HP Penghantaran</label>
                        <input
                          id="account-delivery-phone"
                          type="tel"
                          inputMode="numeric"
                          value={data.customer_phone}
                          onChange={(e) => handleCustomerPhoneChange(e.target.value)}
                          className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        />
                        {errors.customer_phone && <p className="mt-1 text-xs text-rose-600">{errors.customer_phone}</p>}
                        {registerErrors.delivery_phone && <p className="mt-1 text-xs text-rose-600">{registerErrors.delivery_phone}</p>}
                      </div>
                      <div>
                        <label htmlFor="account-login-phone" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. HP Login</label>
                        <input
                          id="account-login-phone"
                          type="tel"
                          inputMode="numeric"
                          value={registerData.no_tel}
                          onChange={(e) => handleRegisterPhoneChange(e.target.value)}
                          className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        />
                        <p className="mt-1 text-xs text-slate-400">Jika kosong, ikut No. HP penghantaran.</p>
                        {registerErrors.no_tel && <p className="mt-1 text-xs text-rose-600">{registerErrors.no_tel}</p>}
                      </div>
                    </div>

                    <div>
                      <label htmlFor="account-address-input" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alamat Penghantaran</label>
                      <textarea
                        id="account-address-input"
                        value={registerData.address}
                        onChange={(e) => handleRegisterAddressChange(e.target.value)}
                        rows={3}
                        className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      />
                      {registerErrors.address && <p className="mt-1 text-xs text-rose-600">{registerErrors.address}</p>}
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                      <div>
                        <label htmlFor="account-password" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Kata Laluan</label>
                        <input
                          id="account-password"
                          type="password"
                          autoComplete="new-password"
                          value={registerData.password}
                          onChange={(e) => handleRegisterPasswordChange(e.target.value)}
                          className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        />
                        {registerErrors.password && <p className="mt-1 text-xs text-rose-600">{registerErrors.password}</p>}
                      </div>
                      <div>
                        <label htmlFor="account-password-confirmation" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Sahkan Kata Laluan</label>
                        <input
                          id="account-password-confirmation"
                          type="password"
                          autoComplete="new-password"
                          value={registerData.password_confirmation}
                          onChange={(e) => handleRegisterPasswordConfirmationChange(e.target.value)}
                          className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        />
                        {registerErrors.password_confirmation && <p className="mt-1 text-xs text-rose-600">{registerErrors.password_confirmation}</p>}
                      </div>
                    </div>

                    {registerErrorMessages.length > 0 && (
                      <div className="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700">
                        {registerErrorMessages.map((message) => <p key={message}>{message}</p>)}
                      </div>
                    )}

                    <button
                      type="button"
                      onClick={handleRegister}
                      disabled={registerProcessing || !(registerData.no_tel.trim() || data.customer_phone.trim())}
                      className="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                      {registerProcessing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                      {registerProcessing ? 'Sedang Mendaftar...' : 'Create Akaun & Login'}
                    </button>
                  </div>
                ) : (
                  <div
                    className="mt-4 space-y-4"
                    role="tabpanel"
                    aria-label="Login"
                    onKeyDown={(event) => {
                      if (event.key === 'Enter') {
                        event.preventDefault();
                        handleLoginSubmit();
                      }
                    }}
                  >
                    <p className="text-sm leading-relaxed text-slate-500">Login menggunakan No. HP atau email dan kata laluan akaun anda.</p>

                    {loginErrors.login && (
                      <div className="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{loginErrors.login}</div>
                    )}

                    <div>
                      <label htmlFor="order-login" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. HP / Email</label>
                      <input
                        id="order-login"
                        type="text"
                        autoComplete="username"
                        value={loginData.login}
                        onChange={(e) => setLoginData('login', e.target.value)}
                        className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <div>
                      <label htmlFor="order-login-password" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Kata Laluan</label>
                      <input
                        id="order-login-password"
                        type="password"
                        autoComplete="current-password"
                        value={loginData.password}
                        onChange={(e) => handleLoginPasswordChange(e.target.value)}
                        className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                        required
                      />
                    </div>
                    <label className="flex items-center gap-2 text-sm text-slate-600">
                      <input
                        type="checkbox"
                        checked={loginData.remember}
                        onChange={(e) => setLoginData('remember', e.target.checked)}
                        className="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                      />
                      Ingat saya
                    </label>
                    <button
                      type="button"
                      onClick={handleLoginSubmit}
                      disabled={loginProcessing}
                      className="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                      {loginProcessing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                      {loginProcessing ? 'Sedang Login...' : 'Login'}
                    </button>
                  </div>
                )}
              </section>
            </div>

            {/* Right: Summary */}
            <div className="lg:col-span-1">
              <div className="sticky top-24 frontend-flat-card p-6">
                <h3 className="text-lg font-bold text-slate-900">Ringkasan Tempahan</h3>

                <div className="mt-4 space-y-3">
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Design</span>
                    <span className="font-medium text-slate-900">
                      {selectedDesign === 'custom' ? 'Custom' : selectedDesignInfo?.name ?? '-'}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Saiz</span>
                    <span className="font-medium text-slate-900">
                      {requestCustomSize ? 'Custom' : sizes.find((s) => s.id === selectedSize)?.name ?? '-'}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Kuantiti</span>
                    <span className="font-medium text-slate-900">{quantity} pcs</span>
                  </div>
                  {priceCalculation && (
                    <div className="flex justify-between text-sm">
                      <span className="text-slate-500">Helai A3</span>
                      <span className="font-medium text-slate-900">{priceCalculation.a3Sheets}</span>
                    </div>
                  )}
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-500">Potong</span>
                    <span className="font-medium text-slate-900">{cutType === 'die-cut' ? 'Ikut Bentuk' : 'Standard'}</span>
                  </div>
                  {data.customer_design_image && (
                    <div className="flex justify-between text-sm">
                      <span className="text-slate-500">Design Hantar</span>
                      <span className="font-medium text-emerald-600">Ya</span>
                    </div>
                  )}
                </div>

                {priceCalculation && (
                  <div className="mt-3 border-t border-slate-100 pt-3 space-y-1 text-xs text-slate-500">
                    <p>RM {priceCalculation.pricePerA3.toFixed(2)} × {priceCalculation.a3Sheets} A3</p>
                  </div>
                )}

                <div className="mt-1 border-t border-slate-100 pt-4">
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-bold text-slate-900">Jumlah</span>
                    <span className="text-xl font-extrabold text-brand-600">
                      {priceCalculation !== null ? `RM ${priceCalculation.total.toFixed(2)}` : 'Pending'}
                    </span>
                  </div>
                  {(requestCustomSize || (!selectedSizeObj?.qty_per_a3)) && (
                    <p className="mt-2 flex items-start gap-1.5 text-xs text-amber-600">
                      <Info className="h-3.5 w-3.5 shrink-0" />
                      Harga akan dimaklumkan selepas admin semak tempahan anda.
                    </p>
                  )}
                </div>

                {!auth.user ? (
                  <div className="mt-6 space-y-3">
                    <p className="text-xs text-slate-500">Anda perlu log masuk untuk checkout.</p>
                    <Link
                      href={route('member.login')}
                      className="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
                    >
                      Log Masuk untuk Mula Tempahan
                      <ChevronRight className="h-4 w-4" />
                    </Link>
                  </div>
                ) : (
                  <button
                    type="submit"
                    disabled={processing || (!selectedDesign && !customDesc) || (!requestCustomSize && !selectedSize) || !!isDieCutTooSmall}
                    className="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20 disabled:opacity-50"
                  >
                    <ShoppingCart className="h-4 w-4" />
                    {processing ? 'Menghantar...' : 'Hantar Tempahan'}
                  </button>
                )}

                {paymentSettings && (
                  <div className="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
                    <p className="font-semibold text-slate-700">Deposit: RM {paymentSettings.deposit_amount}</p>
                    <p>atau bayar penuh selepas confirm design.</p>
                  </div>
                )}
              </div>
            </div>
          </form>

          {submitErrorMessages.length > 0 && (
            <div className="fixed inset-0 z-[80] flex items-center justify-center p-4" role="presentation">
              <button
                type="button"
                aria-label="Tutup mesej error"
                className="absolute inset-0 bg-slate-950/60"
                onClick={() => setSubmitErrorMessages([])}
              />
              <div
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="order-error-title"
                aria-describedby="order-error-description"
                className="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl"
              >
                <div className="flex items-start gap-3">
                  <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <AlertCircle className="h-6 w-6" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <h2 id="order-error-title" className="text-lg font-bold text-slate-900">Tempahan tidak dapat dihantar</h2>
                    <p id="order-error-description" className="mt-1 text-sm text-slate-500">Sila betulkan maklumat berikut:</p>
                  </div>
                  <button
                    type="button"
                    onClick={() => setSubmitErrorMessages([])}
                    aria-label="Tutup mesej error"
                    className="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>
                <ul className="mt-5 space-y-2 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-800">
                  {submitErrorMessages.map((message) => (
                    <li key={message} className="flex items-start gap-2">
                      <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500" />
                      <span>{message}</span>
                    </li>
                  ))}
                </ul>
                <button
                  type="button"
                  onClick={() => setSubmitErrorMessages([])}
                  className="mt-5 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-700"
                >
                  Semak Semula
                </button>
              </div>
            </div>
          )}

          {isDesignPickerOpen && (
            <div className="fixed inset-0 z-50 flex items-center justify-center p-4" role="presentation">
              <button
                type="button"
                aria-label="Tutup katalog design"
                className="absolute inset-0 bg-slate-950/50"
                onClick={closeDesignPicker}
              />
              <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="design-picker-title"
                className="relative flex max-h-[calc(100dvh-2rem)] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl"
              >
                <div className="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">Katalog design</p>
                    <h2 id="design-picker-title" className="mt-1 text-xl font-bold text-slate-900">Pilih design sticker</h2>
                    <p className="mt-1 text-sm text-slate-500">Cari design yang sesuai. Imej dimuatkan mengikut halaman.</p>
                  </div>
                  <button
                    type="button"
                    onClick={closeDesignPicker}
                    aria-label="Tutup katalog design"
                    className="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>

                <div className="overflow-y-auto p-5 sm:p-6">
                  <div className="flex gap-2">
                    <div className="relative min-w-0 flex-1">
                      <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                      <input
                        type="search"
                        value={catalogSearch}
                        onChange={(event) => setCatalogSearch(event.target.value)}
                        onKeyDown={(event) => {
                          if (event.key === 'Enter') {
                            event.preventDefault();
                            handleCatalogSearch();
                          }
                        }}
                        aria-label="Cari design"
                        placeholder="Cari nama design..."
                        className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100"
                      />
                    </div>
                    <button
                      type="button"
                      onClick={handleCatalogSearch}
                      disabled={catalogLoading}
                      className="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700 disabled:opacity-50"
                    >
                      Cari
                    </button>
                  </div>

                  {catalogTags.length > 0 && (
                    <div className="mt-4 flex flex-wrap items-center gap-2">
                      <span className="text-xs font-semibold text-slate-400">#</span>
                      <button
                        type="button"
                        onClick={() => handleCatalogTag(null)}
                        className={`rounded-full px-3 py-1 text-[11px] font-bold transition ${
                          catalogTag === null
                            ? 'bg-brand-100 text-brand-700 ring-1 ring-brand-300'
                            : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                        }`}
                      >
                        Semua
                      </button>
                      {catalogTags.map((tag) => (
                        <button
                          key={tag}
                          type="button"
                          onClick={() => handleCatalogTag(catalogTag === tag ? null : tag)}
                          className={`rounded-full px-3 py-1 text-[11px] font-bold transition ${
                            catalogTag === tag
                              ? 'bg-brand-100 text-brand-700 ring-1 ring-brand-300'
                              : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600'
                          }`}
                        >
                          #{tag}
                        </button>
                      ))}
                    </div>
                  )}

                  {previousProjects.length > 0 && (
                    <div className="mt-5 rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                      <div className="flex items-center gap-2">
                        <FolderKanban className="h-4 w-4 text-brand-600" />
                        <p className="text-sm font-bold text-brand-900">Design project yang pernah dibuat</p>
                      </div>
                      <p className="mt-1 text-xs text-brand-700">Pilih semula design ini untuk buat order print.</p>
                      <div className="mt-3 grid gap-2 sm:grid-cols-2">
                        {previousProjects.map((project) => (
                          <button
                            key={project.id}
                            type="button"
                            onClick={() => chooseProject(project)}
                            aria-pressed={selectedDesign === 'project' && selectedProject?.id === project.id}
                            className={`flex min-w-0 items-center gap-2 rounded-xl border-2 bg-white px-3 py-2 text-left transition ${
                              selectedDesign === 'project' && selectedProject?.id === project.id
                                ? 'border-brand-600'
                                : 'border-brand-100 hover:border-brand-300'
                            }`}
                          >
                            <span className="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-brand-50 text-brand-600">
                              {project.preview_url ? (
                                <img src={project.preview_url} alt="" className="h-full w-full object-contain" />
                              ) : (
                                <FolderKanban className="h-4 w-4" />
                              )}
                            </span>
                            <span className="min-w-0 flex-1">
                              <span className="block truncate text-xs font-semibold text-slate-700">{project.title}</span>
                              <span className="block truncate text-[10px] text-slate-500">Design sendiri / project customer</span>
                            </span>
                            {selectedDesign === 'project' && selectedProject?.id === project.id && <Check className="h-3.5 w-3.5 shrink-0 text-brand-600" />}
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {previousDesigns.length > 0 && (
                    <div className="mt-5 rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                      <div className="flex items-center gap-2">
                        <RotateCcw className="h-4 w-4 text-brand-600" />
                        <p className="text-sm font-bold text-brand-900">Design yang pernah ditempah</p>
                      </div>
                      <div className="mt-3 flex gap-2 overflow-x-auto pb-1">
                        {previousDesigns.map((design) => (
                          <button
                            key={design.id}
                            type="button"
                            onClick={() => chooseDesign(design)}
                            className={`inline-flex shrink-0 items-center gap-2 rounded-xl border-2 bg-white px-3 py-2 text-left transition ${
                              selectedDesign === design.id ? 'border-brand-600' : 'border-brand-100 hover:border-brand-300'
                            }`}
                          >
                            <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                              <RotateCcw className="h-3.5 w-3.5" />
                            </span>
                            <span className="max-w-40 truncate text-xs font-semibold text-slate-700">
                              <span className="text-brand-500">#</span>{design.name}
                            </span>
                            {selectedDesign === design.id && <Check className="h-3.5 w-3.5 text-brand-600" />}
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  <div className="mt-6">
                    <div className="mb-3 flex items-center justify-between gap-3">
                      <p className="text-sm font-bold text-slate-900">
                        Semua design{catalogTotal > 0 ? ` (${catalogTotal})` : ''}
                      </p>
                      {catalogLoading && catalogDesigns.length > 0 && (
                        <span className="inline-flex items-center gap-1.5 text-xs text-slate-500">
                          <LoaderCircle className="h-3.5 w-3.5 animate-spin" />
                          Memuatkan
                        </span>
                      )}
                    </div>

                    {catalogLoading && catalogDesigns.length === 0 && (
                      <div className="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500">
                        <span className="inline-flex items-center gap-2">
                          <LoaderCircle className="h-4 w-4 animate-spin" />
                          Memuatkan katalog...
                        </span>
                      </div>
                    )}

                    {!catalogLoading && catalogError && (
                      <div className="rounded-2xl border border-rose-100 bg-rose-50 p-5 text-center">
                        <p className="text-sm font-semibold text-rose-700">Katalog tidak dapat dimuatkan.</p>
                        <button
                          type="button"
                          onClick={handleCatalogSearch}
                          className="mt-3 rounded-lg bg-white px-3 py-2 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-200 transition hover:bg-rose-100"
                        >
                          Cuba lagi
                        </button>
                      </div>
                    )}

                    {!catalogLoading && !catalogError && catalogLoaded && catalogDesigns.length === 0 && (
                      <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <p className="text-sm font-semibold text-slate-700">Design tidak dijumpai.</p>
                        <p className="mt-1 text-xs text-slate-500">Cuba kata carian yang lain.</p>
                      </div>
                    )}

                    {catalogDesigns.length > 0 && (
                      <div className="grid grid-cols-3 gap-2 sm:grid-cols-3 sm:gap-3 md:grid-cols-4">
                        {catalogDesigns.map((design) => (
                          <button
                            key={design.id}
                            type="button"
                            onClick={() => handleCatalogDesignClick(design)}
                            className={`relative overflow-hidden rounded-xl border-2 text-left transition sm:rounded-2xl ${
                              selectedDesign === design.id
                                ? 'border-brand-600 shadow-sm shadow-brand-600/10'
                                : 'border-slate-200 hover:border-brand-300'
                            }`}
                          >
                            <div className="aspect-square bg-white">
                              {design.image_url ? (
                                <ResponsiveDesignImage
                                  src={design.image_url}
                                  mobileSrc={design.mobile_image_url}
                                  alt={design.name}
                                  loading="lazy"
                                  decoding="async"
                                  className="h-full w-full object-contain"
                                />
                              ) : (
                                <div className="flex h-full items-center justify-center">
                                  <ImageIcon className="h-8 w-8 text-slate-300" />
                                </div>
                              )}
                            </div>
                            <p className="truncate px-1.5 py-1.5 text-[10px] font-semibold text-slate-700 sm:px-2.5 sm:py-2 sm:text-xs">
                              <span className="text-brand-500">#</span>{design.name}
                            </p>
                            {selectedDesign === design.id && (
                              <span className="absolute right-2 top-2 flex h-5 w-5 items-center justify-center rounded-full bg-brand-600">
                                <Check className="h-3 w-3 text-white" />
                              </span>
                            )}
                          </button>
                        ))}
                      </div>
                    )}

                    {hasMoreCatalogDesigns && (
                      <button
                        type="button"
                        onClick={() => void loadCatalog(catalogOffset, false, catalogSearch.trim())}
                        disabled={catalogLoading}
                        className="mt-5 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-brand-300 hover:text-brand-700 disabled:opacity-50"
                      >
                        {catalogLoading && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Muatkan lagi design
                      </button>
                    )}
                  </div>
                </div>
              </div>

              {catalogPreview && (
                <div className="absolute inset-0 z-20 flex items-end justify-center bg-slate-950/50 p-4 sm:items-center">
                  <button
                    type="button"
                    aria-label="Tutup preview design"
                    className="absolute inset-0 cursor-default"
                    onClick={() => setCatalogPreview(null)}
                  />
                  <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="catalog-preview-title"
                    className="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
                  >
                    <div className="relative bg-white">
                      <img
                        src={catalogPreview.image_url ?? ''}
                        alt={`Preview design ${catalogPreview.name}`}
                        width="900"
                        height="900"
                        loading="eager"
                        decoding="async"
                        className="aspect-square max-h-[58dvh] w-full object-contain"
                      />
                      <button
                        type="button"
                        onClick={() => setCatalogPreview(null)}
                        aria-label="Tutup preview design"
                        className="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-md transition hover:bg-white hover:text-slate-900"
                      >
                        <X className="h-4 w-4" />
                      </button>
                    </div>
                    <div className="p-4">
                      <p id="catalog-preview-title" className="text-base font-bold text-slate-900">
                        {catalogPreview.name}
                      </p>
                      <p className="mt-1 text-xs text-slate-500">
                        Tekan butang di bawah untuk pilih design ini.
                      </p>
                      <div className="mt-4 flex gap-2">
                        <button
                          type="button"
                          onClick={() => chooseDesign(catalogPreview)}
                          className="flex-1 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-700"
                        >
                          Pilih Design
                        </button>
                        <button
                          type="button"
                          onClick={() => setCatalogPreview(null)}
                          className="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-brand-200 hover:text-brand-700"
                        >
                          Kembali
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </FrontendLayout>
  );
}
