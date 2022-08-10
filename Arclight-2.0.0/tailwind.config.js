/** @type {import('tailwindcss').Config} */
module.exports = {
  theme: {
    extend: {},
  },
  mode: 'jit',
  content: ['./views/**/*{.ejs,html}',
    './views/*.ejs',
    './node_modules/flowbite/**/*.js'
  ],
  plugins: [
    require('flowbite/plugin')
  ]
}
