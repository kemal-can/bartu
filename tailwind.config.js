/** @type {import('tailwindcss').Config} */

const colors = require('tailwindcss/colors')
const plugin = require('tailwindcss/plugin')
const defaultTheme = require('tailwindcss/defaultTheme')

// https://tailwindcss.com/docs/customizing-colors#naming-your-colors
// https://github.com/adamwathan/tailwind-css-variable-text-opacity-demo

function colorVariantWithOpacity({
  opacityVariable,
  opacityValue,
  color,
  key,
}) {
  if (opacityValue !== undefined) {
    return `rgba(var(--color-${color}-${key}), ${opacityValue})`
  }
  if (opacityVariable !== undefined) {
    return `rgba(var(--color-${color}-${key}), var(${opacityVariable}, 1))`
  }
  return `rgb(var(--color-${color}-${key}))`
}

function colorVariantCallback(attributes, color, key) {
  return colorVariantWithOpacity({
    opacityVariable: attributes.opacityVariable,
    opacityValue: attributes.opacityValue,
    color: color,
    key: key,
  })
}

function generateColorVariant(color) {
  return {
    50: attributes => colorVariantCallback(attributes, color, 50),
    100: attributes => colorVariantCallback(attributes, color, 100),
    200: attributes => colorVariantCallback(attributes, color, 200),
    300: attributes => colorVariantCallback(attributes, color, 300),
    400: attributes => colorVariantCallback(attributes, color, 400),
    500: attributes => colorVariantCallback(attributes, color, 500),
    600: attributes => colorVariantCallback(attributes, color, 600),
    700: attributes => colorVariantCallback(attributes, color, 700),
    800: attributes => colorVariantCallback(attributes, color, 800),
    900: attributes => colorVariantCallback(attributes, color, 900),
  }
}

module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],

  safelist: [
    // Highlights
    'bg-warning-500',
    'bg-info-500',
    // Fields cols width
    'col-spam-12',
    'sm:col-span-6',
    // Cards cols width
    'w-full',
    'lg:w-1/2',
    // Coolean column centering
    'text-center',
    // Webhook workflow action attributes
    '!pl-16',
    // TinyMCE
    'tox-tinymce',
    'tox',
    // Chartist
    'chartist-tooltip',
    'ct-label',
    // https://tailwindcss.com/docs/content-configuration#safelisting-classes
    {
      pattern: /^chart-.*/,
    },
    {
      pattern: /^ct-.*/,
    },
  ],

  darkMode: 'class', // or 'media' or 'class'

  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
      },
      height: {
        navbar: 'var(--navbar-height)',
      },
    },

    colors: {
      transparent: 'transparent',
      current: 'currentColor',

      black: colors.black,
      white: colors.white,

      neutral: generateColorVariant('neutral'),
      danger: generateColorVariant('danger'),
      warning: generateColorVariant('warning'),
      success: generateColorVariant('success'),
      info: generateColorVariant('info'),
      primary: generateColorVariant('primary'),
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/typography'),
    require('./resources/js/tailwindcss/plugins/all'),
    require('./resources/js/tailwindcss/plugins/tinymce'),
    require('./resources/js/tailwindcss/plugins/chartist'),
    require('./resources/js/tailwindcss/plugins/mail'),
  ],
}
