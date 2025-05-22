'use client';

import React, { useState } from 'react';
import { useTranslations } from 'next-intl';
import Link from 'next/link';

// Mock news data
const allNews = [
  {
    id: 1,
    slug: 'national-team-support-2025',
    title: 'KOSMO Foundation Extends Support to National Team for 2025 Season',
    date: '2025-05-15',
    category: 'Programs',
    excerpt: 'The KOSMO Foundation has announced continued support for Korea's national team athletes through the upcoming 2025 competitive season, providing medical services, rehabilitation support, and educational resources.',
    image: '/assets/images/news/national-team.jpg',
    content: `
      <p>The KOSMO Foundation has announced comprehensive support for Korea's national team athletes throughout the 2025 competitive season. This continuation of our flagship support program will provide essential medical services, rehabilitation resources, and educational opportunities to elite athletes representing Korea on the international stage.</p>
      
      <p>"We are committed to ensuring our national athletes have the best possible support system as they compete globally," said Dr. Lee Sang-hoon, Chairman of KOSMO Foundation. "This program represents our dedication to advancing sports medicine in Korea and supporting athletic excellence."</p>
      
      <h3>Program Components</h3>
      
      <p>The support program includes:</p>
      
      <ul>
        <li>On-site medical teams at major training facilities</li>
        <li>Specialized rehabilitation programs for injured athletes</li>
        <li>Nutritional guidance and supplementation support</li>
        <li>Mental health resources and counseling</li>
        <li>Educational workshops on injury prevention</li>
      </ul>
      
      <p>This initiative builds on the success of previous years' programs, which have contributed to improved recovery times and enhanced performance for national team members across multiple sports.</p>
      
      <p>For more information about the National Team Support Program or to learn how you can contribute, please contact our program office.</p>
    `
  },
  {
    id: 2,
    slug: 'new-medical-partnership',
    title: 'New Medical Partnership Announced With Seoul University Hospital',
    date: '2025-05-10',
    category: 'Partnerships',
    excerpt: 'A groundbreaking partnership between KOSMO Foundation and Seoul University Hospital will provide enhanced medical services to student-athletes, offering specialized care and research opportunities.',
    image: '/assets/images/news/hospital-partnership.jpg',
    content: `
      <p>KOSMO Foundation is proud to announce a new strategic partnership with Seoul University Hospital, one of Korea's premier medical institutions. This collaboration will significantly enhance the medical services available to student-athletes throughout the country.</p>
      
      <p>The partnership, formalized in a ceremony held on May 8th, establishes a dedicated sports medicine unit within the hospital, specifically designed to address the unique health needs of young athletes.</p>
      
      <h3>Key Benefits</h3>
      
      <p>Through this partnership, student-athletes will gain access to:</p>
      
      <ul>
        <li>Specialized orthopedic and sports medicine services</li>
        <li>Priority appointment scheduling</li>
        <li>Advanced diagnostic imaging</li>
        <li>Multidisciplinary care teams</li>
        <li>Participation in cutting-edge research studies</li>
      </ul>
      
      <p>"This collaboration represents a major step forward in our mission to provide world-class healthcare to Korea's athletic community," said Dr. Park Ji-won, Medical Director at KOSMO Foundation. "By combining our expertise with Seoul University Hospital's resources and research capabilities, we can offer truly comprehensive care."</p>
      
      <p>The new sports medicine unit is expected to begin operations next month, with full services available by July 2025.</p>
    `
  },
  {
    id: 3,
    slug: 'sports-medicine-conference-2025',
    title: 'KOSMO to Host International Sports Medicine Conference in September',
    date: '2025-05-01',
    category: 'Events',
    excerpt: 'KOSMO Foundation will host its annual International Sports Medicine Conference from September 12-14, bringing together experts from around the world to share the latest research and practices.',
    image: '/assets/images/news/conference.jpg',
    content: `
      <p>The KOSMO Foundation is pleased to announce the 2025 International Sports Medicine Conference, scheduled for September 12-14 in Seoul. This three-day event will bring together leading researchers, clinicians, and practitioners from around the world to share cutting-edge knowledge and best practices in sports medicine.</p>
      
      <h3>Conference Highlights</h3>
      
      <p>This year's conference will feature:</p>
      
      <ul>
        <li>Keynote presentations from internationally renowned experts</li>
        <li>Panel discussions on emerging trends in sports medicine</li>
        <li>Hands-on workshops for practical skill development</li>
        <li>Research presentations from promising scholars</li>
        <li>Networking opportunities with global leaders in the field</li>
      </ul>
      
      <p>"Our annual conference has become a cornerstone event for the sports medicine community in Asia," said Ms. Kim Min-ji, Executive Director of KOSMO Foundation. "We're excited to facilitate the exchange of knowledge that ultimately benefits athletes at all levels."</p>
      
      <h3>Registration</h3>
      
      <p>Early bird registration is now open through July 31st. Special rates are available for students, academic faculty, and medical professionals. For registration information and the preliminary program schedule, please visit our conference website.</p>
    `
  },
  {
    id: 4,
    slug: 'school-outreach-program-expansion',
    title: 'School Outreach Program Expands to 50 Additional Schools',
    date: '2025-04-20',
    category: 'Programs',
    excerpt: 'The KOSMO Foundation's School Outreach Program will expand to 50 additional schools across Korea, bringing sports medicine education and resources to underserved communities.',
    image: '/assets/images/news/school-outreach.jpg',
    content: `
      <p>Following the successful pilot phase, KOSMO Foundation is proud to announce the expansion of our School Outreach Program to 50 additional schools across Korea. This initiative aims to bring sports medicine education, injury prevention resources, and health screenings to student-athletes in underserved communities.</p>
      
      <p>The program expansion, made possible through a government grant and private donations, will reach an estimated 7,500 additional students during the 2025-2026 academic year.</p>
      
      <h3>Program Components</h3>
      
      <p>Schools participating in the outreach program will receive:</p>
      
      <ul>
        <li>Regular visits from sports medicine professionals</li>
        <li>Educational workshops for coaches and physical education staff</li>
        <li>Basic athletic training supplies</li>
        <li>Health and fitness assessments for student-athletes</li>
        <li>Nutritional guidance materials</li>
      </ul>
      
      <p>"We believe every young athlete deserves access to sports medicine resources, regardless of their school's location or budget," said Professor Choi Soo-young, Education Program Director at KOSMO Foundation. "This expansion represents a significant step toward that goal."</p>
      
      <p>Schools interested in applying for the next phase of the program can find application materials on our website beginning June 1st.</p>
    `
  },
  {
    id: 5,
    slug: 'new-research-grant-recipients',
    title: 'KOSMO Announces 2025 Research Grant Recipients',
    date: '2025-04-10',
    category: 'Research',
    excerpt: 'The KOSMO Foundation has awarded research grants to five promising studies in sports medicine, supporting innovative research that will advance athlete care and performance.',
    image: '/assets/images/news/research-grants.jpg',
    content: `
      <p>KOSMO Foundation is pleased to announce the recipients of our 2025 Research Grant Program. Five innovative research projects have been selected from a competitive field of applicants, each addressing critical questions in sports medicine and athlete care.</p>
      
      <p>This year's grants total ₩250 million and will support studies ranging from injury prevention to rehabilitation techniques and performance optimization.</p>
      
      <h3>Selected Research Projects</h3>
      
      <ul>
        <li><strong>Dr. Yoon Ji-hye, Seoul National University</strong> - "Biomechanical Analysis of Knee Injuries in Young Female Athletes"</li>
        <li><strong>Dr. Kim Tae-woo, Yonsei University</strong> - "Nutritional Interventions for Recovery in Elite Endurance Athletes"</li>
        <li><strong>Dr. Park Min-soo, Korea Sport & Olympic Research Institute</strong> - "Novel Rehabilitation Protocols for Shoulder Injuries in Overhead Athletes"</li>
        <li><strong>Dr. Jang Hye-jin, Hanyang University</strong> - "Psychological Factors in Return-to-Play Decision Making"</li>
        <li><strong>Dr. Lee Joon-ho, Korea University</strong> - "AI-Assisted Injury Prediction Models for Team Sports"</li>
      </ul>
      
      <p>"These projects represent the cutting edge of sports medicine research in Korea," said Dr. Lee Sang-hoon, Chairman of KOSMO Foundation. "We're proud to support work that will ultimately improve the health and performance of athletes at all levels."</p>
      
      <p>The grant recipients will present their preliminary findings at the International Sports Medicine Conference in September 2025.</p>
    `
  },
  {
    id: 6,
    slug: 'new-leadership-training-program',
    title: 'New Leadership Training Program for Athlete Development',
    date: '2025-03-25',
    category: 'Programs',
    excerpt: 'KOSMO Foundation launches a new comprehensive leadership training program designed specifically for athletes, helping them develop skills that will benefit them both on and off the field.',
    image: '/assets/images/news/leadership-training.jpg',
    content: `
      <p>KOSMO Foundation is excited to announce the launch of our new Athlete Leadership Development Program, a comprehensive initiative designed to cultivate leadership skills in athletes at various stages of their careers.</p>
      
      <p>The program, developed in consultation with leadership experts and former elite athletes, addresses the unique challenges faced by individuals balancing intensive athletic training with personal and professional growth.</p>
      
      <h3>Program Curriculum</h3>
      
      <p>The leadership program includes modules on:</p>
      
      <ul>
        <li>Effective communication in team environments</li>
        <li>Decision-making under pressure</li>
        <li>Conflict resolution strategies</li>
        <li>Goal setting and personal accountability</li>
        <li>Transitioning skills to post-athletic careers</li>
      </ul>
      
      <p>"Athletes develop many natural leadership abilities through sport, but often lack opportunities to refine these skills in structured environments," said Kim Jun-seo, Leadership Program Coordinator. "Our program bridges that gap, helping athletes recognize and enhance their leadership potential."</p>
      
      <h3>Participation Information</h3>
      
      <p>The inaugural program cohort will begin in September 2025, with applications opening on June 1st. The program is open to athletes aged 16 and older, with sessions tailored to different age groups and competition levels.</p>
      
      <p>For more information or to express interest in the program, please contact our office.</p>
    `
  }
];

