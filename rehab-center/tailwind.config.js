/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],
    theme: {
      extend: {
        colors: {
            pink: {
                '50': 'rgb(255, 224, 233)',
                '100': 'rgb(255, 204, 221)',
                '200': 'rgb(255, 184, 209)',
                '300': 'rgb(255, 164, 197)',
                '400': 'rgb(255, 144, 185)',
                '500': 'rgb(255, 124, 173)',
                '600': 'rgb(255, 104, 161)', // Основной розовый цвет
                '700': 'rgb(255, 84, 149)',
                '800': 'rgb(255, 64, 137)',
                '900': 'rgb(255, 44, 125)',
              }
        }
      }
    },
    plugins: [],
  }