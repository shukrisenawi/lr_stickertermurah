export interface User {
  id: number;
  name: string;
  no_tel: string | null;
  email: string | null;
  must_change_password: boolean;
  is_admin: boolean;
  avatar_url: string | null;
}

export interface CustomerAddress {
  id: number;
  recipient_name: string;
  address: string;
  no_hp: string | null;
  is_default: boolean;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  designs?: StickerDesign[];
}

export interface StickerDesign {
  id: number;
  name: string;
  category_id: number;
  image_url: string;
  mobile_image_url?: string | null;
  description?: string;
  is_featured: boolean;
  created_at: string;
  updated_at: string;
}

export interface StickerSize {
  id: number;
  name: string;
  width_mm: number;
  height_mm: number;
  created_at: string;
  updated_at: string;
}

export interface OrderItem {
  id: number;
  order_id: number;
  sticker_design_id: number;
  sticker_size_id: number;
  quantity: number;
  unit_price: number;
  subtotal: number;
  design?: StickerDesign;
  size?: StickerSize;
  created_at: string;
  updated_at: string;
}

export type OrderStatus = 'pending' | 'processing' | 'shipped' | 'completed' | 'cancelled';

export interface Order {
  id: number;
  order_no: string;
  customer_name: string;
  customer_phone: string;
  customer_address: string;
  material: string;
  status: OrderStatus;
  tracking_no: string | null;
  custom_request: string | null;
  payment_receipt_path: string | null;
  subtotal: number;
  total: number;
  repeat_from_order_id: number | null;
  created_at: string;
  updated_at: string;
  items?: OrderItem[];
  invoice?: Invoice;
  user?: User;
}

export interface Invoice {
  id: number;
  order_id: number;
  invoice_no: string;
  amount: number;
  status: string;
  issue_date: string;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface PageProps {
  auth: {
    user: User | null;
    customerAddresses: CustomerAddress[];
    impersonating: boolean;
  };
  flash: {
    success: string | null;
    error: string | null;
    info: string | null;
  };
  app: {
    name: string;
    env: string;
    logo_url: string;
    whatsapp_phone: string;
    admin_email: string;
  };
  seo: {
    title: string;
    description: string;
    robots: string;
    canonical: string;
    site_name: string;
    og_type: string;
    og_image: string;
    og_image_alt: string;
    locale: string;
    structured_data: Record<string, unknown> | null;
  };
  invoiceCounts: {
    adminPending: number;
    memberUnpaid: number;
  };
  orderCounts: {
    adminPending: number;
  };
  adminNotifications: Array<{
    key: string;
    label: string;
    count: number;
    href: string;
  }>;
  testimonialCounts: {
    adminPending: number;
    approved: number;
  };
  [key: string]: unknown;
}
