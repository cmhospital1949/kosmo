'use client';

import React, { useEffect } from 'react';
import Link from 'next/link';

export default function DonationFailedPage() {
  useEffect(() => {
    // Clear any payment related data from session storage
    sessionStorage.removeItem('kakaopay_tid');
  }, []);
  
  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
      <div className="rounded-full bg-red-100 p-4 h-16 w-16 mx-auto mb-6 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-red-600" viewBox="0 0 20 20" fill="currentColor">
          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
        </svg>
      </div>
      <h1 className="text-3xl font-bold text-secondary mb-4">Payment Failed</h1>
      <p className="text-lg text-secondary-light mb-8">
        We encountered an issue while processing your payment. Don't worry - no payment has been processed.
      </p>
      
      <div className="bg-gray-100 p-6 rounded-lg mb-8">
        <h2 className="text-lg font-medium text-secondary mb-4">Common Reasons for Payment Failure</h2>
        <ul className="text-left text-secondary-light space-y-2">
          <li>• Insufficient funds in your account</li>
          <li>• Payment limit exceeded</li>
          <li>• Connectivity issues during payment</li>
          <li>• Payment gateway technical issues</li>
        </ul>
      </div>
      
      <div className="flex flex-col sm:flex-row gap-4 justify-center">
        <Link
          href="/donate"
          className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-secondary bg-primary hover:bg-primary-dark"
        >
          Try Again
        </Link>
        <Link
          href="/contact"
          className="inline-flex items-center justify-center px-5 py-3 border border-secondary text-base font-medium rounded-md text-secondary hover:bg-gray-100"
        >
          Contact Support
        </Link>
      </div>
    </div>
  );
}
