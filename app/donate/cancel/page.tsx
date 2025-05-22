'use client';

import React, { useEffect } from 'react';
import Link from 'next/link';

export default function DonationCancelPage() {
  useEffect(() => {
    // Clear any payment related data from session storage
    sessionStorage.removeItem('kakaopay_tid');
  }, []);
  
  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
      <div className="rounded-full bg-yellow-100 p-4 h-16 w-16 mx-auto mb-6 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clipRule="evenodd" />
        </svg>
      </div>
      <h1 className="text-3xl font-bold text-secondary mb-4">Donation Cancelled</h1>
      <p className="text-lg text-secondary-light mb-8">
        Your donation process has been cancelled. No payment has been processed.
      </p>
      
      <div className="flex flex-col sm:flex-row gap-4 justify-center">
        <Link
          href="/donate"
          className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-secondary bg-primary hover:bg-primary-dark"
        >
          Try Again
        </Link>
        <Link
          href="/"
          className="inline-flex items-center justify-center px-5 py-3 border border-secondary text-base font-medium rounded-md text-secondary hover:bg-gray-100"
        >
          Return to Homepage
        </Link>
      </div>
    </div>
  );
}
