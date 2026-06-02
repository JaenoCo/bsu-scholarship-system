/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        bsu: {
          red: '#b91c1c',
          redDark: '#991b1b',
          light: '#fef2f2'
        }
      }
    },
  },
  plugins: [],
};
