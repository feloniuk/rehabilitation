/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
      "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],
  theme: {
      extend: {
          colors: {
              pink: {
                  '50': 'rgb(255, 240, 245)',
                  '100': 'rgb(255, 224, 233)',
                  '200': 'rgb(255, 212, 225)',
                  '300': 'rgb(255, 200, 217)',
                  '400': 'rgb(255, 188, 209)',
                  '500': 'rgb(255, 176, 201)',
                  '600': 'rgb(255, 164, 193)', 
                  '700': 'rgb(255, 152, 185)',
                  '800': 'rgb(255, 140, 177)',
                  '900': 'rgb(255, 128, 169)',
              }
          }
      }
  },
  plugins: [],
}