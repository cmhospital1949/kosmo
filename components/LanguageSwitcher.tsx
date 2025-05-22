import React, { useState } from 'react';
import { useLocale } from 'next-intl';
import Link from 'next/link';
import { usePathname } from 'next/navigation';

const LanguageSwitcher = () => {
  const [isOpen, setIsOpen] = useState(false);
  const currentLocale = useLocale();
  const pathname = usePathname();
  
  // Remove locale prefix from pathname
  const pathnameWithoutLocale = pathname.replace(/^\/(en|ko)/, '') || '/';
  
  const toggleDropdown = () => {
    setIsOpen(!isOpen);
  };

  return (
    <div className="relative">
      <button
        type="button"
        className="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-secondary shadow-sm hover:bg-gray-50"
        onClick={toggleDropdown}
      >
        {currentLocale === 'en' ? 'English' : '한국어'}
        <svg
          className="ml-2 -mr-0.5 h-4 w-4"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path
            fillRule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clipRule="evenodd"
          />
        </svg>
      </button>

      {isOpen && (
        <div className="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
          <div className="py-1" role="menu" aria-orientation="vertical" aria-labelledby="language-menu">
            <Link
              href={`/en${pathnameWithoutLocale}`}
              className={`block px-4 py-2 text-sm ${
                currentLocale === 'en' 
                  ? 'bg-gray-100 text-gray-900 font-medium' 
                  : 'text-gray-700 hover:bg-gray-50'
              }`}
              role="menuitem"
              onClick={() => setIsOpen(false)}
            >
              English
            </Link>
            <Link
              href={`/ko${pathnameWithoutLocale}`}
              className={`block px-4 py-2 text-sm ${
                currentLocale === 'ko' 
                  ? 'bg-gray-100 text-gray-900 font-medium' 
                  : 'text-gray-700 hover:bg-gray-50'
              }`}
              role="menuitem"
              onClick={() => setIsOpen(false)}
            >
              한국어
            </Link>
          </div>
        </div>
      )}
    </div>
  );
};

export default LanguageSwitcher;
