import FrontendLayout from '@/Components/Layouts/FrontendLayout';
import MemberLayout from '@/Components/Layouts/MemberLayout';
import AdminLayout from '@/Components/Layouts/AdminLayout';
import PublicHeader from '@/Components/PublicHeader';
import ResponsiveDesignImage from '@/Components/ResponsiveDesignImage';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';
import { whatsappWebUrl, WHATSAPP_TARGET } from '@/lib/whatsapp';
import { calculateBillableA3Sheets, minimumA3Sheets } from '@/lib/stickerPricing';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/Components/ui/tooltip';
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
  Plus,
  RotateCcw,
  Search,
  ShoppingCart,
  Trash2,
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
  customer_project_id: number | null;
  custom_design_description: string | null;
  sticker_size_id: number | null;
  requested_size: string | null;
  quantity: number;
  cut_type: string | null;
  repeat_preview_url?: string | null;
}

interface PreviousOrderDesign {
  id: number;
  preview_index: number;
  title: string;
  preview_url: string;
  order_no: string | null;
  size_id: number | null;
  size_name: string;
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
  shipping_region?: 'peninsular' | 'sabah_sarawak' | null;
  shipping_free_forever: boolean;
  items: RepeatOrderItem[];
}

interface OrderItemDraft {
  key: string;
  design_id: number | null;
  project_id: number | null;
  custom_description: string;
  size_id: number | null;
  requested_size: string;
  quantity: number;
  cut_type: 'standard' | 'die-cut';
  customer_design_images: File[];
  previous_order_item_id: number | null;
  design_name: string;
}

interface ProjectOption {
  id: number;
  title: string;
  notes: string | null;
  customer_address_id: number | null;
  preview_url: string | null;
  created_at: string;
}

interface SizeOption {
  id: number;
  name: string;
  width_cm: number;
  height_cm: number;
  shape: string | null;
  qty_per_a3: number | null;
  is_active: boolean;
}

type SizeInputMode = 'diameter' | 'length' | 'rectangle';

const ORDER_DRAFT_STORAGE_KEY = 'stickertermurah.order-draft.v1';
const ORDER_DRAFT_MAX_AGE = 2 * 60 * 60 * 1000;

type StoredOrderItem = Omit<OrderItemDraft, 'customer_design_images'> & {
  customer_design_images: [];
};

interface StoredOrderDraft {
  version: 1;
  savedAt: number;
  selectedDesign: number | 'custom' | 'project' | 'previous';
  selectedDesignInfo: DesignOption | null;
  selectedProject: ProjectOption | null;
  selectedPreviousOrderDesign: PreviousOrderDesign | null;
  customDesc: string;
  selectedSize: number | null;
  selectedShape?: string;
  sizePrimary?: string;
  sizeSecondary?: string;
  quantity: number;
  requestCustomSize: boolean;
  customSizeDesc: string;
  cutType: 'standard' | 'die-cut';
  savedOrderItems: StoredOrderItem[];
  form: {
    customer_address_id: number | null;
    design_id: number | null;
    project_id: number | null;
    custom_description: string;
    order_note: string;
    size_id: number | null;
    requested_size: string;
    quantity: number;
    cut_type: 'standard' | 'die-cut';
    customer_name: string;
    customer_phone: string;
    customer_address: string;
    shipping_region?: 'peninsular' | 'sabah_sarawak';
    repeat_from_order_id: number | null;
  };
}

function readStoredOrderDraft(): StoredOrderDraft | null {
  if (typeof window === 'undefined') return null;

  try {
    const raw = window.sessionStorage.getItem(ORDER_DRAFT_STORAGE_KEY);
    if (!raw) return null;

    const draft = JSON.parse(raw) as StoredOrderDraft;
    if (
      draft.version !== 1
      || typeof draft.savedAt !== 'number'
      || !draft.form
      || Date.now() - draft.savedAt > ORDER_DRAFT_MAX_AGE
    ) {
      window.sessionStorage.removeItem(ORDER_DRAFT_STORAGE_KEY);
      return null;
    }

    return draft;
  } catch {
    window.sessionStorage.removeItem(ORDER_DRAFT_STORAGE_KEY);
    return null;
  }
}

function removeStoredOrderDraft(): void {
  if (typeof window !== 'undefined') {
    window.sessionStorage.removeItem(ORDER_DRAFT_STORAGE_KEY);
  }
}

