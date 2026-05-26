import { usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';

export function useFlash() {
  const { flash } = usePage<PageProps>().props;

  return {
    success: flash.success,
    error: flash.error,
  };
}
