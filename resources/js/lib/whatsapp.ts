export const WHATSAPP_TARGET = 'stickertermurah-whatsapp-web';

export function normalizeWhatsAppPhone(phone: string): string {
  let digits = phone.replace(/\D/g, '');

  if (digits.startsWith('00')) {
    digits = digits.slice(2);
  }

  if (digits.startsWith('0')) {
    digits = `60${digits.slice(1)}`;
  }

  return digits;
}

export function whatsappWebUrl(phone: string, message?: string): string {
  const params = new URLSearchParams({ phone: normalizeWhatsAppPhone(phone) });

  if (message) {
    params.set('text', message);
  }

  return `https://web.whatsapp.com/send?${params.toString()}`;
}
