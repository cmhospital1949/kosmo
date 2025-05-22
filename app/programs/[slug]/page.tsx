'use client';

import React from 'react';
import { useTranslations } from 'next-intl';
import Link from 'next/link';
import DonateButton from '@/components/DonateButton';

// Mock data for all programs based on the PRD
const programsData = {
  'medical-support': {
    id: 1,
    title: 'Medical Support for Students & Student-Athletes',
    koreanTitle: '학생·학생선수 의료 지원',
    heroImage: '/assets/images/medical-support.jpg',
    content: `
      <p>The KOSMO Foundation's Medical Support Program provides essential healthcare services to students and student-athletes across Korea. Our program addresses the unique medical needs of young athletes, helping them maintain optimal health while pursuing their athletic and academic goals.</p>
      
      <h3>Program Highlights</h3>
      <ul>
        <li>Comprehensive medical evaluations and screenings</li>
        <li>Sports injury prevention and treatment</li>
        <li>Rehabilitation services for injured athletes</li>
        <li>Mental health support and counseling</li>
        <li>Nutritional guidance for optimal performance</li>
      </ul>
      
      <h3>Impact</h3>
      <p>Since its inception, our Medical Support Program has served over 5,000 student-athletes across Korea, significantly reducing career-threatening injuries and improving overall health outcomes. By providing accessible, high-quality medical care, we help ensure that athletic pursuits don't come at the expense of long-term health.</p>
      
      <h3>Eligibility</h3>
      <p>Our program is available to registered student-athletes at all educational levels, from elementary through university education. Priority is given to athletes from underserved communities and those without adequate access to sports medicine resources.</p>
    `
  },
  'cultural-arts': {
    id: 2,
    title: 'Cultural & Arts Education/Events',
    koreanTitle: '문화·예술 교육 및 행사',
    heroImage: '/assets/images/cultural-arts.jpg',
    content: `
      <p>KOSMO Foundation believes in the holistic development of athletes, which includes exposure to cultural and artistic experiences. Our Cultural & Arts Education Program enriches athletes' lives beyond sports, fostering creativity, cultural appreciation, and personal growth.</p>
      
      <h3>Program Highlights</h3>
      <ul>
        <li>Arts workshops tailored for athletes</li>
        <li>Cultural excursions and experiences</li>
        <li>Artist-in-residence programs at training facilities</li>
        <li>Exhibitions showcasing athlete-created artwork</li>
        <li>Performances and events that bridge sports and the arts</li>
      </ul>
      
      <h3>Impact</h3>
      <p>Research shows that engagement with arts and culture enhances cognitive flexibility, reduces stress, and contributes to improved athletic performance. Our program has reached over 3,000 athletes, providing balance to their rigorous training regimens and expanding their horizons beyond sports.</p>
      
      <h3>Upcoming Events</h3>
      <p>Throughout the year, we organize various cultural events and activities for student-athletes. Check our events calendar for upcoming opportunities to participate or volunteer.</p>
    `
  },
  // Add more program details for other programs as needed
};

export default function ProgramDetailPage({ params }) {
  const { slug } = params;
  const t = useTranslations('programs');
  const program = programsData[slug] || {};
  
  if (!program.id) {
    return (
      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <h1 className="text-3xl font-bold text-secondary">Program Not Found</h1>
        <p className="mt-4 text-lg text-secondary-light">The program you're looking for doesn't exist or has been moved.</p>
        <Link 
          href="/programs" 
          className="mt-8 inline-block text-primary-dark hover:text-primary transition-colors"
        >
          Back to All Programs
        </Link>
      </div>
    );
  }
  
  return (
    <div className="bg-background">
      {/* Hero Section */}
      <div className="relative bg-secondary text-white">
        <div className="absolute inset-0 z-0">
          <img 
            src={program.heroImage || '/assets/images/program-default.jpg'} 
            alt={program.title} 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
          <div className="max-w-3xl">
            <div className="flex flex-col sm:flex-row gap-2 items-center sm:items-start">
              <Link 
                href="/programs" 
                className="mb-4 sm:mb-0 text-white/70 hover:text-white transition-colors flex items-center"
              >
                <svg 
                  className="w-5 h-5 mr-1" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                </svg>
                {t('all_programs')}
              </Link>
            </div>
            <h1 className="text-4xl md:text-5xl font-bold tracking-tight mt-4">
              {program.title}
            </h1>
            <p className="mt-4 text-xl text-white/70">
              {program.koreanTitle}
            </p>
          </div>
        </div>
      </div>
      
      {/* Content Section */}
      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
          <div className="lg:col-span-2">
            <article className="prose prose-lg max-w-none">
              <div dangerouslySetInnerHTML={{ __html: program.content }} />
            </article>
          </div>
          
          <div className="lg:col-span-1">
            <div className="sticky top-24 bg-primary/20 rounded-lg p-6 shadow-sm">
              <h3 className="text-xl font-semibold text-secondary mb-4">{t('get_involved')}</h3>
              <p className="text-secondary-light mb-6">
                Support our mission by donating, volunteering, or participating in our programs. Your contribution makes a difference in the lives of student-athletes across Korea.
              </p>
              <div className="space-y-4">
                <DonateButton 
                  label="Support This Program" 
                  className="w-full justify-center"
                />
                <Link
                  href="/contact"
                  className="block w-full text-center px-5 py-2.5 rounded-md font-medium text-secondary border border-secondary hover:bg-secondary hover:text-white transition-colors"
                >
                  Contact Us
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
