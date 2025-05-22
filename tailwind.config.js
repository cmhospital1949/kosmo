/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        // Based on Switch Research color scheme with KOSMO branding adjustments
        primary: {
          DEFAULT: '#EBF2CD', // Light green accent from Switch Research
          dark: '#C4D292',
          light: '#F5F9E7',
        },
        secondary: {
          DEFAULT: '#333333', // Dark text color
          light: '#666666',
        },
        background: {
          DEFAULT: '#FEFBF7', // Light cream background
          alt: '#F3F3F3',
        },
      },
      fontFamily: {
        sans: ['Assistant', 'sans-serif'],
        heading: ['Assistant', 'sans-serif'],
      },
      spacing: {
        '128': '32rem',
        '144': '36rem',
      },
      maxWidth: {
        '8xl': '90rem',
      },
    },
  },
  plugins: [],
}
