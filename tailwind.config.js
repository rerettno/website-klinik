/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php", // File PHP di root folder
    "./admin/**/*.php", // Semua file PHP di folder admin
    "./**/*.html", // Jika ada file HTML
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
