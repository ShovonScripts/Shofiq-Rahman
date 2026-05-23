<script>
import { Head, Link, router } from '@inertiajs/vue3'
import ToastContainer from '@/Components/ToastContainer.vue'

export default {
  name: 'AppLayout',
  components: { Head, Link, ToastContainer },

  data() {
    return {
      isMobileMenuOpen: false,
      isUserDropdownOpen: false,
      searchQuery: '',
    }
  },

  computed: {
    user() {
      return this.$page?.props?.auth?.user || null
    },
    siteName() {
      return this.$page?.props?.settings?.site_name || 'Writer'
    },
    brandLogo() {
      return this.$page?.props?.brand?.logo || null
    },
    brandName() {
      return this.$page?.props?.brand?.name || this.siteName
    },
    socialLinks() {
      const s = this.$page?.props?.settings?.social_links || {}
      return {
        facebook: s.facebook || '',
        twitter: s.twitter || s.x || '',
        youtube: s.youtube || '',
        instagram: s.instagram || '',
      }
    },
    seo() {
      return this.$page?.props?.seo || {}
    },
    currentYear() {
      return new Date().getFullYear()
    },
    navItems() {
      const r = (...args) => {
        try { return this.route ? this.route(...args) : null } catch { return null }
      }
      return [
        { label: 'হোম',    href: r('home') || '/',                path: '/' },
        { label: 'লেখালেখি',    href: r('blog') || '/blog',            path: '/blog' },
        { label: 'বই',   href: r('books.index') || '/books',    path: '/books' },
        { label: 'পরিচিতি',   href: r('about') || '/about',          path: '/about' },
        { label: 'যোগাযোগ', href: r('contact') || '/contact',      path: '/contact' },
      ]
    },
  },

  watch: {
    '$page.url'() {
      this.isMobileMenuOpen = false
      this.isUserDropdownOpen = false
    },
  },

  methods: {
    isActive(path) {
      const url = this.$page?.url || '/'
      const current = url.split(/[?#]/)[0] || '/'
      return path === '/' ? current === '/' : current.startsWith(path)
    },

    toggleMobileMenu() {
      this.isMobileMenuOpen = !this.isMobileMenuOpen
      if (this.isMobileMenuOpen) this.isUserDropdownOpen = false
    },
    closeMobileMenu() { this.isMobileMenuOpen = false },

    toggleUserDropdown() {
      this.isUserDropdownOpen = !this.isUserDropdownOpen
      if (this.isUserDropdownOpen) this.isMobileMenuOpen = false
    },
    closeUserDropdown() { this.isUserDropdownOpen = false },

    logout() { router.post('/logout', {}, { preserveScroll: true }) },

    handleClickOutside(event) {
      if (!event.target.closest('.user-dropdown') && this.isUserDropdownOpen) {
        this.closeUserDropdown()
      }
      if (!event.target.closest('#primary-navigation') &&
          !event.target.closest('#mobile-menu-button') &&
          this.isMobileMenuOpen) {
        this.closeMobileMenu()
      }
    },
    handleKeydown(e) {
      if (e.key === 'Escape') {
        if (this.isUserDropdownOpen) this.closeUserDropdown()
        if (this.isMobileMenuOpen) this.closeMobileMenu()
      }
    },
    handleResize() {
      if (window.innerWidth >= 768 && this.isMobileMenuOpen) {
        this.closeMobileMenu()
      }
    },

    search() {
      if (this.searchQuery.trim()) {
        router.get('/search', { q: this.searchQuery.trim() })
      }
    },

    injectAnalytics() {
      const s = this.seo

      if (s.google_analytics_id) {
        const el = document.createElement('script')
        el.async = true
        el.src = `https://www.googletagmanager.com/gtag/js?id=${s.google_analytics_id}`
        document.head.appendChild(el)

        const el2 = document.createElement('script')
        el2.textContent = `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${s.google_analytics_id}');`
        document.head.appendChild(el2)
      }

      if (s.google_tag_manager_id) {
        const el = document.createElement('script')
        el.textContent = `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','${s.google_tag_manager_id}');`
        document.head.appendChild(el)

        const noscript = document.createElement('noscript')
        const iframe = document.createElement('iframe')
        iframe.src = `https://www.googletagmanager.com/ns.html?id=${s.google_tag_manager_id}`
        iframe.height = '0'
        iframe.width = '0'
        iframe.style.display = 'none'
        iframe.style.visibility = 'hidden'
        noscript.appendChild(iframe)
        document.body.insertBefore(noscript, document.body.firstChild)
      }

      if (s.facebook_pixel_id) {
        const el = document.createElement('script')
        el.textContent = `!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','${s.facebook_pixel_id}');fbq('track','PageView');`
        document.head.appendChild(el)

        const noscript = document.createElement('noscript')
        const img = document.createElement('img')
        img.height = '1'
        img.width = '1'
        img.style.display = 'none'
        img.src = `https://www.facebook.com/tr?id=${s.facebook_pixel_id}&ev=PageView&noscript=1`
        noscript.appendChild(img)
        document.body.insertBefore(noscript, document.body.firstChild)
      }
    },
  },

  mounted() {
    document.addEventListener('click', this.handleClickOutside)
    window.addEventListener('keydown', this.handleKeydown)
    window.addEventListener('resize', this.handleResize)
    this.injectAnalytics()
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
    window.removeEventListener('keydown', this.handleKeydown)
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-bg text-ink transition-colors duration-200">
    <!-- Global Meta Tags -->
    <Head>
      <meta name="keywords" :content="seo.meta_keywords" />
      <meta name="google-site-verification" :content="seo.google_site_verification" />
      <meta name="msvalidate.01" :content="seo.bing_site_verification" />
      <meta name="yandex-verification" :content="seo.yandex_site_verification" />
      <meta property="fb:app_id" :content="seo.facebook_app_id" />
      <meta name="twitter:site" :content="seo.twitter_handle" />
      <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <!-- Skip to content -->
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 bg-blue-600 text-white px-3 py-2 rounded-lg font-sans">
      মূল কন্টেন্টে যান
    </a>

    <!-- Top Nav -->
    <nav class="bg-white/80 backdrop-blur border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Brand -->
          <div class="flex items-center">
            <Link :href="route('home')" class="flex items-center gap-3 text-ink hover:opacity-90 focus-bangla rounded">
              <img v-if="brandLogo" :src="brandLogo" :alt="brandName" class="h-10 w-auto object-contain" loading="lazy" />
              <div v-else class="leading-tight">
                <span class="text-xl font-bold heading-bangla">{{ brandName }}</span>
                <span class="block text-xs text-muted -mt-0.5 font-sans">লেখক ও সাহিত্যিক</span>
              </div>
            </Link>
          </div>

          <!-- Desktop Nav -->
          <div class="hidden md:flex items-center gap-2">
            <template v-for="item in navItems" :key="item.path">
              <Link :href="item.href"
                    :data-active="isActive(item.path)"
                    class="nav-link-bangla"
                    :class="isActive(item.path)
                      ? 'text-blue-700 bg-blue-50 shadow-inner'
                      : 'text-gray-700 hover:text-blue-700 hover:bg-gray-100'">
                {{ item.label }}
              </Link>
            </template>

            <!-- RSS
            <a href="/feed" target="_blank" rel="noopener noreferrer"
               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-orange-600 hover:bg-gray-100 transition-colors flex items-center focus-bangla font-sans">
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6.18 15.64a2.18 2.18 0 012.18 2.18C8.36 19 7.38 20 6.18 20 5 20 4 19 4 17.82a2.18 2.18 0 012.18-2.18zM4 4.44A15.56 15.56 0 0119.56 20h-2.22A13.34 13.34 0 004 6.66v-2.22zm0 5.66a9.9 9.9 0 019.9 9.9h-2.22a7.68 7.68 0 00-7.68-7.68V10.1z"/>
              </svg>
              আরএসএস
            </a> -->

            <!-- Search -->
            <form @submit.prevent="search" class="relative ml-2">
              <input v-model="searchQuery" type="search" placeholder="পোস্ট খুঁজুন…" aria-label="খোঁজ"
                class="w-48 pl-9 pr-3 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 font-sans" />
              <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
            </form>

            <!-- User -->
            <div v-if="user" class="relative user-dropdown ml-2">
              <button @click="toggleUserDropdown"
                      class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100 focus-bangla"
                      aria-haspopup="true" :aria-expanded="isUserDropdownOpen">
                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white grid place-items-center text-sm font-semibold">
                  {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div class="hidden lg:block text-left">
                  <p class="text-sm font-medium text-ink font-sans">{{ user.name || 'User' }}</p>
                  <p class="text-xs text-muted capitalize font-sans">{{ user.role === 'admin' ? 'অ্যাডমিন' : user.role === 'author' ? 'লেখক' : 'ব্যবহারকারী' }}</p>
                </div>
                <svg class="w-4 h-4 text-muted transition-transform" :class="{ 'rotate-180': isUserDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div v-show="isUserDropdownOpen"
                   class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                <div v-if="user.role === 'admin' || user.role === 'author'">
                  <Link href="/dashboard" @click="closeUserDropdown"
                        class="flex items-center px-4 py-2 text-sm text-ink hover:bg-gray-100 font-sans">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    ড্যাশবোর্ড
                  </Link>
                </div>
                <Link href="/profile" @click="closeUserDropdown"
                      class="flex items-center px-4 py-2 text-sm text-ink hover:bg-gray-100 font-sans">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  প্রোফাইল
                </Link>

                <div class="border-t border-gray-200 my-1"></div>

                <Link v-if="user.role === 'admin'" href="/dashboard/settings" @click="closeUserDropdown"
                      class="flex items-center px-4 py-2 text-sm text-ink hover:bg-gray-100 font-sans">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  সেটিংস
                </Link>

                <div class="border-t border-gray-200 my-1"></div>

                <button @click="logout"
                        class="flex items-center w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 font-sans">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                  লগআউট
                </button>
              </div>
            </div>

            <!-- Auth CTA -->
            <Link v-else href="/login"
                  class="ml-2 inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 focus-bangla font-sans">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
              </svg>
              লগইন
            </Link>
          </div>

          <!-- Mobile Controls -->
          <div class="md:hidden flex items-center gap-1">
            <button id="mobile-menu-button" @click="toggleMobileMenu"
              class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus-bangla"
              aria-controls="primary-navigation" :aria-expanded="isMobileMenuOpen">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="isMobileMenuOpen" class="md:hidden border-t border-gray-200 py-4" id="primary-navigation">
          <div class="space-y-2">
            <template v-for="item in navItems" :key="item.path">
              <Link :href="item.href" @click="closeMobileMenu"
                    :class="[
                      'flex items-center px-4 py-3 rounded-lg text-base font-medium font-sans',
                      isActive(item.path)
                        ? 'text-blue-700 bg-blue-50 border border-blue-200'
                        : 'text-gray-700 hover:text-blue-700 hover:bg-gray-100'
                    ]">
                {{ item.label }}
              </Link>
            </template>

            <a href="/feed" target="_blank" rel="noopener noreferrer" @click="closeMobileMenu"
               class="flex items-center px-4 py-3 rounded-lg text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-gray-100 font-sans">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6.18 15.64a2.18 2.18 0 012.18 2.18C8.36 19 7.38 20 6.18 20 5 20 4 19 4 17.82a2.18 2.18 0 012.18-2.18zM4 4.44A15.56 15.56 0 0119.56 20h-2.22A13.34 13.34 0 004 6.66v-2.22zm0 5.66a9.9 9.9 0 019.9 9.9h-2.22a7.68 7.68 0 00-7.68-7.68V10.1z"/>
              </svg>
              আরএসএস
            </a>

            <div class="px-4 py-3">
              <form @submit.prevent="search" class="relative">
                <input v-model="searchQuery" type="search" placeholder="পোস্ট খুঁজুন…" aria-label="খোঁজ"
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 font-sans" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </div>
              </form>
            </div>

            <div v-if="user" class="pt-4 border-t border-gray-200">
              <div class="px-4 py-2 text-sm text-muted font-sans">লগইন করেছেন</div>
              <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white grid place-items-center font-semibold">
                  {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div>
                  <div class="text-sm font-medium text-ink font-sans">{{ user.name }}</div>
                  <div class="text-xs text-muted capitalize font-sans">{{ user.role === 'admin' ? 'অ্যাডমিন' : user.role === 'author' ? 'লেখক' : 'ব্যবহারকারী' }}</div>
                </div>
              </div>
              <div class="mt-2 space-y-1">
                <Link v-if="user.role === 'admin' || user.role === 'author'" href="/dashboard" @click="closeMobileMenu"
                      class="flex items-center px-4 py-3 text-sm text-ink hover:bg-gray-100 rounded-lg font-sans">
                  ড্যাশবোর্ড
                </Link>
                <Link href="/profile" @click="closeMobileMenu"
                      class="flex items-center px-4 py-3 text-sm text-ink hover:bg-gray-100 rounded-lg font-sans">
                  প্রোফাইল
                </Link>
                <Link v-if="user.role === 'admin'" href="/dashboard/settings" @click="closeMobileMenu"
                      class="flex items-center px-4 py-3 text-sm text-ink hover:bg-gray-100 rounded-lg font-sans">
                  সেটিংস
                </Link>
                <button @click="logout"
                        class="flex items-center w-full text-left px-4 py-3 text-sm text-red-700 hover:bg-red-50 rounded-lg font-sans">
                  লগআউট
                </button>
              </div>
            </div>

            <Link v-else href="/login" @click="closeMobileMenu"
                  class="flex items-center justify-center mx-4 mt-2 px-4 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 font-sans">
              লগইন
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Flash -->
    <div v-if="$page.props.flash?.success" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
      <div class="bg-green-50 border border-green-200 rounded-lg p-4 shadow-sm">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-green-800 font-medium font-sans">{{ $page.props.flash.success }}</span>
        </div>
      </div>
    </div>

    <!-- Main -->
    <main id="main-content" class="flex-1 max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center gap-8">
          <Link :href="route('home')" class="flex flex-col items-center gap-2 text-ink hover:opacity-90 focus-bangla rounded">
            <img v-if="brandLogo" :src="brandLogo" :alt="brandName" class="h-12 w-auto object-contain" loading="lazy" />
            <div v-else class="text-center">
              <span class="text-2xl font-bold heading-bangla">{{ brandName }}</span>
              <p class="text-sm text-muted mt-1 font-sans">লেখক ও সাহিত্যিক</p>
            </div>
          </Link>

          <div class="flex items-center gap-4">
            <a v-if="socialLinks.facebook" :href="socialLinks.facebook" target="_blank" rel="noopener noreferrer"
               class="text-muted hover:text-blue-600 p-2 rounded-lg hover:bg-gray-100 focus-bangla" aria-label="Facebook">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" rel="noopener noreferrer"
               class="text-muted hover:text-blue-400 p-2 rounded-lg hover:bg-gray-100 focus-bangla" aria-label="Twitter/X">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            <a v-if="socialLinks.youtube" :href="socialLinks.youtube" target="_blank" rel="noopener noreferrer"
               class="text-muted hover:text-red-600 p-2 rounded-lg hover:bg-gray-100 focus-bangla" aria-label="YouTube">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
            <a v-if="socialLinks.instagram" :href="socialLinks.instagram" target="_blank" rel="noopener noreferrer"
               class="text-muted hover:text-pink-600 p-2 rounded-lg hover:bg-gray-100 focus-bangla" aria-label="Instagram">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
          </div>

<div class="text-center text-muted text-sm font-sans">
  © {{ currentYear }} {{ brandName }}. সর্বস্বত্ব সংরক্ষিত।
  <div class="mt-2">
    <a 
      href="https://prodo.top" 
      target="_blank" 
      rel="noopener noreferrer"
      class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-gray-700 transition-colors duration-200"
    >
      Crafted by 
      <span class="font-medium text-gray-600">ProDo</span>
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
      </svg>
    </a>
  </div>
</div>
        </div>
      </div>
    </footer>
  </div>
  <ToastContainer />
</template>

<style scoped>
/* Scrollbar */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #f1f5f9; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>