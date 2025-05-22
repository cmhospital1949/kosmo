'use client';

import React, { useEffect, useState } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import axios from 'axios';

export default function DonationConfirmPage() {
  const searchParams = useSearchParams();
  const router = useRouter();
  
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [donation, setDonation] = useState(null);
  
  useEffect(() => {
    const pgToken = searchParams.get('pg_token');
    const orderId = searchParams.get('orderId');
    const tid = sessionStorage.getItem('kakaopay_tid');
    
    if (!pgToken || !orderId || !tid) {
      setError('Missing required payment information. Please try again.');
      setLoading(false);
      return;
    }
    
    // Approve the payment
    approvePayment(pgToken, orderId, tid);
  }, [searchParams]);
  
  const approvePayment = async (pgToken, orderId, tid) => {
    try {
      setLoading(true);
      
      // Call the approve API endpoint
      const response = await axios.post('/api/pay/kakao/approve', {
        pgToken,
        orderId,
        tid
      });
      
      // Update donation state
      setDonation(response.data);
      
      // Clear the TID from session storage
      sessionStorage.removeItem('kakaopay_tid');
      
      setLoading(false);
    } catch (error) {
      console.error('Payment approval error:', error);
      setError('Failed to complete your donation. Please contact support.');
      setLoading(false);
    }
  };
  
  if (loading) {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <div className="animate-pulse">
          <div className="h-12 bg-primary/30 rounded mb-6 w-1/2 mx-auto"></div>
          <div className="h-6 bg-gray-200 rounded mb-4 w-3/4 mx-auto"></div>
          <div className="h-6 bg-gray-200 rounded mb-4 w-2/3 mx-auto"></div>
          <div className="h-6 bg-gray-200 rounded mb-4 w-1/2 mx-auto"></div>
        </div>
        <p className="mt-8 text-secondary-light">Processing your donation...</p>
      </div>
    );
  }
  
  if (error) {
    return (
      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <div className="rounded-full bg-red-100 p-4 h-16 w-16 mx-auto mb-6 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-red-600" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
          </svg>
        </div>
        <h1 className="text-3xl font-bold text-secondary mb-4">Payment Error</h1>
        <p className="text-lg text-secondary-light mb-8">{error}</p>
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
  
  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
      <div className="rounded-full bg-green-100 p-4 h-16 w-16 mx-auto mb-6 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-green-600" viewBox="0 0 20 20" fill="currentColor">
          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
        </svg>
      </div>
      <h1 className="text-3xl font-bold text-secondary mb-4">Thank You for Your Donation!</h1>
      <p className="text-lg text-secondary-light mb-8">
        Your donation of {donation?.amount?.toLocaleString()} KRW has been successfully processed.
      </p>
      
      <div className="bg-gray-100 p-6 rounded-lg mb-8">
        <h2 className="text-lg font-medium text-secondary mb-4">Donation Details</h2>
        <dl className="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
          <div className="sm:col-span-1">
            <dt className="text-sm font-medium text-gray-500">Amount</dt>
            <dd className="mt-1 text-sm text-secondary">{donation?.amount?.toLocaleString()} KRW</dd>
          </div>
          <div className="sm:col-span-1">
            <dt className="text-sm font-medium text-gray-500">Status</dt>
            <dd className="mt-1 text-sm text-secondary capitalize">{donation?.status}</dd>
          </div>
          <div className="sm:col-span-1">
            <dt className="text-sm font-medium text-gray-500">Date</dt>
            <dd className="mt-1 text-sm text-secondary">
              {new Date(donation?.approvedAt || Date.now()).toLocaleDateString()}
            </dd>
          </div>
          <div className="sm:col-span-1">
            <dt className="text-sm font-medium text-gray-500">Donation ID</dt>
            <dd className="mt-1 text-sm text-secondary">{donation?.donationId}</dd>
          </div>
        </dl>
      </div>
      
      <p className="text-secondary-light mb-8">
        Your contribution helps us provide essential support to athletes and sports professionals across Korea.
        We've sent a confirmation email with details of your donation.
      </p>
      
      <div className="flex flex-col sm:flex-row gap-4 justify-center">
        <Link
          href="/"
          className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-secondary hover:bg-secondary/80"
        >
          Return to Homepage
        </Link>
        <Link
          href="/programs"
          className="inline-flex items-center justify-center px-5 py-3 border border-secondary text-base font-medium rounded-md text-secondary hover:bg-gray-100"
        >
          Explore Our Programs
        </Link>
      </div>
    </div>
  );
}
