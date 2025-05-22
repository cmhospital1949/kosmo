import { createIntl } from 'next-intl';

// Default messages (english)
import enMessages from '@/messages/en.json';
import koMessages from '@/messages/ko.json';

const messages = {
  'en': enMessages,
  'ko': koMessages
};

/**
 * Get translations for the given locale
 * @param {string} locale - The locale to use (e.g., 'en', 'ko')
 * @returns {Object} - The translations object for the given locale
 */
export function getTranslations(locale = 'en') {
  return messages[locale] || messages['en'];
}

/**
 * Create an internationalization instance for the given locale
 * @param {string} locale - The locale to use
 * @returns {Object} - The internationalization instance
 */
export function createI18nInstance(locale = 'en') {
  return createIntl({
    locale,
    messages: getTranslations(locale)
  });
}

/**
 * Get a translated string by key
 * @param {string} key - The key of the string to translate (e.g., 'navigation.home')
 * @param {string} locale - The locale to use
 * @returns {string} - The translated string
 */
export function t(key, locale = 'en') {
  const messages = getTranslations(locale);
  
  // Handle nested keys (e.g., 'navigation.home')
  const keyParts = key.split('.');
  let result = messages;
  
  for (const part of keyParts) {
    result = result?.[part];
    if (result === undefined) break;
  }
  
  return result || key;
}

/**
 * Format a date based on the given locale
 * @param {Date|string} date - The date to format
 * @param {Object} options - The options for formatting
 * @param {string} locale - The locale to use
 * @returns {string} - The formatted date
 */
export function formatDate(date, options = {}, locale = 'en') {
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  
  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    ...options
  }).format(dateObj);
}

/**
 * Format a number based on the given locale
 * @param {number} number - The number to format
 * @param {Object} options - The options for formatting
 * @param {string} locale - The locale to use
 * @returns {string} - The formatted number
 */
export function formatNumber(number, options = {}, locale = 'en') {
  return new Intl.NumberFormat(locale, options).format(number);
}

/**
 * Format currency based on the given locale
 * @param {number} amount - The amount to format
 * @param {string} currency - The currency code (e.g., 'KRW', 'USD')
 * @param {string} locale - The locale to use
 * @returns {string} - The formatted currency
 */
export function formatCurrency(amount, currency = 'KRW', locale = 'en') {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
    maximumFractionDigits: 0
  }).format(amount);
}