export default function NewsPage() {
  const t = useTranslations('news');
  const [category, setCategory] = useState('all');
  
  // Filter news based on selected category
  const filteredNews = category === 'all' 
    ? allNews 
    : allNews.filter(news => news.category === category);
  
  // Get unique categories for filter
  const categories = ['all', ...new Set(allNews.map(news => news.category))];
  
  return (
    <div className="bg-background">
      {/* Hero Section */}
      <div className="relative bg-secondary text-white">
        <div className="absolute inset-0 z-0">
          <img 
            src="/assets/images/news-hero.jpg" 
            alt="KOSMO Foundation News" 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl font-bold tracking-tight">
              {t('latest_news')}
            </h1>
            <p className="mt-6 text-xl max-w-2xl">
              Stay updated with the latest news, events, and announcements from KOSMO Foundation.
            </p>
          </div>
        </div>
      </div>
      
      {/* News Content */}
      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {/* Category Filters */}
        <div className="mb-12">
          <div className="flex flex-wrap items-center gap-2">
            <span className="text-secondary font-medium">{t('filter_by')}:</span>
            {categories.map((cat) => (
              <button
                key={cat}
                onClick={() => setCategory(cat)}
                className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${
                  category === cat
                    ? 'bg-primary text-secondary'
                    : 'bg-gray-100 text-secondary-light hover:bg-gray-200'
                }`}
              >
                {cat === 'all' ? t('all_categories') : cat}
              </button>
            ))}
          </div>
        </div>
        
        {/* News Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {filteredNews.map((news) => (
            <div key={news.id} className="flex flex-col bg-white rounded-lg overflow-hidden shadow">
              <div className="aspect-w-16 aspect-h-9 bg-gray-200">
                <img
                  src={news.image}
                  alt={news.title}
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="flex-1 p-6 flex flex-col">
                <div className="flex-1">
                  <div className="flex items-center gap-4 mb-3">
                    <span className="text-sm text-primary-dark font-medium">
                      {news.category}
                    </span>
                    <span className="text-sm text-gray-500">
                      {new Date(news.date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                      })}
                    </span>
                  </div>
                  <h3 className="text-xl font-semibold text-secondary mb-3">
                    <Link href={`/news/${news.slug}`}>
                      {news.title}
                    </Link>
                  </h3>
                  <p className="text-secondary-light line-clamp-3">{news.excerpt}</p>
                </div>
                <div className="mt-6">
                  <Link
                    href={`/news/${news.slug}`}
                    className="text-base font-medium text-primary-dark hover:text-primary transition-colors flex items-center"
                  >
                    {t('read_more')}
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
          ))}
        </div>
      </div>
    </div>
  );
}
