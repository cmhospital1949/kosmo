'use client';

import { NextAuthProvider } from '@/context/auth';

export function Providers({ children }) {
  return (
    <NextAuthProvider>
      {children}
    </NextAuthProvider>
  );
}