interface AdminCustomerAddress {
  id: number;
  recipient_name: string | null;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

interface AdminCustomerOption {
  id: number;
  name: string;
  email: string | null;
  no_tel: string | null;
  addresses: AdminCustomerAddress[];
}

interface OrderFormProps extends PageProps {
  adminMode: boolean;
  initialCustomerId: number | null;
  initialAddressId: number | null;
  customers: AdminCustomerOption[];
  memberMode: boolean;
  initialDesign: DesignOption | null;
  initialProject: ProjectOption | null;
  sizes: SizeOption[];
  previousDesigns: DesignOption[];
  previousProjects: ProjectOption[];
  previousOrderDesigns: PreviousOrderDesign[];
  catalogTags: string[];
  priceSettings: Array<{
    id: number;
    sticker_type: string;
    qty_from: number;
    qty_to: number | null;
    price_per_a3: number | string;
  }>;
  minimumA3SheetsWithoutDesign: number;
  paymentSettings: {
    bank_name: string;
    bank_account_no: string;
    bank_account_name: string;
    deposit_amount: number;
  } | null;
  repeatOrder: RepeatOrder | null;
}

function normalizeShape(shape: string | null | undefined): string {
  return (shape ?? '').trim().toLowerCase().replace(/[\s_-]+/g, '');
}

function shapeLabel(shape: string | null | undefined): string {
  const normalized = normalizeShape(shape);

  if (normalized === 'bulat') return 'Bulat';
  if (normalized === 'segiempat' || normalized === 'segiempatsama') return 'Segi Empat Sama';

  return shape?.trim() || 'Lain-lain';
}

function sizeInputMode(shape: string): SizeInputMode {
  const normalized = normalizeShape(shape);

  if (normalized === 'bulat') return 'diameter';
  if (normalized === 'segiempat' || normalized === 'segiempatsama') return 'length';

  return 'rectangle';
}

function dimensionDescription(shape: string, primary: string, secondary: string): string {
  const mode = sizeInputMode(shape);

  if (mode === 'diameter') return primary ? `Diameter ${primary}cm` : '';
  if (mode === 'length') return primary ? `Panjang ${primary}cm` : '';

  return primary && secondary ? `${primary}cm x ${secondary}cm` : '';
}

function requestedSizeDetails(value: string | null | undefined): { shape: string; primary: string; secondary: string } {
  const parts = (value ?? '').split(':', 2);
  if (parts.length < 2) return { shape: '', primary: '', secondary: '' };

  const dimensions = parts[1].match(/\d+(?:[.,]\d+)?/g) ?? [];

  return {
    shape: parts[0].trim(),
    primary: dimensions[0]?.replace(',', '.') ?? '',
    secondary: dimensions[1]?.replace(',', '.') ?? '',
  };
}

function formatDimension(value: number): string {
  const numeric = Number(value);
  if (!Number.isFinite(numeric)) return '';

  return Number.isInteger(numeric)
    ? String(numeric)
    : numeric.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
}

function positiveDimension(value: string): number | null {
  const numeric = Number(value);

  return Number.isFinite(numeric) && numeric > 0 ? numeric : null;
}

function dimensionsMatch(actual: number, expected: number): boolean {
  return Math.abs(Number(actual) - expected) < 0.001;
}

function findSizeForDimensions(sizes: SizeOption[], shape: string, primary: string, secondary: string): SizeOption | null {
  const first = positiveDimension(primary);
  if (first === null) return null;

  const mode = sizeInputMode(shape);
  const second = mode === 'rectangle' ? positiveDimension(secondary) : first;
  if (second === null) return null;

  const dimensionMatches = sizes.filter((size) => {
    const width = Number(size.width_cm);
    const height = Number(size.height_cm);
    if (!Number.isFinite(width) || !Number.isFinite(height)) return false;

    if (mode !== 'rectangle') {
      return dimensionsMatch(width, first) && dimensionsMatch(height, first);
    }

    return (dimensionsMatch(width, first) && dimensionsMatch(height, second))
      || (dimensionsMatch(width, second) && dimensionsMatch(height, first));
  });

  const exactShapeMatch = dimensionMatches.find((size) => shapeLabel(size.shape) === shape);
  if (exactShapeMatch) return exactShapeMatch;

  if (mode !== 'diameter') return null;

  return dimensionMatches.find((size) => {
    const normalized = normalizeShape(size.shape);
    return normalized === 'segiempat' || normalized === 'segiempatsama';
  }) ?? dimensionMatches[0] ?? null;
}

export default function OrderForm() {
  const { adminMode, initialCustomerId, initialAddressId, customers, memberMode, initialDesign, initialProject, previousDesigns, previousProjects, previousOrderDesigns, catalogTags, sizes, priceSettings, minimumA3SheetsWithoutDesign, paymentSettings, repeatOrder, auth, app, flash } = usePage<OrderFormProps>().props;
  const canAddItems = adminMode || memberMode;

  const repeatItem = repeatOrder?.items?.[0] ?? null;
  const isRepeatOrder = repeatOrder !== null;
  const initialDesignId = initialDesign?.id ?? null;
  const configuredWhatsappPhone = app.whatsapp_phone.replace(/\D/g, '');
  const whatsappPhone = configuredWhatsappPhone
    ? (configuredWhatsappPhone.startsWith('0') ? `60${configuredWhatsappPhone.slice(1)}` : configuredWhatsappPhone)
    : '601169409606';
  const whatsappLink = whatsappWebUrl(whatsappPhone, 'Assalamualaikum, saya perlukan bantuan untuk tempahan sticker.');
  const initialAdminCustomer = adminMode
    ? customers.find((customer) => customer.id === initialCustomerId) ?? null
    : null;
  const initialAdminAddress = initialAdminCustomer?.addresses.find((address) => address.id === initialAddressId)
    ?? initialAdminCustomer?.addresses[0]
    ?? null;
  const initialShippingRegion = repeatOrder?.shipping_region === 'sabah_sarawak' ? 'sabah_sarawak' : 'peninsular';
  const initialSize = repeatItem?.sticker_size_id
    ? sizes.find((size) => size.id === repeatItem.sticker_size_id) ?? null
    : null;
  const requestedSize = requestedSizeDetails(repeatItem?.requested_size);
  const initialShape = initialSize ? shapeLabel(initialSize.shape) : requestedSize.shape;
  const initialSizeMode = initialShape ? sizeInputMode(initialShape) : null;
  const initialRepeatPreview = !initialDesign && !initialProject && repeatItem?.repeat_preview_url
    ? {
        id: repeatItem.id,
        preview_index: 0,
        title: repeatItem.custom_design_description ?? 'Design order terdahulu',
        preview_url: repeatItem.repeat_preview_url,
        order_no: repeatOrder?.order_no ?? null,
        size_id: repeatItem.sticker_size_id,
        size_name: initialSize?.name ?? repeatItem.requested_size ?? 'Saiz custom',
        requested_size: repeatItem.requested_size,
        quantity: repeatItem.quantity,
        cut_type: repeatItem.cut_type,
      }
    : null;
  const initialSelection = initialProject
    ? 'project'
    : initialDesignId
      ? initialDesignId
      : initialRepeatPreview
        ? 'previous'
        : 'custom';
  const [selectedCustomerId, setSelectedCustomerId] = useState<number | null>(initialAdminCustomer?.id ?? null);
  const selectedAdminCustomer = customers.find((customer) => customer.id === selectedCustomerId) ?? null;
  const [adminCustomerSearch, setAdminCustomerSearch] = useState('');
  const [showAdminCustomerPicker, setShowAdminCustomerPicker] = useState(false);
  const defaultCustomerAddress = adminMode
    ? null
    : auth.customerAddresses.find((address) => address.is_default)
      ?? auth.customerAddresses[0]
      ?? null;
  const filteredAdminCustomers = useMemo(() => {
    const query = adminCustomerSearch.trim().toLowerCase();

    return customers
      .filter((customer) => {
        if (query === '') return true;

        const searchable = [
          customer.name,
          customer.email ?? '',
          customer.no_tel ?? '',
          ...customer.addresses.flatMap((address) => [address.recipient_name ?? '', address.no_hp ?? '']),
        ].join(' ').toLowerCase();

        return searchable.includes(query);
      })
      .slice(0, 50);
  }, [adminCustomerSearch, customers]);

  const [selectedDesign, setSelectedDesign] = useState<number | 'custom' | 'project' | 'previous'>(
    initialSelection
  );
  const [selectedDesignInfo, setSelectedDesignInfo] = useState<DesignOption | null>(initialDesign);
  const [selectedProject, setSelectedProject] = useState<ProjectOption | null>(initialProject);
  const [selectedPreviousOrderDesign, setSelectedPreviousOrderDesign] = useState<PreviousOrderDesign | null>(initialRepeatPreview);
  const [customDesc, setCustomDesc] = useState(repeatItem?.custom_design_description ?? '');
  const [selectedSize, setSelectedSize] = useState<number | null>(repeatItem?.sticker_size_id ?? null);
  const [selectedShape, setSelectedShape] = useState(initialShape);
  const [sizePrimary, setSizePrimary] = useState(initialSize ? formatDimension(initialSize.width_cm) : requestedSize.primary);
  const [sizeSecondary, setSizeSecondary] = useState(initialSize && initialSizeMode === 'rectangle' ? formatDimension(initialSize.height_cm) : requestedSize.secondary);
  const [quantity, setQuantity] = useState(repeatItem?.quantity ?? 100);
  const [requestCustomSize, setRequestCustomSize] = useState(!!repeatItem?.requested_size && !repeatItem?.sticker_size_id);
  const [customSizeDesc, setCustomSizeDesc] = useState(repeatItem?.requested_size ?? '');
  const [cutType, setCutType] = useState<'standard' | 'die-cut'>(
    repeatItem?.cut_type === 'die-cut' ? 'die-cut' : 'standard'
  );
  const [savedOrderItems, setSavedOrderItems] = useState<OrderItemDraft[]>([]);
  const [designPreviews, setDesignPreviews] = useState<Array<{ id: string; name: string; url: string | null }>>([]);
  const [isDesignPickerOpen, setIsDesignPickerOpen] = useState(false);
  const [catalogPreview, setCatalogPreview] = useState<DesignOption | null>(null);
  const [projectPreview, setProjectPreview] = useState<ProjectOption | null>(null);
  const [catalogDesigns, setCatalogDesigns] = useState<DesignOption[]>([]);
  const [catalogSearch, setCatalogSearch] = useState('');
  const [catalogTag, setCatalogTag] = useState<string | null>(null);
  const [catalogOffset, setCatalogOffset] = useState(0);
  const [catalogTotal, setCatalogTotal] = useState(0);
  const [catalogLoading, setCatalogLoading] = useState(false);
  const [catalogLoaded, setCatalogLoaded] = useState(false);
  const [catalogError, setCatalogError] = useState(false);
  const [submitErrorMessages, setSubmitErrorMessages] = useState<string[]>([]);
  const [itemAddedSuccess, setItemAddedSuccess] = useState(false);
  const [draftRestored, setDraftRestored] = useState(false);
  const [accountTab, setAccountTab] = useState<'register' | 'login'>('register');
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(initialAdminAddress?.id ?? defaultCustomerAddress?.id ?? null);
  const [loginPhoneCustomized, setLoginPhoneCustomized] = useState(false);
  const [loginPasswordCustomized, setLoginPasswordCustomized] = useState(false);
  const catalogAbortRef = useRef<AbortController | null>(null);
  const addedItemsSectionRef = useRef<HTMLElement | null>(null);
  const draftRestoredRef = useRef(false);

  const { data, setData, post, processing, errors, transform } = useForm({
    customer_id: initialAdminCustomer?.id ?? null,
    customer_address_id: initialAdminAddress?.id ?? defaultCustomerAddress?.id ?? null,
    design_id: initialProject ? null : initialDesignId,
    project_id: initialProject?.id ?? null,
    custom_description: repeatItem?.custom_design_description ?? '',
    order_note: '',
    size_id: repeatItem?.sticker_size_id ?? null,
    requested_size: repeatItem?.requested_size ?? '',
    quantity: repeatItem?.quantity ?? 100,
    cut_type: (repeatItem?.cut_type === 'die-cut' ? 'die-cut' : 'standard') as 'standard' | 'die-cut',
     customer_design_image: null as File | null,
     customer_design_images: [] as File[],
     previous_order_item_id: isRepeatOrder ? repeatItem?.id ?? null : null,
     customer_name: adminMode ? (initialAdminAddress?.recipient_name ?? initialAdminCustomer?.name ?? '') : (repeatOrder?.customer_name ?? auth.user?.name ?? ''),
    customer_phone: adminMode ? (initialAdminAddress?.no_hp ?? initialAdminCustomer?.no_tel ?? '') : (repeatOrder?.customer_phone ?? auth.user?.no_tel ?? ''),
    customer_address: adminMode ? (initialAdminAddress?.address ?? '') : (repeatOrder?.customer_address ?? defaultCustomerAddress?.address ?? ''),
     shipping_region: initialShippingRegion as 'peninsular' | 'sabah_sarawak',
     shipping_free_forever: false,
     repeat_from_order_id: repeatOrder?.id ?? null,
   });

  const setConfiguredSize = (sizeId: number | null) => {
    const size = sizeId === null ? null : sizes.find((candidate) => candidate.id === sizeId) ?? null;

    setSelectedSize(size?.id ?? null);
    setSelectedShape(size ? shapeLabel(size.shape) : '');
    setSizePrimary(size ? formatDimension(size.width_cm) : '');
    setSizeSecondary(size && sizeInputMode(shapeLabel(size.shape)) === 'rectangle' ? formatDimension(size.height_cm) : '');
    setData('size_id', size?.id ?? null);
    setData('requested_size', '');
  };

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
    password: '',
    password_confirmation: '',
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

  useEffect(() => {
    if (draftRestoredRef.current || adminMode || initialDesign || initialProject || repeatOrder) return;

    draftRestoredRef.current = true;
    const draft = readStoredOrderDraft();
    if (!draft) return;

    setSelectedDesign(draft.selectedDesign);
    setSelectedDesignInfo(draft.selectedDesignInfo ?? null);
    setSelectedProject(draft.selectedProject ?? null);
    setSelectedPreviousOrderDesign(draft.selectedPreviousOrderDesign ?? null);
    setCustomDesc(draft.customDesc ?? '');
    setSelectedSize(draft.selectedSize ?? null);
    const draftSize = draft.selectedSize === null
      ? null
      : sizes.find((size) => size.id === draft.selectedSize) ?? null;
    const draftShape = draft.selectedShape ?? (draftSize ? shapeLabel(draftSize.shape) : '');
    setSelectedShape(draftShape);
    setSizePrimary(draft.sizePrimary ?? (draftSize ? formatDimension(draftSize.width_cm) : ''));
    setSizeSecondary(draft.sizeSecondary ?? (draftSize && sizeInputMode(draftShape) === 'rectangle' ? formatDimension(draftSize.height_cm) : ''));
    setQuantity(draft.quantity || 100);
    setRequestCustomSize(Boolean(draft.requestCustomSize));
    setCustomSizeDesc(draft.customSizeDesc ?? '');
    setCutType(draft.cutType === 'die-cut' ? 'die-cut' : 'standard');
    setSavedOrderItems((draft.savedOrderItems ?? []).map((item) => ({
      ...item,
      customer_design_images: [],
    })));

    setSelectedAddressId(draft.form.customer_address_id ?? null);
    setData('customer_address_id', draft.form.customer_address_id ?? null);
    setData('design_id', draft.form.design_id ?? null);
    setData('project_id', draft.form.project_id ?? null);
    setData('custom_description', draft.form.custom_description ?? '');
    setData('order_note', draft.form.order_note ?? '');
    setData('size_id', draft.form.size_id ?? null);
    setData('requested_size', draft.form.requested_size ?? '');
    setData('quantity', draft.form.quantity || 100);
    setData('cut_type', draft.form.cut_type === 'die-cut' ? 'die-cut' : 'standard');
    setData('customer_name', draft.form.customer_name ?? '');
    setData('customer_phone', draft.form.customer_phone ?? '');
    setData('customer_address', draft.form.customer_address ?? '');
    setData('shipping_region', draft.form.shipping_region === 'sabah_sarawak' ? 'sabah_sarawak' : 'peninsular');
    setData('repeat_from_order_id', draft.form.repeat_from_order_id ?? null);
    setData('customer_design_image', null);
    setData('customer_design_images', []);

    setRegisterData('no_tel', draft.form.customer_phone ?? '');
    setRegisterData('delivery_phone', draft.form.customer_phone ?? '');
    setRegisterData('recipient_name', draft.form.customer_name ?? '');
    setRegisterData('address', draft.form.customer_address ?? '');
    setLoginData('login', draft.form.customer_phone ?? '');
    setLoginData('password', draft.form.customer_phone ?? '');
    setDraftRestored(true);
  }, [adminMode, initialDesign, initialProject, repeatOrder, setData, setLoginData, setRegisterData, sizes]);

  const saveOrderDraft = () => {
    if (adminMode || typeof window === 'undefined') return;

    const draft: StoredOrderDraft = {
      version: 1,
      savedAt: Date.now(),
      selectedDesign,
      selectedDesignInfo,
      selectedProject,
      selectedPreviousOrderDesign,
      customDesc,
      selectedSize,
      selectedShape,
      sizePrimary,
      sizeSecondary,
      quantity,
      requestCustomSize,
      customSizeDesc,
      cutType,
      savedOrderItems: savedOrderItems.map(({ customer_design_images: _images, ...item }) => ({
        ...item,
        customer_design_images: [],
      })),
      form: {
        customer_address_id: data.customer_address_id,
        design_id: data.design_id,
        project_id: data.project_id,
        custom_description: data.custom_description,
        order_note: data.order_note,
        size_id: data.size_id,
        requested_size: data.requested_size,
        quantity: data.quantity,
        cut_type: data.cut_type,
        customer_name: data.customer_name,
        customer_phone: data.customer_phone,
        customer_address: data.customer_address,
        shipping_region: data.shipping_region,
        repeat_from_order_id: data.repeat_from_order_id,
      },
    };

    window.sessionStorage.setItem(ORDER_DRAFT_STORAGE_KEY, JSON.stringify(draft));
  };

  const clearOrderDraft = () => {
    removeStoredOrderDraft();
    setDraftRestored(false);
  };

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
    }

