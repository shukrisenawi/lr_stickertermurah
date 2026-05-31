import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

function pad(n: number): string {
  return String(n).padStart(2, '0');
}

export function formatDate(date: string | Date): string {
  const d = new Date(date);
  return `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}`;
}

export function maskDate(value: string): string {
  const digits = value.replace(/\D/g, '').slice(0, 8);
  if (!digits) return '';
  let formatted = '';
  for (let i = 0; i < digits.length; i++) {
    if (i === 2 || i === 4) formatted += '-';
    formatted += digits[i];
  }
  return formatted;
}

export function formatDateTime(date: string | Date): string {
  const d = new Date(date);
  const day = pad(d.getDate());
  const month = pad(d.getMonth() + 1);
  const year = d.getFullYear();
  const hours = pad(d.getHours());
  const mins = pad(d.getMinutes());
  const ampm = d.getHours() >= 12 ? 'PM' : 'AM';
  return `${day}-${month}-${year} ${hours}:${mins}${ampm}`;
}
