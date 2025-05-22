'use client';

import React from 'react';
import { useTranslations } from 'next-intl';
import ProgramCard from '@/components/ProgramCard';

// Mock data for all programs based on the PRD
const allPrograms = [
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
  },
  {
    id: 4,
    slug: 'sports-med-edu',
    title: 'Sports-Medicine Education for Staff & Athletes',
    excerpt: 'Educating sports staff and athletes on the latest sports medicine practices and injury prevention techniques.',
    heroImage: '/assets/images/sports-med-edu.jpg'
  },
  {
    id: 5,
    slug: 'meal-support',
    title: 'Nutritious Meal Support',
    excerpt: 'Providing nutritious meal support to ensure proper nutrition for optimal athletic performance and health.',
    heroImage: '/assets/images/meal-support.jpg'
  },
  {
    id: 6,
    slug: 'supplements',
    title: 'Dietary Supplement Guidance & Aid',
    excerpt: 'Offering guidance on proper use of dietary supplements and aid for athletes with specific nutritional needs.',
    heroImage: '/assets/images/supplements.jpg'
  },
  {
    id: 7,
    slug: 'career-consult',
    title: 'Career Education & Consulting',
    excerpt: 'Helping athletes plan for careers both within and beyond sports through education and consulting services.',
    heroImage: '/assets/images/career-consult.jpg'
  },
  {
    id: 8,
    slug: 'rehab-cert',
    title: 'Sports Rehab & Trainer Certification',
    excerpt: 'Providing rehabilitation services and certification programs for sports trainers to enhance athlete care.',
    heroImage: '/assets/images/rehab-cert.jpg'
  },
  {
    id: 9,
    slug: 'seminars',
    title: 'Seminars & Events Hosting',
    excerpt: 'Hosting educational seminars and events to share knowledge and bring together sports professionals.',
    heroImage: '/assets/images/seminars.jpg'
  },
  {
    id: 10,
    slug: 'publications',
    title: 'Publications & Journals',
    excerpt: 'Publishing educational materials, research papers, and journals to advance sports medicine knowledge.',
    heroImage: '/assets/images/publications.jpg'
  },
  {
    id: 11,
    slug: 'musculo-edu',
    title: 'Sports, MSK & Medical Education',
    excerpt: 'Providing comprehensive education on musculoskeletal health and sports medicine for medical professionals.',
    heroImage: '/assets/images/musculo-edu.jpg'
  },
  {
    id: 12,
    slug: 'elite-support',
    title: 'National-Team / School Athlete Support',
    excerpt: 'Supporting elite national team and school athletes with specialized services tailored to their needs.',
    heroImage: '/assets/images/elite-support.jpg'
  },
  {
    id: 13,
    slug: 'ai-automation',
    title: 'AI-Driven Automation & Assessment Tools',
    excerpt: 'Developing and implementing AI tools for automated assessment and training optimization.',
    heroImage: '/assets/images/ai-automation.jpg'
  },
  {
    id: 14,
    slug: 'govt-projects',
    title: 'Government-Funded Projects',
    excerpt: 'Executing government-funded initiatives to advance sports medicine and support throughout Korea.',
    heroImage: '/assets/images/govt-projects.jpg'
  }
];

export default function ProgramsPage() {
  const t = useTranslations('programs');
  
  return (
    <div className="bg-background">
      {/* Hero Section */}
      <div className="relative bg-secondary text-white">
        <div className="absolute inset-0 z-0">
          <img 
            src="/assets/images/programs-hero.jpg" 
            alt="KOSMO Foundation Programs" 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl font-bold tracking-tight">
              {t('all_programs')}
            </h1>
            <p className="mt-6 text-xl max-w-2xl">
              Explore our wide range of programs designed to support athletes, students, and sports professionals across Korea.
            </p>
          </div>
        </div>
      </div>
      
      {/* Programs List */}
      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {allPrograms.map((program) => (
            <ProgramCard key={program.id} program={program} />
          ))}
        </div>
      </div>
    </div>
  );
}
