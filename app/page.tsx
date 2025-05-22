'use client';

import React from 'react';
import { useTranslations } from 'next-intl';
import Link from 'next/link';
import DonateButton from '@/components/DonateButton';
import ProgramCard from '@/components/ProgramCard';

// Mock data for featured programs
const featuredPrograms = [
  {
    id: 1,
    slug: 'medical-support',
    title: 'Medical Support for Students & Student-Athletes',
    excerpt: 'Providing essential medical care and support services to students and student-athletes across Korea.',
    heroImage: '/assets/images/medical-support.jpg'
  },
  {
    id: 2,
    slug: 'cultural-arts',
    title: 'Cultural & Arts Education/Events',
    excerpt: 'Supporting cultural and arts education to foster creativity and holistic development in young athletes.',
    heroImage: '/assets/images/cultural-arts.jpg'
  },
  {
    id: 3,
    slug: 'leadership',
    title: 'Personal Development & Leadership Training',
    excerpt: 'Building future leaders through comprehensive personal development and leadership training programs.',
    heroImage: '/assets/images/leadership.jpg'
  }
];

// Mock data for recent news
const recentNews = [
  {
    id: 1,
    slug: 'national-team-support-2025',
    title: 'KOSMO Foundation Extends Support to National Team for 2025 Season',
    date: '2025-05-15',
    excerpt: 'The KOSMO Foundation has announced continued support for Korea's national team athletes...',
    image: '/assets/images/news-1.jpg'
  },
  {
    id: 2,
    slug: 'new-medical-partnership',
    title: 'New Medical Partnership Announced With Seoul University Hospital',
    date: '2025-05-10',
    excerpt: 'A groundbreaking partnership between KOSMO Foundation and Seoul University Hospital will provide...',
    image: '/assets/images/news-2.jpg'
  }
];

export default function HomePage() {
  const t = useTranslations('homepage');
  const nav = useTranslations('navigation');
  
  return (
    <div className="flex flex-col">
      {/* Hero Section */}
      <section className="relative bg-secondary text-white overflow-hidden">
        <div className="absolute inset-0 z-0">
          <img 
            src="/assets/images/hero-bg.jpg" 
            alt="Athletes in training" 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 lg:py-40">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight">
              {t('hero_title')}
            </h1>
            <p className="mt-6 text-xl md:text-2xl max-w-2xl">
              {t('hero_subtitle')}
            </p>
            <div className="mt-10 flex flex-col sm:flex-row gap-4">
              <DonateButton 
                label={nav('donate')} 
                size="large" 
                className="font-bold"
              />
              <Link 
                href="/programs" 
                className="inline-flex items-center justify-center rounded-md border-2 border-white bg-transparent px-6 py-3 text-lg font-medium text-white hover:bg-white hover:text-secondary transition-colors"
              >
                {t('our_programs')}
              </Link>
            </div>
          </div>
        </div>
      </section>
      
      {/* Programs Section */}
      <section className="bg-background py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-secondary">{t('our_programs')}</h2>
            <div className="w-24 h-1 bg-primary mx-auto mt-4"></div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {featuredPrograms.map((program) => (
              <ProgramCard key={program.id} program={program} />
            ))}
          </div>
          
          <div className="mt-12 text-center">
            <Link 
              href="/programs" 
              className="inline-flex items-center justify-center rounded-md bg-secondary text-white px-6 py-3 text-base font-medium hover:bg-secondary/80 transition-colors"
            >
              {t('view_all_programs')}
              <svg
                className="ml-2 h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
              >
                <path
                  fillRule="evenodd"
                  d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                  clipRule="evenodd"
                />
              </svg>
            </Link>
          </div>
        </div>
      </section>
      
      {/* Mission Section */}
      <section className="bg-primary/30 py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl md:text-4xl font-bold text-secondary">{t('mission_title')}</h2>
              <div className="w-24 h-1 bg-primary mt-4"></div>
              <p className="mt-6 text-lg text-secondary-light">
                {t('mission_description')}
              </p>
              <div className="mt-8">
                <Link 
                  href="/about" 
                  className="inline-flex items-center text-lg font-medium text-primary-dark hover:text-primary transition-colors"
                >
                  Learn more about us
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
            <div className="relative h-96">
              <img 
                src="/assets/images/mission.jpg" 
                alt="Students in a classroom" 
                className="w-full h-full object-cover rounded-lg shadow-lg"
              />
            </div>
          </div>
        </div>
      </section>
      
      {/* News Section */}
      <section className="bg-background py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-secondary">{t('recent_news')}</h2>
            <div className="w-24 h-1 bg-primary mx-auto mt-4"></div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {recentNews.map((news) => (
              <div key={news.id} className="group overflow-hidden rounded-lg border border-gray-200 bg-white">
                <div className="flex flex-col md:flex-row">
                  <div className="md:w-1/3 md:flex-shrink-0">
                    <img
                      src={news.image}
                      alt={news.title}
                      className="h-48 md:h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
                    />
                  </div>
                  <div className="flex flex-1 flex-col justify-between p-6">
                    <div>
                      <p className="text-sm text-primary-dark font-medium">
                        {new Date(news.date).toLocaleDateString('en-US', { 
                          year: 'numeric', 
                          month: 'long', 
                          day: 'numeric' 
                        })}
                      </p>
                      <h3 className="mt-2 text-xl font-semibold text-secondary group-hover:text-primary-dark">
                        <Link href={`/news/${news.slug}`}>
                          {news.title}
                        </Link>
                      </h3>
                      <p className="mt-3 text-base text-gray-500">{news.excerpt}</p>
                    </div>
                    <div className="mt-4">
                      <Link
                        href={`/news/${news.slug}`}
                        className="text-base font-medium text-primary-dark hover:text-primary transition-colors flex items-center"
                      >
                        Read more
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
              </div>
            ))}
          </div>
          
          <div className="mt-12 text-center">
            <Link 
              href="/news" 
              className="inline-flex items-center justify-center rounded-md bg-secondary text-white px-6 py-3 text-base font-medium hover:bg-secondary/80 transition-colors"
            >
              {t('view_all_news')}
              <svg
                className="ml-2 h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
              >
                <path
                  fillRule="evenodd"
                  d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                  clipRule="evenodd"
                />
              </svg>
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
