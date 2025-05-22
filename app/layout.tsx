import React from 'react';
import { NextIntlClientProvider } from 'next-intl';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import { Providers } from './providers';

export const metadata = {
  title: 'KOSMO Foundation',
  description: 'Government-certified non-profit foundation for sports medicine and education',
};

export default async function RootLayout({
  children,
  params: { locale },
}) {
  let messages;
  try {
    messages = (await import(`../messages/${locale}.json`)).default;
  } catch (error) {
    // Fallback to English if messages for the locale are not found
    messages = (await import(`../messages/en.json`)).default;
  }

  return (
    <html lang={locale}>
      <body>
        <Providers>
          <NextIntlClientProvider locale={locale} messages={messages}>
            <div className="flex flex-col min-h-screen">
              <Navbar translations={messages.navigation} />
              <main className="flex-grow">{children}</main>
              <Footer translations={messages.footer} />
            </div>
          </NextIntlClientProvider>
        </Providers>
      </body>
    </html>
  );
}
