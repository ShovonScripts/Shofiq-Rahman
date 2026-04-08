// resources/js/app.js
import './bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap

import '../css/app.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { InertiaProgress } from '@inertiajs/progress'
import AppLayout from './Layouts/AppLayout.vue'

// ✅ Ziggy (named routes) — rely on @routes (window.Ziggy) only
import { ZiggyVue } from 'ziggy-js'

createInertiaApp({
  title: (title) => (title ? `${title} - App` : 'App'),

  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    const key = `./Pages/${name}.vue`
    const page = pages[key]

    if (!page) {
      console.error(`[Inertia] Page not found: ${key}`)
      throw new Error(`Page not found: ${key}`)
    }

    // Default layout for every page, unless the page already sets one
    page.default.layout ??= AppLayout
    return page
  },

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      // ⚠️ No config object passed — plugin will read window.Ziggy injected by @routes
      .use(ZiggyVue)
      .mount(el)
  },
})

// Inertia progress bar
InertiaProgress.init({
  delay: 250,
  color: '#4B5563',
  includeCSS: true,
  showSpinner: true,
})
