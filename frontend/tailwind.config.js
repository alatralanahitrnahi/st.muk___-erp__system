/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        science: {
          50: '#eff6ff',
          500: '#2563eb',
          600: '#1d4ed8',
        },
        commerce: {
          50: '#ecfdf5',
          500: '#059669',
          600: '#047857',
        },
        arts: {
          50: '#faf5ff',
          500: '#7c3aed',
          600: '#6d28d9',
        },
      },
    },
  },
  plugins: [],
}
