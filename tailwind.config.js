// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
// import typography from '@tailwindcss/typography' // ← enable if installed
// import aspectRatio from '@tailwindcss/aspect-ratio' // ← optional
// import lineClamp from '@tailwindcss/line-clamp' // ← optional

/** @type {import('tailwindcss').Config} */
export default {
  // ✅ No dark mode
  // darkMode: false, // (omitted is fine; Tailwind defaults to 'media' off behavior for variants you don't use)

  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{vue,js,ts}',
    './storage/framework/views/**/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './app/Http/Controllers/**/*.php',
  ],

  future: {
    hoverOnlyWhenSupported: true, // better mobile hover behavior
  },

  theme: {
    container: {
      center: true,
      padding: {
        DEFAULT: '1rem',
        sm: '2rem',
        lg: '4rem',
        xl: '5rem',
        '2xl': '6rem',
      },
      // Wide max so your reading width can be controlled per component
      screens: { '2xl': '1320px' },
    },

    extend: {
      fontFamily: {
        sans: ['Hind Siliguri', 'Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
        serif: ['Noto Serif Bengali', ...defaultTheme.fontFamily.serif],
        body: ['Hind Siliguri', ...defaultTheme.fontFamily.sans],
        heading: ['Noto Serif Bengali', ...defaultTheme.fontFamily.serif],
      },

      // Token-driven palette (reads from your app.css variables)
      colors: {
        primary: {
          50:  '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
        ink: 'var(--ink)',
        muted: 'var(--muted)',
        bg: 'var(--bg)',
        brand: {
          DEFAULT: 'var(--brand, #2563eb)',
          600: 'var(--brand-600, #2563eb)',
          700: 'var(--brand-700, #1d4ed8)',
        },
        surface: {
          DEFAULT: 'var(--card, #ffffff)',
        },
        border: {
          DEFAULT: 'var(--border, #e5e7eb)',
        },
      },

      borderRadius: {
        '2xl': '1rem',
      },

      boxShadow: {
        card: '0 1px 2px 0 rgb(0 0 0 / 0.03), 0 1px 3px 0 rgb(0 0 0 / 0.06)',
      },

      // Smooth micro-animations for menus/drawers
      keyframes: {
        'fade-in': { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
        'scale-in': {
          '0%': { opacity: 0, transform: 'scale(0.98)' },
          '100%': { opacity: 1, transform: 'scale(1)' },
        },
        'slide-in-left': {
          '0%': { transform: 'translateX(-8px)', opacity: 0 },
          '100%': { transform: 'translateX(0)', opacity: 1 },
        },
      },
      animation: {
        'fade-in': 'fade-in .15s ease-out',
        'scale-in': 'scale-in .15s ease-out',
        'slide-in-left': 'slide-in-left .18s ease-out',
      },

      // Typography presets (works when plugin is enabled)
      typography: ({ theme }) => ({
        DEFAULT: {
          css: {
            color: theme('colors.ink'),
            a: {
              color: theme('colors.primary.600'),
              textDecoration: 'underline',
              '&:hover': { color: theme('colors.primary.700') },
            },
            h1: { fontFamily: theme('fontFamily.heading').join(', ') },
            h2: { fontFamily: theme('fontFamily.heading').join(', ') },
            h3: { fontFamily: theme('fontFamily.heading').join(', ') },
            blockquote: { borderLeftColor: 'var(--border, #e5e7eb)', color: 'var(--muted)' },
            code: { backgroundColor: 'transparent' },
          },
        },
      }),
    },
  },

  safelist: [
    // Prose variants for dynamic Markdown content
    'prose', 'prose-sm', 'prose-lg',
    // (Removed all `dark:` utilities to keep the build clean)
  ],

  plugins: [
    forms,
    // typography,    // ← enable if installed
    // aspectRatio,   // ← optional
    // lineClamp,     // ← optional
  ],
}
