'use client';

import React, { useState } from 'react';
import { useTranslations } from 'next-intl';

const donationAmounts = [
  { value: 10000, label: '10,000 ₩' },
  { value: 30000, label: '30,000 ₩' },
  { value: 50000, label: '50,000 ₩' },
  { value: 100000, label: '100,000 ₩' },
  { value: 'custom', label: 'Custom Amount' }
];

export default function DonatePage() {
  const t = useTranslations('donate');
  const [selectedAmount, setSelectedAmount] = useState(donationAmounts[1].value);
  const [customAmount, setCustomAmount] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('kakaopay');
  const [donorInfo, setDonorInfo] = useState({
    name: '',
    email: '',
    isAnonymous: false
  });
  const [isProcessing, setIsProcessing] = useState(false);
  
  const handleAmountSelect = (amount) => {
    setSelectedAmount(amount);
  };
  
  const handleDonorInfoChange = (e) => {
    const { name, value, type, checked } = e.target;
    setDonorInfo({
      ...donorInfo,
      [name]: type === 'checkbox' ? checked : value
    });
  };
  
  const getActualAmount = () => {
    if (selectedAmount === 'custom') {
      return parseInt(customAmount) || 0;
    }
    return selectedAmount;
  };
  
  const handleDonationSubmit = async (e) => {
    e.preventDefault();
    setIsProcessing(true);
    
    try {
      // In a real implementation, this would call the KakaoPay API
      // For this demo, we'll simulate a redirect after a delay
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      // Simulate redirect to payment gateway
      alert(`Redirecting to ${paymentMethod === 'kakaopay' ? 'KakaoPay' : 'Bank Transfer'} for donation of ${getActualAmount().toLocaleString()} ₩`);
      
      setIsProcessing(false);
    } catch (error) {
      console.error('Error processing donation:', error);
      setIsProcessing(false);
      alert('There was an error processing your donation. Please try again.');
    }
  };
  
  return (
    <div className="bg-background">
      {/* Hero Section */}
      <div className="relative bg-secondary text-white">
        <div className="absolute inset-0 z-0">
          <img 
            src="/assets/images/donate-hero.jpg" 
            alt="Support KOSMO Foundation" 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl font-bold tracking-tight">
              {t('support_our_cause')}
            </h1>
            <p className="mt-6 text-xl max-w-2xl">
              Your donation helps us provide essential medical support, education, and resources to athletes across Korea. Every contribution makes a difference.
            </p>
          </div>
        </div>
      </div>
      
      {/* Donation Form Section */}
      <section className="py-16 md:py-24">
        <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="bg-white rounded-lg shadow-md overflow-hidden">
            <div className="p-8">
              <h2 className="text-2xl font-bold text-secondary mb-6">{t('donation_options')}</h2>
              
              <form onSubmit={handleDonationSubmit}>
                {/* Donation Amount */}
                <div className="mb-8">
                  <h3 className="text-lg font-medium text-secondary mb-4">{t('amount')}</h3>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {donationAmounts.map((amount) => (
                      <button
                        key={amount.value}
                        type="button"
                        className={`py-3 px-4 rounded-md text-center ${
                          selectedAmount === amount.value
                            ? 'bg-primary text-secondary ring-2 ring-primary-dark'
                            : 'bg-gray-100 text-secondary-light hover:bg-gray-200'
                        }`}
                        onClick={() => handleAmountSelect(amount.value)}
                      >
                        {amount.label}
                      </button>
                    ))}
                  </div>
                  
                  {selectedAmount === 'custom' && (
                    <div className="mt-4">
                      <label htmlFor="customAmount" className="sr-only">Custom Amount</label>
                      <div className="relative rounded-md shadow-sm">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span className="text-gray-500 sm:text-sm">₩</span>
                        </div>
                        <input
                          type="number"
                          name="customAmount"
                          id="customAmount"
                          className="focus:ring-primary focus:border-primary block w-full pl-8 pr-12 sm:text-sm border-gray-300 rounded-md"
                          placeholder="Enter amount"
                          value={customAmount}
                          onChange={(e) => setCustomAmount(e.target.value)}
                          min="1000"
                        />
                        <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                          <span className="text-gray-500 sm:text-sm">KRW</span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
                
                {/* Payment Method */}
                <div className="mb-8">
                  <h3 className="text-lg font-medium text-secondary mb-4">{t('payment_methods')}</h3>
                  <div className="space-y-4">
                    <div className="flex items-center">
                      <input
                        id="kakaopay"
                        name="paymentMethod"
                        type="radio"
                        checked={paymentMethod === 'kakaopay'}
                        onChange={() => setPaymentMethod('kakaopay')}
                        className="focus:ring-primary h-4 w-4 text-primary border-gray-300"
                      />
                      <label htmlFor="kakaopay" className="ml-3 flex items-center">
                        <img src="/assets/images/kakaopay-logo.png" alt="KakaoPay" className="h-8 mr-2" />
                        <span className="text-secondary">KakaoPay</span>
                      </label>
                    </div>
                    <div className="flex items-center">
                      <input
                        id="bank"
                        name="paymentMethod"
                        type="radio"
                        checked={paymentMethod === 'bank'}
                        onChange={() => setPaymentMethod('bank')}
                        className="focus:ring-primary h-4 w-4 text-primary border-gray-300"
                      />
                      <label htmlFor="bank" className="ml-3">
                        <span className="text-secondary">Bank Transfer</span>
                      </label>
                    </div>
                  </div>
                </div>
                
                {/* Donor Information */}
                <div className="mb-8">
                  <h3 className="text-lg font-medium text-secondary mb-4">{t('personal_info')}</h3>
                  
                  <div className="space-y-4">
                    <div>
                      <label htmlFor="name" className="block text-sm font-medium text-secondary mb-1">
                        Name
                      </label>
                      <input
                        type="text"
                        id="name"
                        name="name"
                        value={donorInfo.name}
                        onChange={handleDonorInfoChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                        disabled={donorInfo.isAnonymous}
                        required={!donorInfo.isAnonymous}
                      />
                    </div>
                    
                    <div>
                      <label htmlFor="email" className="block text-sm font-medium text-secondary mb-1">
                        Email
                      </label>
                      <input
                        type="email"
                        id="email"
                        name="email"
                        value={donorInfo.email}
                        onChange={handleDonorInfoChange}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"
                        disabled={donorInfo.isAnonymous}
                        required={!donorInfo.isAnonymous}
                      />
                    </div>
                    
                    <div className="flex items-center">
                      <input
                        id="anonymous"
                        name="isAnonymous"
                        type="checkbox"
                        checked={donorInfo.isAnonymous}
                        onChange={handleDonorInfoChange}
                        className="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      />
                      <label htmlFor="anonymous" className="ml-2 block text-sm text-secondary">
                        Make this donation anonymous
                      </label>
                    </div>
                  </div>
                </div>
                
                {/* Submit Button */}
                <button
                  type="submit"
                  className="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-base font-medium text-secondary bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                  disabled={isProcessing || (selectedAmount === 'custom' && !customAmount)}
                >
                  {isProcessing ? (
                    <>
                      <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Processing...
                    </>
                  ) : t('complete_donation')}
                </button>
              </form>
            </div>
            
            {/* Bank Information Panel */}
            <div className="bg-gray-100 px-8 py-6">
              <h3 className="text-lg font-medium text-secondary mb-4">Bank Account Information</h3>
              <div className="bg-white p-4 rounded-md">
                <p className="text-secondary-light">
                  <strong>Bank:</strong> IBK 기업은행<br />
                  <strong>Account Number:</strong> 077-162014-01-031<br />
                  <strong>Account Holder:</strong> (재)한국스포츠의료지원재단
                </p>
              </div>
              <p className="mt-3 text-sm text-gray-500">
                For bank transfers, please include your name in the transfer memo to help us track your donation.
              </p>
            </div>
          </div>
        </div>
      </section>
      
      {/* Impact Section */}
      <section className="bg-primary/20 py-16">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold text-secondary text-center mb-12">Your Donation Makes an Impact</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="bg-white p-6 rounded-lg shadow-sm text-center">
              <div className="text-4xl font-bold text-primary-dark mb-4">30,000 ₩</div>
              <p className="text-secondary">Provides medical supplies for student-athletes for one week</p>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-sm text-center">
              <div className="text-4xl font-bold text-primary-dark mb-4">100,000 ₩</div>
              <p className="text-secondary">Sponsors one athlete's sports medicine consultation</p>
            </div>
            
            <div className="bg-white p-6 rounded-lg shadow-sm text-center">
              <div className="text-4xl font-bold text-primary-dark mb-4">500,000 ₩</div>
              <p className="text-secondary">Funds training workshops for sports medicine professionals</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
