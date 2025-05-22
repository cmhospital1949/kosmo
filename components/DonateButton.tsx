import React from 'react';
import Link from 'next/link';

const DonateButton = ({ label = 'Donate', className = '', size = 'default' }) => {
  const sizes = {
    small: 'px-3 py-1.5 text-sm',
    default: 'px-5 py-2.5 text-base',
    large: 'px-6 py-3 text-lg',
  };
  
  const sizeClass = sizes[size] || sizes.default;
  
  return (
    <Link 
      href="/donate"
      className={`inline-flex items-center justify-center rounded-md font-medium text-secondary bg-primary hover:bg-primary-dark transition-colors ${sizeClass} ${className}`}
    >
      {label}
    </Link>
  );
};

export default DonateButton;
