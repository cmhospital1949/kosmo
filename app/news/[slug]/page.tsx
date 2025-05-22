'use client';

import React from 'react';
import Link from 'next/link';
import { useTranslations } from 'next-intl';

// This would normally come from a CMS, but for this demo, we'll use mock data
// In a real implementation, this would be fetched from the Sanity CMS
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
  // Include other news items from the news page as well
];

export default function NewsDetail({ params }) {
  const { slug } = params;
  const t = useTranslations('news');
  
  // Find the current news article by slug
  const news = allNews.find(n => n.slug === slug);
  
  // Handle case when news article is not found
  if (!news) {
    return (
      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <h1 className="text-3xl font-bold text-secondary">Article Not Found</h1>
        <p className="mt-4 text-lg text-secondary-light">The article you're looking for doesn't exist or has been moved.</p>
        <Link 
          href="/news" 
          className="mt-8 inline-block text-primary-dark hover:text-primary transition-colors"
        >
          Back to News
        </Link>
      </div>
    );
  }
  
  // Format the date
  const formattedDate = new Date(news.date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  
  // Get related articles (articles in the same category, excluding the current one)
  const relatedArticles = allNews
    .filter(n => n.category === news.category && n.id !== news.id)
    .slice(0, 3);
  
  return (
    <div className="bg-background">
      {/* Hero Section */}
      <div className="relative bg-secondary text-white">
        <div className="absolute inset-0 z-0">
          <img 
            src={news.image} 
            alt={news.title} 
            className="w-full h-full object-cover opacity-30"
          />
        </div>
        <div className="relative z-10 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
          <div className="max-w-3xl">
            <div className="flex flex-col sm:flex-row gap-2 items-center sm:items-start">
              <Link 
                href="/news" 
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
                {t('back_to_news')}
              </Link>
            </div>
            <div className="flex items-center gap-4 mt-6 mb-3">
              <span className="text-sm bg-primary/30 text-white px-3 py-1 rounded-full font-medium">
                {news.category}
              </span>
              <span className="text-sm text-white/70">
                {formattedDate}
              </span>
            </div>
            <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight mt-4">
              {news.title}
            </h1>
          </div>
        </div>
      </div>
      
      {/* Content Section */}
      <div className="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
          <div className="lg:col-span-2">
            <article className="prose prose-lg max-w-none">
              <div dangerouslySetInnerHTML={{ __html: news.content }} />
            </article>
            
            {/* Social Share */}
            <div className="mt-12 border-t border-gray-200 pt-8">
              <h3 className="text-lg font-medium text-secondary">{t('share_this')}</h3>
              <div className="flex space-x-6 mt-4">
                <a href="#" className="text-gray-400 hover:text-primary-dark">
                  <span className="sr-only">Facebook</span>
                  <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fillRule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clipRule="evenodd" />
                  </svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-primary-dark">
                  <span className="sr-only">Twitter</span>
                  <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                  </svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-primary-dark">
                  <span className="sr-only">LinkedIn</span>
                  <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                  </svg>
                </a>
                <a href="#" className="text-gray-400 hover:text-primary-dark">
                  <span className="sr-only">Email</span>
                  <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
          
          {/* Sidebar */}
          <div className="lg:col-span-1">
            {/* Related Articles */}
            {relatedArticles.length > 0 && (
              <div className="bg-primary/10 rounded-lg p-6 shadow-sm">
                <h3 className="text-xl font-semibold text-secondary mb-4">{t('related_articles')}</h3>
                <div className="space-y-6">
                  {relatedArticles.map((article) => (
                    <div key={article.id} className="group">
                      <Link href={`/news/${article.slug}`} className="flex gap-4">
                        <div className="w-20 h-20 flex-shrink-0 rounded overflow-hidden">
                          <img 
                            src={article.image} 
                            alt={article.title} 
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                          />
                        </div>
                        <div className="flex-1">
                          <h4 className="text-base font-medium text-secondary group-hover:text-primary-dark transition-colors line-clamp-2">
                            {article.title}
                          </h4>
                          <p className="text-sm text-gray-500 mt-1">
                            {new Date(article.date).toLocaleDateString('en-US', {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric'
                            })}
                          </p>
                        </div>
                      </Link>
                    </div>
                  ))}
                </div>
                <div className="mt-6">
                  <Link
                    href="/news"
                    className="text-base font-medium text-primary-dark hover:text-primary transition-colors flex items-center"
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
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clipRule="evenodd"
                      />
                    </svg>
                  </Link>
                </div>
              </div>
            )}
            
            {/* Contact Box */}
            <div className="bg-secondary text-white rounded-lg p-6 shadow-sm mt-8">
              <h3 className="text-xl font-semibold mb-4">Contact Us</h3>
              <p className="mb-4">
                Have questions about this article or want to learn more about our programs?
              </p>
              <Link
                href="/contact"
                className="block w-full text-center bg-white text-secondary px-5 py-2.5 rounded-md font-medium hover:bg-primary transition-colors"
              >
                Get in Touch
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
