import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useToast } from '@/Components/Toast';

export function FlashToasts() {
  const { flash } = usePage<PageProps>().props;
  const { addToast } = useToast();

  useEffect(() => {
    if (flash.success) {
      addToast({ message: flash.success, type: 'success' });
    }
    if (flash.error) {
      addToast({ message: flash.error, type: 'error' });
    }
  }, [flash.success, flash.error, addToast]);

  return null;
}
