'use client';

import React from 'react';
import { useTranslations } from 'next-intl';
import Link from 'next/link';

// Mock timeline data
const timeline = [
  {
    year: '2020',
    title: 'Foundation Established',
    description: 'KOSMO Foundation was established as a non-profit organization dedicated to supporting athletes and sports medicine in Korea.'
  },
  {
    year: '2021',
    title: 'Government Certification',
    description: 'Received official government certification as a non-profit foundation, enabling expanded operations and partnerships.'
  },
  {
    year: '2022',
    title: 'National Team Partnership',
    description: 'Formed an official partnership with Korean National Sports Teams to provide medical support and education.'
  },
  {
    year: '2023',
    title: 'Education Programs Launch',
    description: 'Launched comprehensive education programs for sports medicine professionals and student-athletes.'
  },
  {
    year: '2024',
    title: 'Research Initiative',
    description: 'Established the Sports Medicine Research Initiative to advance knowledge and practices in the field.'
  },
  {
    year: '2025',
    title: 'International Partnerships',
    description: 'Expanded our reach through international partnerships with leading sports medicine organizations.'
  }
];

// Mock partners data
const partners = [
  { name: 'Ministry of Culture, Sports and Tourism', logo: '/assets/images/partners/ministry.png' },
  { name: 'Korean Olympic Committee', logo: '/assets/images/partners/koc.png' },
  { name: 'Seoul National University Hospital', logo: '/assets/images/partners/snuh.png' },
  { name: 'Korean Sports Association', logo: '/assets/images/partners/ksa.png' },
  { name: 'IBK Industrial Bank', logo: '/assets/images/partners/ibk.png' },
  { name: 'Samsung', logo: '/assets/images/partners/samsung.png' },
];

// Mock team members data
const teamMembers = [
  {
    name: 'Lee Sang-hoon',
    title: 'Chairman',
    image: '/assets/images/team/chairman.jpg',
    bio: 'Dr. Lee has over 25 years of experience in sports medicine and has served as the medical director for multiple Korean national teams.'
  },
  {
    name: 'Kim Min-ji',
    title: 'Executive Director',
    image: '/assets/images/team/executive-director.jpg',
    bio: 'With a background in non-profit management and healthcare administration, Ms. Kim oversees all operational aspects of the foundation.'
  },
  {
    name: 'Park Ji-won',
    title: 'Medical Director',
    image: '/assets/images/team/medical-director.jpg',
    bio: 'Dr. Park specializes in orthopedic surgery and sports medicine, with particular expertise in rehabilitation for athletes.'
  },
  {
    name: 'Choi Soo-young',
    title: 'Education Program Director',
    image: '/assets/images/team/education-director.jpg',
    bio: 'Professor Choi leads our educational initiatives, developing curriculum and coordinating with academic institutions.'
  }
];

export default function AboutPage() {
  const t = useTranslations('about');
  
  return (
    <div className="bg-background">
      {/* Hero Section */}
      <div className="relative bg-secondary text-white">
        <div className="absolute inset-0 z-0">
          <img 
            src="/assets/images/about-hero.jpg" 
            alt="About KOSMO Foundation" 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl font-bold tracking-tight">
              {t('our_story')}
            </h1>
            <p className="mt-6 text-xl max-w-2xl">
              Founded in 2020, KOSMO Foundation has been dedicated to providing medical support, education, and resources to athletes and sports professionals across Korea.
            </p>
          </div>
        </div>
      </div>
      
      {/* Mission & Vision Section */}
      <section className="py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
              <h2 className="text-3xl font-bold text-secondary">{t('our_mission')}</h2>
              <div className="w-24 h-1 bg-primary mt-4"></div>
              <p className="mt-6 text-lg text-secondary-light">
                KOSMO Foundation is committed to enhancing the health, well-being, and performance of athletes at all levels through comprehensive medical support, education, and research. We aim to create a sustainable ecosystem of sports medicine excellence in Korea.
              </p>
            </div>
            <div>
              <h2 className="text-3xl font-bold text-secondary">{t('our_vision')}</h2>
              <div className="w-24 h-1 bg-primary mt-4"></div>
              <p className="mt-6 text-lg text-secondary-light">
                We envision a future where every athlete in Korea has access to world-class sports medicine care and education, supported by cutting-edge research and innovative practices. Through our work, we strive to position Korea as a global leader in sports medicine and athlete development.
              </p>
            </div>
          </div>
        </div>
      </section>
      
      {/* Timeline Section */}
      <section className="bg-primary/20 py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold text-secondary text-center">Our Timeline</h2>
          <div className="w-24 h-1 bg-primary mx-auto mt-4 mb-12"></div>
          
          <div className="relative">
            {/* Timeline Line */}
            <div className="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-primary"></div>
            
            {/* Timeline Items */}
            <div className="relative z-10">
              {timeline.map((item, index) => (
                <div 
                  key={index} 
                  className={`mb-12 flex items-center ${
                    index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'
                  }`}
                >
                  <div className={`w-1/2 ${index % 2 === 0 ? 'pr-12 text-right' : 'pl-12'}`}>
                    <div className="bg-white p-6 rounded-lg shadow-md">
                      <h3 className="text-xl font-semibold text-secondary">{item.title}</h3>
                      <p className="text-lg font-bold text-primary-dark">{item.year}</p>
                      <p className="mt-2 text-secondary-light">{item.description}</p>
                    </div>
                  </div>
                  <div className="absolute left-1/2 transform -translate-x-1/2 w-8 h-8 rounded-full bg-primary flex items-center justify-center shadow-md">
                    <div className="w-4 h-4 bg-white rounded-full"></div>
                  </div>
                  <div className="w-1/2"></div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>
      
      {/* Team Section */}
      <section className="py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold text-secondary text-center">{t('our_team')}</h2>
          <div className="w-24 h-1 bg-primary mx-auto mt-4 mb-12"></div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {teamMembers.map((member, index) => (
              <div key={index} className="bg-white rounded-lg overflow-hidden shadow-md">
                <div className="aspect-w-1 aspect-h-1 bg-gray-200">
                  <img 
                    src={member.image} 
                    alt={member.name} 
                    className="w-full h-full object-cover"
                  />
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-secondary">{member.name}</h3>
                  <p className="text-primary-dark font-medium">{member.title}</p>
                  <p className="mt-3 text-secondary-light">{member.bio}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
      
      {/* Partners Section */}
      <section className="bg-primary/20 py-16 md:py-24">
        <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold text-secondary text-center">{t('our_partners')}</h2>
          <div className="w-24 h-1 bg-primary mx-auto mt-4 mb-12"></div>
          
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
            {partners.map((partner, index) => (
              <div key={index} className="flex items-center justify-center p-6 bg-white rounded-lg shadow-sm">
                <img 
                  src={partner.logo} 
                  alt={partner.name} 
                  className="max-h-16"
                />
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