    setLoginData('login', phone);

    if (!loginPasswordCustomized) setLoginData('password', phone);
  };

  const handleAdminCustomerChange = (customerId: number) => {
    const customer = customers.find((item) => item.id === customerId);
    if (!customer) return;

    const address = customer.addresses[0] ?? null;
    setSelectedCustomerId(customer.id);
    setSelectedAddressId(address?.id ?? null);
    setData('customer_id', customer.id);
    setData('customer_address_id', address?.id ?? null);
    setData('customer_name', customer.name);
    setData('customer_phone', address?.no_hp ?? customer.no_tel ?? '');
    setData('customer_address', address?.address ?? '');
    setAdminCustomerSearch('');
    setShowAdminCustomerPicker(false);
  };

  const handleAdminAddressChange = (addressId: number) => {
    const address = selectedAdminCustomer?.addresses.find((item) => item.id === addressId);
    if (!address) return;

    setSelectedAddressId(address.id);
    setData('customer_address_id', address.id);
    setData('customer_name', address.recipient_name ?? selectedAdminCustomer?.name ?? '');
    setData('customer_phone', address.no_hp ?? selectedAdminCustomer?.no_tel ?? '');
    setData('customer_address', address.address);
  };

  const handleRegisterPhoneChange = (phone: string) => {
    setLoginPhoneCustomized(phone.trim().length > 0);
    setRegisterData('no_tel', phone);
  };

  const handleRegisterPasswordChange = (password: string) => {
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
    saveOrderDraft();
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
    saveOrderDraft();
    transformLogin((form) => ({ ...form, from_order: true }));
    postLogin(route('member.login.attempt'), {
      preserveScroll: true,
    });
  };

  const handleAddressSelect = (addressId: number) => {
    const address = auth.customerAddresses.find((item) => item.id === addressId);
    if (!address) return;

    setSelectedAddressId(address.id);
    setData('customer_address_id', address.id);
    setData('customer_name', address.recipient_name || auth.user?.name || '');
    handleCustomerPhoneChange(address.no_hp ?? auth.user?.no_tel ?? '');
    setData('customer_address', address.address);
  };

  const selectedCustomerAddress = auth.customerAddresses.find((address) => address.id === selectedAddressId)
    ?? defaultCustomerAddress;

  const loadCatalog = useCallback(async (nextOffset: number, reset: boolean, search: string, tag = catalogTag) => {
    catalogAbortRef.current?.abort();
    const controller = new AbortController();
    catalogAbortRef.current = controller;
    setCatalogLoading(true);
    setCatalogError(false);

    try {
      const url = new URL(route('api.designs.index'), window.location.origin);
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
  }, [catalogTag]);

  const openDesignPicker = () => {
    if (isRepeatOrder) return;

    setIsDesignPickerOpen(true);
    setCatalogPreview(null);
    setProjectPreview(null);
    if (!catalogLoaded && !catalogLoading) void loadCatalog(0, true, '');
  };

  const closeDesignPicker = () => {
    catalogAbortRef.current?.abort();
    setCatalogPreview(null);
    setProjectPreview(null);
    setIsDesignPickerOpen(false);
  };

  const clearDesignPreviews = () => {
    designPreviews.forEach((preview) => {
      if (preview.url) URL.revokeObjectURL(preview.url);
    });
    setDesignPreviews([]);
  };

  const chooseDesign = (design: DesignOption) => {
    if (isRepeatOrder) return;

    setSelectedDesign(design.id);
    setSelectedDesignInfo(design);
    setSelectedProject(null);
    setSelectedPreviousOrderDesign(null);
    setData('design_id', design.id);
    setData('project_id', null);
    setData('previous_order_item_id', null);
    setData('customer_design_image', null);
    setData('customer_design_images', []);
    clearDesignPreviews();
    closeDesignPicker();
  };

  const chooseProject = (project: ProjectOption) => {
    if (isRepeatOrder) return;

    setSelectedDesign('project');
    setSelectedDesignInfo(null);
    setSelectedProject(project);
    setSelectedPreviousOrderDesign(null);
    setData('design_id', null);
    setData('project_id', project.id);
    setData('previous_order_item_id', null);
    setData('customer_address_id', project.customer_address_id);
    setData('customer_design_image', null);
    setData('customer_design_images', []);
    clearDesignPreviews();
    closeDesignPicker();
  };

  const handleCatalogDesignClick = (design: DesignOption) => {
    if (design.image_url) {
      setCatalogPreview(design);
      return;
    }

    chooseDesign(design);
  };

  const chooseCustomDesign = () => {
    if (isRepeatOrder) return;

    setSelectedDesign('custom');
    setSelectedDesignInfo(null);
    setSelectedProject(null);
    setSelectedPreviousOrderDesign(null);
    setData('design_id', null);
    setData('project_id', null);
    setData('previous_order_item_id', null);
  };

  const choosePreviousOrderDesign = (previousDesign: PreviousOrderDesign) => {
    if (isRepeatOrder) return;

    const matchingSize = previousDesign.size_id !== null && sizes.some((size) => size.id === previousDesign.size_id)
      ? previousDesign.size_id
      : null;

    setSelectedDesign('previous');
    setSelectedDesignInfo(null);
    setSelectedProject(null);
    setSelectedPreviousOrderDesign(previousDesign);
    setCustomDesc('');
    setConfiguredSize(matchingSize);
    setRequestCustomSize(matchingSize === null);
    setCustomSizeDesc(matchingSize === null ? (previousDesign.requested_size ?? previousDesign.size_name) : '');
    setQuantity(previousDesign.quantity || 100);
    setCutType(previousDesign.cut_type === 'die-cut' ? 'die-cut' : 'standard');
    setData('design_id', null);
    setData('project_id', null);
    setData('previous_order_item_id', previousDesign.id);
    setData('custom_description', '');
    setData('requested_size', matchingSize === null ? (previousDesign.requested_size ?? previousDesign.size_name) : '');
    setData('quantity', previousDesign.quantity || 100);
    setData('cut_type', previousDesign.cut_type === 'die-cut' ? 'die-cut' : 'standard');
    setData('customer_design_image', null);
    setData('customer_design_images', []);
    clearDesignPreviews();
    closeDesignPicker();
  };

  const handleCatalogSearch = () => {
    void loadCatalog(0, true, catalogSearch.trim(), catalogTag);
  };

  const catalogSearchReady = useRef(false);

  useEffect(() => {
    if (!isDesignPickerOpen) {
      catalogSearchReady.current = false;
      return;
    }

    if (!catalogSearchReady.current) {
      catalogSearchReady.current = true;
      return;
    }

    const timeout = window.setTimeout(() => {
      void loadCatalog(0, true, catalogSearch.trim(), catalogTag);
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [catalogSearch, catalogTag, isDesignPickerOpen, loadCatalog]);

  const handleCatalogTag = (tag: string | null) => {
    setCatalogTag(tag);
  };

  const hasMoreCatalogDesigns = catalogOffset < catalogTotal;

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

        if (projectPreview) {
          setProjectPreview(null);
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
  }, [catalogPreview, projectPreview, isDesignPickerOpen]);

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

  const shapeOptions = useMemo(() => {
    return Array.from(new Set(['Bulat', 'Segi Empat Sama', 'Petak', ...sizes.map((size) => shapeLabel(size.shape))]))
      .sort((first, second) => first.localeCompare(second, 'ms'));
  }, [sizes]);
  const selectedSizeInputMode = selectedShape ? sizeInputMode(selectedShape) : 'rectangle';
  const dimensionsComplete = Boolean(selectedShape)
    && positiveDimension(sizePrimary) !== null
    && (selectedSizeInputMode !== 'rectangle' || positiveDimension(sizeSecondary) !== null);
  const isLegacyCustomSize = requestCustomSize && selectedShape === '' && selectedSize === null;
  const matchingSize = useMemo(
    () => findSizeForDimensions(sizes, selectedShape, sizePrimary, sizeSecondary),
    [selectedShape, sizePrimary, sizeSecondary, sizes],
  );
  const dimensionSummary = dimensionDescription(selectedShape, sizePrimary, sizeSecondary);
  const requestedDimensionSummary = selectedShape && dimensionSummary
    ? `${selectedShape}: ${dimensionSummary}`
    : '';

  const handleShapeChange = (shape: string) => {
    if (isRepeatOrder) return;

    setRequestCustomSize(false);
    setCustomSizeDesc('');
    setSelectedShape(shape);
    setSelectedSize(null);
    setSizePrimary('');
    setSizeSecondary('');
    setData('size_id', null);
    setData('requested_size', '');
  };

  const handleDimensionChange = (field: 'primary' | 'secondary', value: string) => {
    const nextPrimary = field === 'primary' ? value : sizePrimary;
    const nextSecondary = field === 'secondary' ? value : sizeSecondary;

    if (field === 'primary') setSizePrimary(value);
    if (field === 'secondary') setSizeSecondary(value);

    const size = findSizeForDimensions(sizes, selectedShape, nextPrimary, nextSecondary);
    setSelectedSize(size?.id ?? null);
    setData('size_id', size?.id ?? null);
    const nextDimensionSummary = dimensionDescription(selectedShape, nextPrimary, nextSecondary);
    setData('requested_size', size ? '' : (selectedShape && nextDimensionSummary ? `${selectedShape}: ${nextDimensionSummary}` : ''));
  };

  const selectedSizeObj = useMemo(() => sizes.find((s) => s.id === selectedSize) ?? null, [sizes, selectedSize]);
  const isDieCutTooSmall = cutType === 'die-cut' && selectedSizeObj && Math.max(selectedSizeObj.width_cm, selectedSizeObj.height_cm) < 5;
  const currentItemHasDesign = typeof selectedDesign === 'number'
    || (selectedDesign === 'project' && selectedProject !== null)
    || (selectedDesign === 'previous' && selectedPreviousOrderDesign !== null)
    || isRepeatOrder
    || data.customer_design_images.length > 0;
  const currentItemMinimumA3Sheets = minimumA3Sheets(currentItemHasDesign, minimumA3SheetsWithoutDesign);

  const priceCalculation = useMemo(() => {
    if (isLegacyCustomSize || !selectedSize || !selectedSizeObj) return null;

    const qtyPerA3 = selectedSizeObj.qty_per_a3;
    if (!qtyPerA3) return null;

    const a3Sheets = calculateBillableA3Sheets(quantity, qtyPerA3, currentItemHasDesign, minimumA3SheetsWithoutDesign);

    const match = priceSettings.find(
      (ps) => ps.sticker_type === 'Mirrorcote'
        && a3Sheets >= ps.qty_from
        && (ps.qty_to === null || a3Sheets <= ps.qty_to)
    );

    if (!match) return null;

    const pricePerA3 = Number(match.price_per_a3);
    if (!Number.isFinite(pricePerA3)) return null;

    return {
      a3Sheets,
      pricePerA3,
      total: a3Sheets * pricePerA3,
    };
  }, [selectedSize, selectedSizeObj, quantity, priceSettings, isLegacyCustomSize, currentItemHasDesign, minimumA3SheetsWithoutDesign]);

  const calculateOrderItemPrice = (item: OrderItemDraft) => {
    if (!item.size_id || item.requested_size.trim()) return null;

    const size = sizes.find((candidate) => candidate.id === item.size_id);
    const qtyPerA3 = size?.qty_per_a3;
    if (!qtyPerA3) return null;

    const hasDesign = item.design_id !== null
      || item.project_id !== null
      || item.previous_order_item_id !== null
      || item.customer_design_images.length > 0;
    const a3Sheets = calculateBillableA3Sheets(item.quantity, qtyPerA3, hasDesign, minimumA3SheetsWithoutDesign);
    const match = priceSettings.find(
      (priceSetting) => priceSetting.sticker_type === 'Mirrorcote'
        && a3Sheets >= priceSetting.qty_from
        && (priceSetting.qty_to === null || a3Sheets <= priceSetting.qty_to),
    );
    if (!match) return null;

    const pricePerA3 = Number(match.price_per_a3);
    if (!Number.isFinite(pricePerA3)) return null;

    return {
      a3Sheets,
      hasDesign,
      minimumA3Sheets: minimumA3Sheets(hasDesign, minimumA3SheetsWithoutDesign),
      total: a3Sheets * pricePerA3,
    };
  };

  const getCurrentOrderItem = (): OrderItemDraft => ({
    key: `order-item-${crypto.randomUUID()}`,
    design_id: typeof selectedDesign === 'number' ? selectedDesign : null,
    project_id: selectedDesign === 'project' ? selectedProject?.id ?? null : null,
    custom_description: customDesc,
    size_id: isLegacyCustomSize ? null : selectedSize,
    requested_size: isLegacyCustomSize ? customSizeDesc : (selectedSize ? '' : requestedDimensionSummary),
    quantity,
    cut_type: cutType,
    customer_design_images: data.customer_design_images,
    previous_order_item_id: isRepeatOrder
      ? repeatItem?.id ?? null
      : selectedDesign === 'previous' ? selectedPreviousOrderDesign?.id ?? null : null,
    design_name: typeof selectedDesign === 'number'
      ? selectedDesignInfo?.name ?? 'Design katalog'
      : selectedDesign === 'project'
        ? selectedProject?.title ?? 'Design project'
        : selectedDesign === 'previous'
          ? selectedPreviousOrderDesign?.title ?? 'Design order terdahulu'
        : 'Design custom',
  });

  const currentOrderItem = getCurrentOrderItem();
  const currentOrderItemHasContent = selectedDesign === 'previous'
    || typeof selectedDesign === 'number'
    || Boolean(selectedProject)
    || customDesc.trim().length > 0
    || data.customer_design_images.length > 0;
  const orderItems = canAddItems
    ? [...savedOrderItems, ...(currentOrderItemHasContent ? [currentOrderItem] : [])]
    : [];
  const orderItemPrices = orderItems.map(calculateOrderItemPrice);
  const orderTotal = orderItems.length > 0 && orderItemPrices.every((price) => price !== null)
    ? orderItemPrices.reduce((total, price) => total + (price?.total ?? 0), 0)
    : null;
  const summarySubtotal = canAddItems ? orderTotal : priceCalculation?.total ?? null;
  const repeatDetailsUnchanged = Boolean(
    repeatOrder
      && repeatOrder.shipping_free_forever
      && repeatOrder.items.length === 1
      && repeatItem
      && quantity === repeatItem.quantity
      && Number(data.size_id ?? 0) === Number(repeatItem.sticker_size_id ?? 0)
      && data.requested_size.trim() === (repeatItem.requested_size ?? '').trim()
      && data.customer_design_images.length === 0
      && (
        repeatItem.sticker_design_id !== null
          ? (Number(data.design_id ?? 0) === repeatItem.sticker_design_id
            || (data.design_id === null && data.previous_order_item_id === repeatItem.id))
          : repeatItem.customer_project_id !== null
            ? Number(data.project_id ?? 0) === repeatItem.customer_project_id
            : data.design_id === null
              && data.project_id === null
              && (
                data.custom_description.trim() === (repeatItem.custom_design_description ?? '').trim()
                || (data.custom_description.trim() === '' && data.previous_order_item_id === repeatItem.id)
              )
      )
  );
  const summaryShippingFee = summarySubtotal === null
    ? null
    : (adminMode && data.shipping_free_forever) || repeatDetailsUnchanged || summarySubtotal >= 150
      ? 0
      : data.shipping_region === 'sabah_sarawak' ? 12 : 7;
  const summaryTotal = summarySubtotal === null || summaryShippingFee === null
    ? null
    : summarySubtotal + summaryShippingFee;
  const currentItemSizeValid = isLegacyCustomSize
    ? customSizeDesc.trim().length > 0
    : selectedSize !== null || dimensionsComplete;
  const currentOrderItemValid = !currentOrderItemHasContent
    || currentItemSizeValid && !isDieCutTooSmall;

  const resetCurrentOrderItem = () => {
    setSelectedDesign('custom');
    setSelectedDesignInfo(null);
    setSelectedProject(null);
    setSelectedPreviousOrderDesign(null);
    setCustomDesc('');
    setConfiguredSize(null);
    setQuantity(100);
    setRequestCustomSize(false);
    setCustomSizeDesc('');
    setCutType('standard');
    clearDesignPreviews();
    setData('design_id', null);
    setData('project_id', null);
    setData('previous_order_item_id', null);
    setData('custom_description', '');
    setData('size_id', null);
    setData('requested_size', '');
    setData('quantity', 100);
    setData('cut_type', 'standard');
    setData('customer_design_image', null);
    setData('customer_design_images', []);
  };

  const handleAddOrderItem = () => {
    if (!currentOrderItemHasContent) {
      setSubmitErrorMessages(['Pilih design atau isi keterangan design sebelum menambah item.']);
      return;
    }

    if (!isLegacyCustomSize && selectedSize === null && !dimensionsComplete) {
      setSubmitErrorMessages(['Pilih bentuk dan lengkapkan dimensi untuk item ini sebelum menambah item lain.']);
      return;
    }

    if (isLegacyCustomSize && customSizeDesc.trim().length === 0) {
      setSubmitErrorMessages(['Masukkan saiz custom untuk item ini sebelum menambah item lain.']);
      return;
    }

    if (isDieCutTooSmall) {
      setSubmitErrorMessages(['Potong ikut bentuk hanya boleh untuk saiz 5cm ke atas.']);
      return;
    }

    setSavedOrderItems((items) => [...items, currentOrderItem]);
    resetCurrentOrderItem();
    setSubmitErrorMessages([]);

    if (adminMode) {
      setItemAddedSuccess(true);
      requestAnimationFrame(() => {
        addedItemsSectionRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }
  };

  const handleRemoveOrderItem = (index: number) => {
    setSavedOrderItems((items) => items.filter((_, itemIndex) => itemIndex !== index));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitErrorMessages([]);

    if (canAddItems && orderItems.length === 0) {
      setSubmitErrorMessages(['Tambah sekurang-kurangnya satu item ke dalam order.']);
      return;
    }

    if (adminMode && !selectedAdminCustomer) {
      setSubmitErrorMessages(['Pilih customer sebelum menghantar order.']);
      return;
    }

    if (canAddItems && !currentOrderItemValid) {
      setSubmitErrorMessages(['Lengkapkan saiz untuk item semasa sebelum menghantar order.']);
      return;
    }

    const itemPayload = orderItems.map(({ key: _key, design_name: _designName, ...item }) => item);
    transform((form) => canAddItems ? {
      ...form,
      items: itemPayload,
      customer_design_image: null,
      customer_design_images: [],
    } : form);
    post(route(adminMode ? 'admin.orders.store' : 'orders.store'), {
      forceFormData: canAddItems,
      onSuccess: clearOrderDraft,
      onError: (validationErrors) => {
        const messages = Object.values(validationErrors).filter(
          (message): message is string => typeof message === 'string' && message.length > 0,
        );
        setSubmitErrorMessages(messages.length > 0 ? Array.from(new Set(messages)) : ['Sila semak maklumat tempahan dan cuba lagi.']);
      },
    });
  };

  const pageContent = (
    <>
      <Head title={adminMode ? 'Tambah Order' : 'Tempah Sticker'} />
      {!memberMode && !adminMode && <PublicHeader active="design" />}
      <div className="frontend-shell min-h-screen pb-20">
        <div className="mx-auto max-w-[1280px] px-4 py-10 lg:px-8">
          <h1 className="text-2xl font-extrabold tracking-tight text-slate-900">{adminMode ? 'Tambah Order' : 'Tempah Sticker'}</h1>
          <p className="mt-2 text-sm text-slate-500">{adminMode ? 'Buat tempahan untuk customer dari panel admin.' : 'Pilih design, saiz & kuantiti sticker anda.'}</p>

          {!adminMode && (
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
                target={WHATSAPP_TARGET}
                aria-label="WhatsApp admin untuk bantuan tempahan"
                className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-600 active:scale-[0.98] sm:w-auto sm:shrink-0"
              >
                <MessageCircle className="h-4 w-4" />
                WhatsApp Admin
              </a>
            </div>
          )}

          {draftRestored && (
            <div className="mt-4 flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800" role="status">
              <Info className="mt-0.5 h-4 w-4 shrink-0" />
              <p>Draft tempahan anda telah dipulihkan. Jika ada fail design, sila pilih semula sebelum menghantar.</p>
            </div>
          )}

          {repeatOrder && (
            <div className="mt-4 flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-3.5">
              <RotateCcw className="h-5 w-5 shrink-0 text-brand-600" />
              <div>
                <p className="text-sm font-bold text-brand-900">Ulang Tempahan {repeatOrder.order_no}</p>
                <p className="text-xs text-brand-700">
                  Butiran dari tempahan lama telah diisi. Sila semak dan ubah jika perlu.
                  {repeatOrder.shipping_free_forever && ' Kekalkan design, saiz dan kuantiti yang sama untuk menikmati free pos selamanya.'}
                </p>
              </div>
            </div>
          )}

          <form onSubmit={handleSubmit} className="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            {/* Left: Design Selection */}
            <div className="lg:col-span-2 space-y-8">
              {canAddItems && savedOrderItems.length > 0 && (
                <section ref={addedItemsSectionRef} className="frontend-flat-card scroll-mt-24 p-6">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">Order berbilang item</p>
                      <h2 className="mt-1 text-lg font-bold text-slate-900">Item yang telah ditambah</h2>
                    </div>
                    <span className="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700">{savedOrderItems.length} item</span>
                  </div>
                  <div className="mt-4 space-y-3">
                    {savedOrderItems.map((item, index) => {
                      const price = calculateOrderItemPrice(item);
                      const size = sizes.find((candidate) => candidate.id === item.size_id);

                      return (
                        <div key={item.key} className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                          <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-sm font-bold text-brand-700">{index + 1}</div>
                          <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-bold text-slate-900">{item.design_name}</p>
                            <p className="mt-0.5 text-xs text-slate-500">
                               {item.requested_size || size?.name || 'Saiz custom'} • {item.quantity} pcs
                              {price ? ` • ${price.a3Sheets} helai A3` : ''}
                            </p>
                            {price && !price.hasDesign && (
                              <p className="mt-1 text-[11px] font-semibold text-amber-700">Minimum {minimumA3SheetsWithoutDesign} helai A3 kerana tiada design.</p>
                            )}
                          </div>
                          <p className="shrink-0 text-sm font-bold text-brand-700">{price ? `RM ${price.total.toFixed(2)}` : 'Pending'}</p>
                          <button
                            type="button"
                            onClick={() => handleRemoveOrderItem(index)}
                            aria-label={`Buang item ${index + 1}`}
                            className="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      );
                    })}
                  </div>
                </section>
              )}

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
                    disabled={isRepeatOrder}
                    className={`flex w-full items-center gap-4 rounded-2xl border-2 bg-white p-3 text-left transition ${
                      isRepeatOrder
                        ? 'cursor-default border-emerald-200 bg-emerald-50/40'
                        : 'border-slate-200 hover:border-brand-300 hover:bg-brand-50/40'
                    }`}
                  >
                    <div className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white">
                      {selectedPreviousOrderDesign?.preview_url ? (
                        <img
                          src={selectedPreviousOrderDesign.preview_url}
                          alt=""
                          className="h-full w-full object-contain"
                        />
                      ) : selectedProject?.preview_url ? (
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
                            : selectedDesign === 'previous'
                              ? selectedPreviousOrderDesign?.title ?? 'Design order terdahulu'
                            : selectedDesignInfo?.name ?? 'Pilih daripada katalog'}
                      </p>
                      <p className="mt-0.5 text-xs text-slate-500">
                        {isRepeatOrder
                          ? `Design asal daripada order ${repeatOrder.order_no}`
                          : selectedDesign === 'project'
                          ? 'Design yang pernah dibuat'
                          : selectedDesign === 'previous'
                            ? `${selectedPreviousOrderDesign?.size_name ?? 'Saiz terdahulu'} • ${selectedPreviousOrderDesign?.quantity ?? quantity} pcs`
                            : selectedDesignInfo?.category ?? 'Katalog design sticker'}
                      </p>
                    </div>
                    <span className={`shrink-0 rounded-xl px-3 py-2 text-xs font-bold ${isRepeatOrder ? 'bg-emerald-100 text-emerald-700' : 'bg-brand-50 text-brand-700'}`}>
                      {isRepeatOrder ? 'Design asal' : 'Pilih Design'}
                    </span>
                  </button>

                  {!isRepeatOrder && (
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
                        Saya perlukan custom design
                      </span>
                      {selectedDesign === 'custom' && <Check className="h-4 w-4 text-brand-600" />}
                    </button>
                  )}
                </div>
                <p className="mt-3 text-xs text-slate-400">
                  {isRepeatOrder
                    ? `Design dan bentuk dikekalkan daripada order ${repeatOrder.order_no}. Anda hanya boleh ubah saiz dan kuantiti.`
                    : 'Katalog dibuka apabila diperlukan. Imej dimuatkan secara berperingkat.'}
                </p>

                {selectedDesign === 'custom' && (
                  <div className="mt-4">
                    <label htmlFor="custom-desc" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Keterangan Design</label>
                    <textarea
                      id="custom-desc"
                      value={customDesc}
                      onChange={(e) => { setCustomDesc(e.target.value); setData('custom_description', e.target.value); }}
                      readOnly={isRepeatOrder}
                      rows={3}
                      className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                      placeholder="Terangkan design yang anda mahukan..."
                    />
                  </div>
                )}

                   <div className={`mt-5 ${isRepeatOrder ? 'opacity-60' : ''}`}>
                   <label htmlFor="design-upload" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Hantar Design Sendiri (Pilihan)</label>
                   <div className="mt-2 space-y-3">
                     {designPreviews.length > 0 ? (
                       <div className="flex flex-wrap gap-3">
                         {designPreviews.map((preview) => (
                           <div key={preview.id} className="w-24">
                             <div className="flex h-20 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                               {preview.url ? (
                                 <img src={preview.url} alt={`Preview ${preview.name}`} className="h-full w-full object-contain" />
                               ) : (
                                 <ImageIcon className="h-8 w-8 text-slate-300" />
                               )}
                             </div>
                              <Tooltip>
                                <TooltipTrigger asChild>
                                  <p className="mt-1 truncate text-[11px] text-slate-500">{preview.name}</p>
                                </TooltipTrigger>
                                <TooltipContent>{preview.name}</TooltipContent>
                              </Tooltip>
                           </div>
                         ))}
                       </div>
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
                          multiple
                          disabled={isRepeatOrder}
                         onChange={(e) => {
                           const files = Array.from(e.target.files ?? []);
                           clearDesignPreviews();
                            setData('customer_design_image', null);
                            setData('customer_design_images', files);
                            setData('previous_order_item_id', null);
                           setDesignPreviews(files.map((file) => ({
                             id: crypto.randomUUID(),
                             name: file.name,
                             url: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
                           })));
                           if (files.length > 0) {
                              setSelectedDesign('custom');
                              setSelectedDesignInfo(null);
                              setSelectedProject(null);
                              setSelectedPreviousOrderDesign(null);
                             setData('design_id', null);
                             setData('project_id', null);
                           }
                         }}
                         className="text-sm"
                       />
                        <p className="mt-1 text-xs text-slate-400">
                          {isRepeatOrder
                            ? 'Design asal digunakan semula untuk tempahan ini.'
                            : 'JPG, PNG, PDF. Maks 10MB setiap fail. Boleh pilih lebih daripada satu design.'}
                        </p>
                     </div>
                   </div>
                  </div>

                  <div className={`mt-5 flex items-start gap-3 rounded-2xl border p-4 ${currentItemHasDesign ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'}`} role="note">
                    <Info className={`mt-0.5 h-5 w-5 shrink-0 ${currentItemHasDesign ? 'text-emerald-600' : 'text-amber-600'}`} />
                    <div>
                      <p className={`text-sm font-bold ${currentItemHasDesign ? 'text-emerald-900' : 'text-amber-900'}`}>
                        {currentItemHasDesign ? 'Design tersedia untuk cetakan.' : 'Belum ada design untuk dicetak.'}
                      </p>
                      <p className={`mt-1 text-xs leading-relaxed ${currentItemHasDesign ? 'text-emerald-800' : 'text-amber-800'}`}>
                        {currentItemHasDesign
                          ? `Jika design sudah siap${data.customer_design_images.length > 0 ? ' dan telah diupload' : ''}, minimum tempahan ialah ${currentItemMinimumA3Sheets} helai A3.`
                          : `Jika kami perlu sediakan design, minimum caj ialah ${currentItemMinimumA3Sheets} helai A3. Kuantiti pcs boleh diisi seperti biasa.`}
                      </p>
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
                    <div>
                         <label htmlFor="sticker-shape" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                           {isRepeatOrder ? '1. Bentuk asal' : '1. Pilih Jenis Bentuk'}
                         </label>
                          <select
                            id="sticker-shape"
                            value={selectedShape}
                            onChange={(event) => handleShapeChange(event.target.value)}
                            disabled={isRepeatOrder}
                            className={`mt-1 w-full rounded-xl border px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100 ${
                              isRepeatOrder ? 'cursor-not-allowed border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white'
                            }`}
                         >
                           <option value="">Pilih jenis sticker...</option>
                           {shapeOptions.map((shape) => (
                             <option key={shape} value={shape}>{shape}</option>
                           ))}
                         </select>
                       </div>

                      {selectedShape && (
                        <div className="rounded-2xl border border-brand-100 bg-brand-50/50 p-4">
                           <p className="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700">2. Masukkan Dimensi</p>
                           <div className="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                             <div className={selectedSizeInputMode !== 'rectangle' ? 'sm:col-span-2' : ''}>
                               <label htmlFor="size-primary" className="text-xs font-semibold text-slate-600">
                                 {selectedSizeInputMode === 'diameter'
                                   ? 'Diameter Sticker (cm)'
                                   : selectedSizeInputMode === 'length'
                                     ? 'Panjang Sticker (cm)'
                                     : 'Lebar Sticker (cm)'}
                               </label>
                               <input
                                 id="size-primary"
                                 type="number"
                                 min="0.01"
                                 step="0.01"
                                 inputMode="decimal"
                                  value={sizePrimary}
                                 onChange={(event) => handleDimensionChange('primary', event.target.value)}
                                 className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                                 placeholder={selectedSizeInputMode === 'diameter' ? 'Contoh: 5' : 'Contoh: 10'}
                               />
                             </div>

                             {selectedSizeInputMode === 'rectangle' && (
                               <div>
                                 <label htmlFor="size-secondary" className="text-xs font-semibold text-slate-600">Tinggi Sticker (cm)</label>
                                 <input
                                   id="size-secondary"
                                   type="number"
                                   min="0.01"
                                   step="0.01"
                                   inputMode="decimal"
                                    value={sizeSecondary}
                                   onChange={(event) => handleDimensionChange('secondary', event.target.value)}
                                   className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                                   placeholder="Contoh: 15"
                                 />
                               </div>
                             )}
                           </div>
                            {dimensionsComplete && !matchingSize ? (
                              <p className="mt-3 rounded-xl bg-amber-100 px-3 py-2 text-xs leading-relaxed text-amber-800">
                                Saiz ini akan disemak oleh admin sebelum harga dimuktamadkan.
                              </p>
                            ) : !dimensionsComplete ? (
                              <p className="mt-3 text-xs text-slate-500">
                                {selectedSizeInputMode === 'rectangle' ? 'Isi lebar dan tinggi sticker.' : `Isi ${selectedSizeInputMode === 'diameter' ? 'diameter' : 'panjang'} sticker.`}
                              </p>
                            ) : null}
                        </div>
                      )}
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
                  </div>
                </div>
              </section>

              {canAddItems && !isRepeatOrder && (
                <div className="rounded-2xl border border-dashed border-brand-300 bg-brand-50/50 p-4">
                  <button
                    type="button"
                    onClick={handleAddOrderItem}
                    className="flex w-full items-center justify-center gap-2 rounded-xl border border-brand-200 bg-white px-4 py-3 text-sm font-bold text-brand-700 transition hover:border-brand-400 hover:bg-brand-50"
                  >
                    <Plus className="h-4 w-4" />
                    Tambah Item ke Order
                  </button>
                   <p className="mt-2 text-center text-xs text-brand-700">Simpan item semasa, kemudian pilih design dan masukkan bentuk serta dimensi untuk item seterusnya.</p>
                </div>
              )}

              {/* Step 3: Customer / Member Account */}
              <section className="frontend-flat-card p-6">
                <div className="flex items-center gap-2">
                  <div className="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">3</div>
                  <h2 className="text-lg font-bold text-slate-900">{adminMode ? 'Pilih Customer' : auth.user ? 'Alamat Penghantaran' : 'Create Akaun'}</h2>
                </div>

                {adminMode ? (
                  <div className="mt-4 space-y-4">
                    <div>
                      <label htmlFor="admin-customer-search" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Customer</label>
                      <div className="relative mt-1">
                        {selectedAdminCustomer && !showAdminCustomerPicker ? (
                          <div className="flex items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3">
                            <div className="min-w-0">
                              <p className="truncate text-sm font-semibold text-slate-900">{selectedAdminCustomer.name}</p>
                              <p className="truncate text-xs text-slate-500">{selectedAdminCustomer.no_tel ?? selectedAdminCustomer.email ?? 'Tiada maklumat tambahan'}</p>
                            </div>
                            <button
                              type="button"
                              onClick={() => {
                                setAdminCustomerSearch('');
                                setShowAdminCustomerPicker(true);
                              }}
                              className="shrink-0 text-xs font-semibold text-brand-600 hover:underline"
                            >
                              Tukar
                            </button>
                          </div>
                        ) : (
                          <>
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input
                              id="admin-customer-search"
                              type="search"
                              value={adminCustomerSearch}
                              onChange={(event) => {
                                setAdminCustomerSearch(event.target.value);
                                setShowAdminCustomerPicker(true);
                              }}
                              onFocus={() => setShowAdminCustomerPicker(true)}
                              onBlur={() => window.setTimeout(() => setShowAdminCustomerPicker(false), 150)}
                              className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                              placeholder={selectedAdminCustomer ? `Cari customer lain... (${selectedAdminCustomer.name})` : 'Cari nama, emel atau nombor...'}
                              autoComplete="off"
                              role="combobox"
                              aria-expanded={showAdminCustomerPicker}
                              aria-controls="admin-customer-options"
                            />
                            {showAdminCustomerPicker && (
                              <div id="admin-customer-options" className="absolute z-30 mt-1 max-h-72 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                                {filteredAdminCustomers.length > 0 ? filteredAdminCustomers.map((customer) => {
                                  const address = customer.addresses.find((item) => item.is_default) ?? customer.addresses[0];
                                  const phone = address?.no_hp ?? customer.no_tel;

                                  return (
                                    <button
                                      key={customer.id}
                                      type="button"
                                      onMouseDown={(event) => event.preventDefault()}
                                      onClick={() => handleAdminCustomerChange(customer.id)}
                                      className={`flex w-full items-start gap-3 px-4 py-2.5 text-left transition hover:bg-brand-50 ${customer.id === selectedCustomerId ? 'bg-brand-50/70' : ''}`}
                                    >
                                      <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">
                                        {customer.name.charAt(0).toUpperCase()}
                                      </span>
                                      <span className="min-w-0">
                                        <span className="block truncate text-sm font-medium text-slate-900">{customer.name}</span>
                                        <span className="block truncate text-xs text-slate-500">{phone ?? customer.email ?? 'Tiada nombor'}</span>
                                      </span>
                                    </button>
                                  );
                                }) : (
                                  <p className="px-4 py-6 text-center text-sm text-slate-500">Tiada customer dijumpai.</p>
                                )}
                              </div>
                            )}
                          </>
                        )}
                      </div>
                      {errors.customer_id && <p className="mt-1 text-xs text-rose-600">{errors.customer_id}</p>}
                    </div>

                    {selectedAdminCustomer ? (
                      <>
                        {selectedAdminCustomer.addresses.length > 1 && (
                          <div>
                            <label htmlFor="admin-address" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alamat Customer</label>
                            <select
                              id="admin-address"
                              value={selectedAddressId ?? ''}
                              onChange={(event) => handleAdminAddressChange(Number(event.target.value))}
                              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                            >
                              {selectedAdminCustomer.addresses.map((address) => (
                                <option key={address.id} value={address.id}>
                                  {address.recipient_name ?? selectedAdminCustomer.name} - {address.address}
                                </option>
                              ))}
                            </select>
                          </div>
                        )}

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                          <div>
                            <label htmlFor="admin-customer-name" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nama Penghantaran</label>
                            <input
                              id="admin-customer-name"
                              type="text"
                              value={data.customer_name}
                              onChange={(event) => setData('customer_name', event.target.value)}
                              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                              required
                            />
                            {errors.customer_name && <p className="mt-1 text-xs text-rose-600">{errors.customer_name}</p>}
                          </div>
                          <div>
                            <label htmlFor="admin-customer-phone" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No. Telefon</label>
                            <input
                              id="admin-customer-phone"
                              type="tel"
                              value={data.customer_phone}
                              onChange={(event) => setData('customer_phone', event.target.value)}
                              className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                              required
                            />
                            {errors.customer_phone && <p className="mt-1 text-xs text-rose-600">{errors.customer_phone}</p>}
                          </div>
                        </div>

                        <div>
                          <label htmlFor="admin-customer-address" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Alamat Penghantaran</label>
                          <textarea
                            id="admin-customer-address"
                            value={data.customer_address}
                            onChange={(event) => setData('customer_address', event.target.value)}
                            rows={3}
                            className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                            required
                          />
                          {errors.customer_address && <p className="mt-1 text-xs text-rose-600">{errors.customer_address}</p>}
                        </div>
                      </>
                    ) : (
                      <div className="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Pilih customer untuk mengisi maklumat penghantaran.
                      </div>
                    )}
                  </div>
                ) : (
                  <>
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
                    <p className="text-sm leading-relaxed text-slate-500">Isi maklumat di bawah untuk daftar sebagai user dan tetapkan password sendiri.</p>

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
                          minLength={8}
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
                      disabled={registerProcessing || !(registerData.no_tel.trim() || data.customer_phone.trim()) || !registerData.password.trim()}
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
                  </>
                )}

                <div className="mt-5 border-t border-slate-100 pt-5">
                  <label htmlFor="shipping-region" className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                    Lokasi Penghantaran
                  </label>
                   <select
                     id="shipping-region"
                     value={data.shipping_region}
                    onChange={(event) => setData('shipping_region', event.target.value as 'peninsular' | 'sabah_sarawak')}
                    className="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 outline-none transition focus:border-brand-300 focus:ring-2 focus:ring-brand-100"
                  >
                    <option value="peninsular">Semenanjung Malaysia - RM7</option>
                     <option value="sabah_sarawak">Sabah &amp; Sarawak - RM12</option>
                   </select>
                   {adminMode && (
                    <label className="mt-3 flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
                      <input
                        type="checkbox"
                        checked={data.shipping_free_forever}
                        onChange={(event) => setData('shipping_free_forever', event.target.checked)}
                        className="mt-0.5 h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                      />
                      <span>
                        <span className="block font-semibold">Free Pos Selamanya</span>
                        <span className="mt-0.5 block text-xs text-emerald-700">Order ulangan dengan design, saiz dan kuantiti sama turut percuma pos.</span>
                      </span>
                    </label>
                   )}
                   <p className="mt-1 text-xs text-slate-400">Pos percuma untuk subtotal produk RM150 dan ke atas.</p>
                 </div>
              </section>
            </div>

            {/* Right: Summary */}
            <div className="lg:col-span-1">
              <div className="sticky top-24 frontend-flat-card p-6">
                <h3 className="text-lg font-bold text-slate-900">Ringkasan Tempahan</h3>

                {canAddItems ? (
                  <div className="mt-4 space-y-3">
                    {orderItems.length === 0 ? (
                      <p className="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">Belum ada item. Isi item semasa dan tekan "Tambah Item ke Order".</p>
                    ) : (
                      orderItems.map((item, index) => {
                        const price = calculateOrderItemPrice(item);
                        const size = sizes.find((candidate) => candidate.id === item.size_id);

                        return (
                          <div key={item.key} className="flex items-start justify-between gap-3 text-sm">
                            <div className="min-w-0">
                              <p className="truncate font-semibold text-slate-900">{index + 1}. {item.design_name}</p>
                              <p className="mt-0.5 text-xs text-slate-500">
                                {item.requested_size || size?.name || 'Saiz custom'} • {item.quantity} pcs
                                {price ? ` • ${price.a3Sheets} helai A3` : ''}
                              </p>
                              {price && !price.hasDesign && (
                                <p className="mt-1 text-[11px] font-semibold text-amber-700">Minimum {minimumA3SheetsWithoutDesign} helai A3 kerana tiada design.</p>
                              )}
                            </div>
                            <span className="shrink-0 font-medium text-slate-900">{price ? `RM ${price.total.toFixed(2)}` : 'Pending'}</span>
                          </div>
                        );
                      })
                    )}
                  </div>
                ) : (
                  <div className="mt-4 space-y-3">
                    <div className="flex justify-between text-sm">
                      <span className="text-slate-500">Design</span>
                      <span className="font-medium text-slate-900">
                         {selectedDesign === 'custom'
                           ? 'Custom'
                           : selectedDesign === 'previous'
                             ? selectedPreviousOrderDesign?.title ?? 'Design order terdahulu'
                             : selectedDesignInfo?.name ?? '-'}
                      </span>
                    </div>
                     <div className="flex justify-between text-sm">
                       <span className="text-slate-500">Saiz</span>
                       <span className="font-medium text-slate-900">
                          {isLegacyCustomSize
                           ? 'Custom'
                           : selectedShape
                             ? `${selectedShape}${selectedSizeObj?.name ? ` • ${selectedSizeObj.name}` : dimensionSummary ? ` • ${dimensionSummary}` : ''}`
                             : '-'}
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
                      <span className="text-slate-500">Minimum design</span>
                      <span className="font-medium text-slate-900">{currentItemMinimumA3Sheets} helai A3</span>
                    </div>
                    {data.customer_design_images.length > 0 && (
                      <div className="flex justify-between text-sm">
                        <span className="text-slate-500">Design Hantar</span>
                        <span className="font-medium text-emerald-600">Ya</span>
                      </div>
                    )}
                  </div>
                )}

                {!canAddItems && priceCalculation && (
                  <div className="mt-3 border-t border-slate-100 pt-3 space-y-1 text-xs text-slate-500">
                    <p>RM {priceCalculation.pricePerA3.toFixed(2)} × {priceCalculation.a3Sheets} A3</p>
                  </div>
                )}

                <div className="mt-1 space-y-2 border-t border-slate-100 pt-4">
                  {summarySubtotal !== null && summaryShippingFee !== null ? (
                    <>
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-slate-500">Subtotal produk</span>
                        <span className="font-medium text-slate-900">RM {summarySubtotal.toFixed(2)}</span>
                      </div>
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-slate-500">Pos</span>
                        <span className={`font-medium ${summaryShippingFee === 0 ? 'text-emerald-600' : 'text-slate-900'}`}>
                          {summaryShippingFee === 0 ? 'Percuma' : `RM ${summaryShippingFee.toFixed(2)}`}
                        </span>
                      </div>
                      <div className="flex items-center justify-between border-t border-slate-100 pt-3">
                        <span className="text-sm font-bold text-slate-900">Jumlah</span>
                        <span className="text-xl font-extrabold text-brand-600">RM {summaryTotal?.toFixed(2)}</span>
                      </div>
                    </>
                  ) : (
                    <div className="flex items-center justify-between">
                      <span className="text-sm font-bold text-slate-900">Jumlah</span>
                      <span className="text-xl font-extrabold text-brand-600">Pending</span>
                    </div>
                  )}
                  {((canAddItems && orderTotal === null && orderItems.length > 0)
                     || (!canAddItems && (isLegacyCustomSize || !selectedSizeObj?.qty_per_a3))) && (
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
                      href={route('member.login', { from_order: 1 })}
                      onClick={saveOrderDraft}
                      className="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-brand-700 shadow-lg shadow-brand-600/20"
                    >
                      Log Masuk untuk Mula Tempahan
                      <ChevronRight className="h-4 w-4" />
                    </Link>
                  </div>
                ) : (
                  <button
                    type="submit"
                    disabled={processing
                      || (adminMode && !selectedCustomerId)
                        || (canAddItems ? orderItems.length === 0 || !currentOrderItemValid : (!selectedDesign && !customDesc) || (!isLegacyCustomSize && !selectedSize && !dimensionsComplete) || (isLegacyCustomSize && !customSizeDesc.trim()) || !!isDieCutTooSmall)}
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

          {itemAddedSuccess && (
            <div className="fixed inset-0 z-[80] flex items-center justify-center p-4" role="presentation">
              <button
                type="button"
                aria-label="Tutup mesej berjaya"
                className="absolute inset-0 bg-slate-950/60"
                onClick={() => setItemAddedSuccess(false)}
              />
              <div
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="item-added-title"
                aria-describedby="item-added-description"
                className="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl"
              >
                <div className="flex items-start gap-3">
                  <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <Check className="h-6 w-6" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <h2 id="item-added-title" className="text-lg font-bold text-slate-900">Item berjaya ditambah</h2>
                    <p id="item-added-description" className="mt-1 text-sm text-slate-500">Item telah disimpan dalam order. Senarai item berada di bahagian atas.</p>
                  </div>
                  <button
                    type="button"
                    onClick={() => setItemAddedSuccess(false)}
                    aria-label="Tutup mesej berjaya"
                    className="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>
                <button
                  type="button"
                  onClick={() => setItemAddedSuccess(false)}
                  className="mt-5 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-700"
                >
                  Lihat Item
                </button>
              </div>
            </div>
          )}

          {isDesignPickerOpen && !isRepeatOrder && (
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
                  <div>
                    <div className="relative min-w-0 flex-1">
                      <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                      <input
                        type="search"
                        value={catalogSearch}
                        onChange={(event) => setCatalogSearch(event.target.value)}
                        aria-label="Cari design"
                        placeholder="Cari nama design..."
                        className="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-900 outline-none transition focus:border-brand-300 focus:bg-white focus:ring-2 focus:ring-brand-100"
                      />
                    </div>
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
                        <p className="mt-1 text-xs text-brand-700">Klik gambar untuk lihat preview atau pilih design ini untuk buat order print.</p>
                        <div className="mt-3 grid gap-2 sm:grid-cols-2">
                          {previousProjects.map((project) => (
                            <div
                              key={project.id}
                              className={`flex min-w-0 items-center gap-2 rounded-xl border-2 bg-white px-3 py-2 transition ${
                                selectedDesign === 'project' && selectedProject?.id === project.id
                                  ? 'border-brand-600'
                                  : 'border-brand-100'
                              }`}
                            >
                              {project.preview_url ? (
                                <button
                                  type="button"
                                  onClick={() => setProjectPreview(project)}
                                  aria-label={`Lihat preview ${project.title}`}
                                  className="flex h-9 w-9 shrink-0 cursor-zoom-in items-center justify-center overflow-hidden rounded-lg bg-brand-50 text-brand-600 transition hover:ring-2 hover:ring-brand-300"
                                >
                                  <img src={project.preview_url} alt={`Preview ${project.title}`} className="h-full w-full object-contain" />
                                </button>
                              ) : (
                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                  <FolderKanban className="h-4 w-4" />
                                </span>
                              )}
                              <button
                                type="button"
                                onClick={() => chooseProject(project)}
                                aria-pressed={selectedDesign === 'project' && selectedProject?.id === project.id}
                                className="flex min-w-0 flex-1 items-center gap-2 text-left"
                              >
                                <span className="min-w-0 flex-1">
                                  <span className="block truncate text-xs font-semibold text-slate-700">{project.title}</span>
                                  <span className="block truncate text-[10px] text-slate-500">Design sendiri / project customer</span>
                                </span>
                                {selectedDesign === 'project' && selectedProject?.id === project.id && <Check className="h-3.5 w-3.5 shrink-0 text-brand-600" />}
                              </button>
                            </div>
                          ))}
                        </div>
                    </div>
                  )}

                   {previousOrderDesigns.length > 0 && (
                     <div className="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
                       <div className="flex items-center gap-2">
                         <ImageIcon className="h-4 w-4 text-emerald-600" />
                         <p className="text-sm font-bold text-emerald-900">Gambar order terdahulu</p>
                       </div>
                       <p className="mt-1 text-xs text-emerald-800">Pilih gambar yang pernah ditempah. Saiz dan kuantiti akan diisi daripada order tersebut.</p>
                       <div className="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                         {previousOrderDesigns.map((previousDesign) => (
                           <button
                              key={`${previousDesign.id}-${previousDesign.preview_index}`}
                             type="button"
                             onClick={() => choosePreviousOrderDesign(previousDesign)}
                             className={`overflow-hidden rounded-2xl border-2 bg-white text-left transition ${
                                selectedDesign === 'previous'
                                  && selectedPreviousOrderDesign?.id === previousDesign.id
                                  && selectedPreviousOrderDesign?.preview_index === previousDesign.preview_index
                                 ? 'border-emerald-600 shadow-sm shadow-emerald-600/10'
                                 : 'border-emerald-100 hover:border-emerald-300'
                             }`}
                           >
                             <div className="aspect-square bg-slate-50">
                               <img
                                 src={previousDesign.preview_url}
                                 alt={previousDesign.title}
                                 loading="lazy"
                                 decoding="async"
                                 className="h-full w-full object-contain"
                               />
                             </div>
                             <span className="block min-w-0 px-2.5 py-2">
                               <span className="block truncate text-xs font-bold text-slate-800">{previousDesign.title}</span>
                               <span className="mt-1 block truncate text-[10px] text-slate-500">{previousDesign.size_name} • {previousDesign.quantity} pcs</span>
                               <span className="mt-0.5 block truncate text-[10px] text-slate-400">{previousDesign.order_no ?? 'Order terdahulu'}</span>
                             </span>
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

              {projectPreview && (
                <div className="absolute inset-0 z-20 flex items-end justify-center bg-slate-950/50 p-4 sm:items-center">
                  <button
                    type="button"
                    aria-label="Tutup preview design project"
                    className="absolute inset-0 cursor-default"
                    onClick={() => setProjectPreview(null)}
                  />
                  <div
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="project-preview-title"
                    className="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
                  >
                    <div className="relative bg-white">
                      <img
                        src={projectPreview.preview_url ?? ''}
                        alt={`Preview design ${projectPreview.title}`}
                        width="900"
                        height="900"
                        loading="eager"
                        decoding="async"
                        className="aspect-square max-h-[58dvh] w-full object-contain"
                      />
                      <button
                        type="button"
                        onClick={() => setProjectPreview(null)}
                        aria-label="Tutup preview design project"
                        className="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-md transition hover:bg-white hover:text-slate-900"
                      >
                        <X className="h-4 w-4" />
                      </button>
                    </div>
                    <div className="p-4">
                      <p id="project-preview-title" className="text-base font-bold text-slate-900">
                        {projectPreview.title}
                      </p>
                      <p className="mt-1 text-xs text-slate-500">
                        Preview design yang pernah dibuat.
                      </p>
                      <div className="mt-4 flex gap-2">
                        <button
                          type="button"
                          onClick={() => chooseProject(projectPreview)}
                          className="flex-1 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-700"
                        >
                          Pilih Design
                        </button>
                        <button
                          type="button"
                          onClick={() => setProjectPreview(null)}
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
    </>
  );

  return adminMode ? (
    <AdminLayout>{pageContent}</AdminLayout>
  ) : memberMode ? (
    <MemberLayout>{pageContent}</MemberLayout>
  ) : (
    <FrontendLayout hideNavbar>{pageContent}</FrontendLayout>
  );
}
