import { usePage } from '@inertiajs/react';
import { type PageProps } from '@/types';

export function useAuth() {
  const { auth } = usePage<PageProps>().props;

  return {
    user: auth.user,
    isAdmin: auth.user?.is_admin ?? false,
    isMember: !!auth.user && !auth.user.is_admin,
    isGuest: !auth.user,
    customerAddresses: auth.customerAddresses,
  };
}
