/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        whatsapp: {
          DEFAULT: '#25D366',
          dark: '#075E54',
          teal: '#128C7E',
        },
        dark: {
          bg: '#111b21',
          paper: '#202c33',
          input: '#2a3942',
        }
      }
    },
  },
  plugins: [],
}
