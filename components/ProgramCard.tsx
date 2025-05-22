import React from 'react';
import Link from 'next/link';

const ProgramCard = ({ program }) => {
  const { slug, title, excerpt, heroImage } = program;
  
  return (
    <div className="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
      <div className="aspect-w-16 aspect-h-9 bg-gray-200 overflow-hidden">
        <img
          src={heroImage || '/assets/images/placeholder.jpg'}
          alt={title}
          className="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
        />
      </div>
      <div className="flex flex-1 flex-col p-6">
        <h3 className="text-xl font-semibold text-secondary">{title}</h3>
        <p className="mt-3 text-base text-gray-500 line-clamp-3">{excerpt}</p>
        <div className="mt-6 flex flex-1 items-end">
          <Link
            href={`/programs/${slug}`}
            className="text-base font-medium text-primary-dark hover:text-primary transition-colors flex items-center"
          >
            Learn more
            <svg
              className="ml-2 h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
              aria-hidden="true"
            >
              <path
                fillRule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clipRule="evenodd"
              />
            </svg>
          </Link>
        </div>
      </div>
    </div>
  );
};

export default ProgramCard;
