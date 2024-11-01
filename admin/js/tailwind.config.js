module.exports = {
  purge: {
    enabled: true,
    content: [
      './*.php',
    ],
  },
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {},
  },
  variants: {
    extend: {    
     margin: ['last'],
    }
  },
  plugins: [],
}
