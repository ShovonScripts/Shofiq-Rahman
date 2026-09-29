// resources/js/app.js
import './bootstrap'

import '../css/app.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import AppLayout from './Layouts/AppLayout.vue'

// ✅ Ziggy (named routes) — rely on @routes (window.Ziggy) only
import { ZiggyVue } from 'ziggy-js'

createInertiaApp({
  title: (title) => title || '',
  progress: { delay: 250, color: '#4B5563', includeCSS: true, showSpinner: true },

  resolve: (name) => {
    // ✅ Lazy-load pages — only downloads the component when its route is visited
    const pages = import.meta.glob('./Pages/**/*.vue')
    const key = `./Pages/${name}.vue`
    const loadPage = pages[key]

    if (!loadPage) {
      console.error(`[Inertia] Page not found: ${key}`)
      throw new Error(`Page not found: ${key}`)
    }

    return loadPage().then((module) => {
      // Default layout for every page, unless the page already sets one
      module.default.layout ??= AppLayout
      return module
    })
  },

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      // ⚠️ No config object passed — plugin will read window.Ziggy injected by @routes
      .use(ZiggyVue)
      .mount(el)
  },
})
