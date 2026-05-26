export interface User {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  avatar_url: string | null;
}

export interface CustomerAddress {
  id: number;
  label: string;
  address: string;
  phone: string;
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

export interface StickerPriceTier {
  id: number;
  sticker_size_id: number;
  min_qty: number;
  max_qty: number | null;
  price_per_unit: number;
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
  created_at: string;
  updated_at: string;
}

export interface PageProps {
  auth: {
    user: User | null;
    customerAddresses: CustomerAddress[];
  };
  flash: {
    success: string | null;
    error: string | null;
  };
  app: {
    name: string;
    env: string;
  };
  [key: string]: unknown;
}
