/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  images: {
    domains: ['kosmo.or.kr', 'www.kosmo.or.kr'],
  },
  i18n: {
    locales: ['en', 'ko'],
    defaultLocale: 'en',
    localeDetection: true,
  },
};

module.exports = nextConfig;
