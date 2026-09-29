<script>
import { Link, router } from '@inertiajs/vue3'
import ToastContainer from '@/Components/ToastContainer.vue'

export default {
  name: 'AdminLayout',
  components: { Link, ToastContainer },

  data() {
    return {
      isSidebarOpen: false,
      isProfileDropdownOpen: false,
      touchStartX: 0,
    }
  },

  computed: {
    user() {
      return this.$page?.props?.auth?.user || null
    },
    currentRoute() {
      return this.$page?.url || '/'
    },
    currentYear() {
      return new Date().getFullYear()
    },
    // Centralize sidebar nav; edit here to add/remove items
    nav() {
      const r = (...args) => {
        try { return this.route ? this.route(...args) : null } catch { return null }
      }
      return [
        {
          heading: 'Main',
          items: [
            { label: 'Dashboard', href: r('dashboard') || '/dashboard', path: '/dashboard', icon: 'home' },
          ],
        },
        {
          heading: 'Content',
          items: [
            { label: 'Homepage', adminOnly: true, href: r('dashboard.homepage.edit')  || '/dashboard/homepage',  path: '/dashboard/homepage',  icon: 'layout' },
            { label: 'Posts',     href: r('dashboard.posts.index')     || '/dashboard/posts',     path: '/dashboard/posts',     icon: 'posts' },
            { label: 'Books',     href: r('dashboard.books.index')     || '/dashboard/books',     path: '/dashboard/books',     icon: 'books' },
            { label: 'Genres',    href: r('dashboard.genres.index')    || '/dashboard/genres',    path: '/dashboard/genres',    icon: 'tag' },
            { label: 'Categories',href: r('dashboard.categories.index')|| '/dashboard/categories',path: '/dashboard/categories',icon: 'grid' },
          ],
        },
        {
          heading: 'Author',
          items: [
            { label: 'Profile',       href: r('dashboard.about.edit')           || '/dashboard/about',           path: '/dashboard/about',           icon: 'user' },
            { label: 'Achievements',  href: r('dashboard.about.achievements.index') || '/dashboard/about/achievements', path: '/dashboard/about/achievements', icon: 'check' },
            { label: 'Life Events',   href: r('dashboard.about.events.index')    || '/dashboard/about/events',    path: '/dashboard/about/events',    icon: 'calendar' },
          ],
        },
        {
          heading: 'Interactions',
          items: [
            { label: 'Comments', href: r('dashboard.comments.index') || '/dashboard/comments', path: '/dashboard/comments', icon: 'chat' },
            { label: 'Contacts', adminOnly: true, href: r('dashboard.contacts.index') || '/dashboard/contacts', path: '/dashboard/contacts', icon: 'mail' },
          ],
        },
        {
          heading: 'Administration',
          items: [
            { label: 'Users', adminOnly: true,   href: r('dashboard.users.index')    || '/dashboard/users',    path: '/dashboard/users',    icon: 'users' },
            { label: 'SEO Manager', href: r('dashboard.seo.index') || '/dashboard/seo', path: '/dashboard/seo', icon: 'search' },
            { label: 'Settings',    href: r('dashboard.settings.edit') || '/dashboard/settings', path: '/dashboard/settings', icon: 'settings' },
          ],
        },
      ].map(group => ({
        ...group,
        items: group.items.filter(item => !item.adminOnly || this.user?.role === 'admin'),
      })).filter(group => group.items.length)
    },
  },

  mounted() {
    window.addEventListener('keydown', this.onKeydown)
    window.addEventListener('click', this.onGlobalClick, true)
    // Touch swipe to close sidebar on mobile
    window.addEventListener('touchstart', this.onTouchStart, { passive: true })
    window.addEventListener('touchend', this.onTouchEnd)
  },

  beforeUnmount() {
    window.removeEventListener('keydown', this.onKeydown)
    window.removeEventListener('click', this.onGlobalClick, true)
    window.removeEventListener('touchstart', this.onTouchStart)
    window.removeEventListener('touchend', this.onTouchEnd)
  },

  methods: {
    onKeydown(e) {
      if (e.key === 'Escape') {
        if (this.isSidebarOpen) this.closeSidebar()
        if (this.isProfileDropdownOpen) this.closeProfileDropdown()
      }
    },
    onGlobalClick(e) {
      const withinProfileBtn  = e.target.closest('#profileDropdownButton')
      const withinProfileMenu = e.target.closest('#profileDropdownMenu')
      if (!withinProfileBtn && !withinProfileMenu && this.isProfileDropdownOpen) {
        this.closeProfileDropdown()
      }

      const withinSidebar = e.target.closest('#adminSidebar')
      const sidebarToggle = e.target.closest('#sidebarToggle')
      if (!withinSidebar && !sidebarToggle && this.isSidebarOpen) {
        this.closeSidebar()
      }
    },

    isActive(path) {
      return this.currentRoute === path || this.currentRoute.startsWith(path + '/')
    },

    logout() {
      if (confirm('Are you sure you want to logout?')) {
        router.post('/logout')
      }
    },

    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen
    },
    closeSidebar() {
      this.isSidebarOpen = false
    },

    toggleProfileDropdown(e) {
      e.stopPropagation()
      this.isProfileDropdownOpen = !this.isProfileDropdownOpen
    },
    closeProfileDropdown() {
      this.isProfileDropdownOpen = false
    },

    // Touch handling for sidebar swipe gestures
    onTouchStart(e) {
      this.touchStartX = e.touches[0].clientX
    },
    onTouchEnd(e) {
      const touchEndX = e.changedTouches[0].clientX
      const diff = this.touchStartX - touchEndX
      // Swipe left to open (started from left edge), swipe right to close
      if (Math.abs(diff) > 50) {
        if (diff > 0 && this.touchStartX < 60 && !this.isSidebarOpen) {
          this.isSidebarOpen = true
        } else if (diff < 0 && this.isSidebarOpen) {
          this.isSidebarOpen = false
        }
      }
    },

    // Heroicons (outline 24px paths) — polished, consistent visual weight
    iconPath(name) {
      switch (name) {
        case 'home':
          return 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
        case 'layout':
          return 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'
        case 'posts':
          return 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'
        case 'books':
          return 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'
        case 'tag':
          return 'M7 7h.01M7 12h.01M7 17h.01M3 21h18M3 10h18M3 3h18'
        case 'grid':
          return 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z'
        case 'user':
          return 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        case 'check':
          return 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        case 'calendar':
          return 'M8 7V3m8 4V3M6 7h12a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2zM6 20h12a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z'
        case 'chat':
          return 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8 4.03-8 9-8 9 3.582 9 8z'
        case 'mail':
          return 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
        case 'users':
          return 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
        case 'settings':
          return 'M10.325 4.317a3.903 3.903 0 11-3.418 5.096l3.418-5.096zM15.586 6.707a3.903 3.903 0 103.417 5.097l-3.418-5.097zM12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z'
        case 'search':
          return 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'
        default:
          return ''
      }
    },
  },
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar Overlay (Mobile) -->
    <div
      v-if="isSidebarOpen"
      class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-200"
      @click="closeSidebar"
      aria-hidden="true"
    />

    <!-- Sidebar -->
    <aside
      id="adminSidebar"
      class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform duration-200 ease-out lg:transform-none lg:shadow-none"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo Section -->
      <div class="flex-shrink-0 px-5 py-5 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="flex-shrink-0">
            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
          </div>
          <div class="min-w-0">
            <h1 class="text-base font-bold text-gray-900 truncate">Writer Pro</h1>
            <p class="text-xs text-gray-500 uppercase tracking-wide">Admin</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-2 sm:px-4 py-4 sm:py-6 space-y-1 overflow-y-auto" aria-label="Sidebar Navigation">
        <template v-for="section in nav" :key="section.heading">
          <div class="mb-2">
            <h3 class="px-2 sm:px-3 text-[10px] sm:text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1 hidden sm:block">
              {{ section.heading }}
            </h3>
            <div class="space-y-0.5 sm:space-y-1">
              <Link
                v-for="item in section.items"
                :key="item.path"
                :href="item.href"
                class="nav-item group relative flex items-center gap-2 sm:gap-3 rounded-lg px-2 sm:px-3 py-2 sm:py-2.5 text-xs sm:text-sm font-medium transition-all duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500/50"
                :class="isActive(item.path)
                  ? 'bg-gradient-to-br from-blue-50 to-indigo-50/50 text-blue-700 border-l-3 border-blue-500 shadow-sm'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-3 border-transparent'"
                :aria-current="isActive(item.path) ? 'page' : undefined"
                @click="closeSidebar"
              >
                <!-- Icon - filled when active, outline when idle -->
                <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0 transition-all duration-150"
                     :class="isActive(item.path)
                       ? 'text-blue-600 bg-blue-100/60 p-0.5 rounded'
                       : 'text-gray-400 group-hover:text-gray-600'"
                     fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                  <path :d="iconPath(item.icon)"/>
                </svg>
                <span class="truncate">{{ item.label }}</span>
                <!-- Small dot indicator for active -->
                <span v-if="isActive(item.path)" class="absolute right-2 sm:right-3 w-1.5 h-1.5 rounded-full bg-blue-500 sm:hidden"></span>
              </Link>
            </div>
          </div>
        </template>
      </nav>

      <!-- Logout Section -->
      <div class="flex-shrink-0 border-t border-gray-100/80 p-3 sm:p-4">
        <button
          @click="logout"
          class="w-full inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-600 bg-gray-100/80 hover:bg-gray-200 hover:text-gray-900 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:bg-white active:scale-[0.98]"
          title="Logout"
        >
          <svg class="w-4 h-4 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          <span class="hidden sm:inline">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Header -->
      <header class="bg-white/80 backdrop-blur-md border-b border-gray-200/70 sticky top-0 z-30 shadow-sm">
        <div class="flex items-center justify-between px-3 sm:px-6 py-2.5 sm:py-4">
          <div class="flex items-center min-w-0">
            <!-- Mobile sidebar toggle -->
            <button
              id="sidebarToggle"
              @click="toggleSidebar"
              class="lg:hidden p-2 -ml-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 active:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50"
              aria-controls="adminSidebar"
              :aria-expanded="isSidebarOpen"
              aria-label="Toggle sidebar"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path v-if="!isSidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <div class="ml-2 sm:ml-3 md:ml-4 truncate">
              <h1 class="text-sm sm:text-lg font-semibold text-gray-900 truncate">{{ currentRoute === '/dashboard' ? 'Dashboard' : route().current()?.split('.').pop()?.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) || 'Dashboard' }}</h1>
              <p class="text-[10px] sm:text-xs text-gray-500 truncate hidden sm:block">Welcome back, {{ user?.name?.split(' ')[0] || 'Admin' }}</p>
            </div>
          </div>

          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- View Site (icon only on mobile, icon+text on sm+) -->
            <Link
              href="/"
              class="inline-flex items-center px-2 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              title="View Site"
            >
              <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <span class="hidden sm:inline">View Site</span>
            </Link>

            <!-- Profile Dropdown -->
            <div class="relative">
              <button
                id="profileDropdownButton"
                @click="toggleProfileDropdown"
                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                aria-haspopup="menu"
                :aria-expanded="isProfileDropdownOpen"
                aria-controls="profileDropdownMenu"
              >
                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-blue-500 rounded-full grid place-items-center text-white font-semibold text-sm">
                  {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div class="text-left hidden sm:block min-w-0 max-w-[120px]">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name || 'User' }}</p>
                  <p class="text-xs text-gray-500 truncate capitalize">{{ user?.role || 'Admin' }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="isProfileDropdownOpen"
                id="profileDropdownMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                role="menu"
              >

                <Link href="/dashboard/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors" role="menuitem">
                  Settings
                </Link>
                <div class="border-t border-gray-200 my-1"></div>
                <button
                  @click="logout"
                  class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors"
                  role="menuitem"
                >
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="mx-4 sm:mx-6 mt-4 sm:mt-6">
        <div class="bg-green-50 border border-green-200/70 rounded-xl p-3 sm:p-4 shadow-sm flex items-start gap-3">
          <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <span class="text-green-800 text-sm font-medium">{{ $page.props.flash.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash?.error" class="mx-4 sm:mx-6 mt-4 sm:mt-6">
        <div class="bg-red-50 border border-red-200/70 rounded-xl p-3 sm:p-4 shadow-sm flex items-start gap-3">
          <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <span class="text-red-800 text-sm font-medium">{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Page Content -->
      <section class="flex-1 p-4 sm:p-6 overflow-auto">
        <div class="max-w-7xl mx-auto">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 sm:p-6">
              <slot />
            </div>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="bg-white border-t border-gray-200 py-3 px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-0">
          <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
            © {{ currentYear }} Develop by jis.digital
          </div>
          <div class="text-xs text-gray-500">
            Writer Pro v.1
          </div>
        </div>
      </footer>
    </main>
  </div>
  <ToastContainer />
</template>

<style scoped>
/* Smooth, modern sidebar scrolling */
aside nav {
  scrollbar-width: thin;
  scrollbar-color: #e2e8f0 #f8fafc;
}
aside nav::-webkit-scrollbar { width: 6px; }
aside nav::-webkit-scrollbar-track { background: #f8fafc; border-radius: 3px; }
aside nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }
aside nav::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

/* Focus visible ring for nav items (outline only) */
.nav-item:focus-visible {
  outline: none;
  box-shadow: inset 0 0 0 2px white, 0 0 0 4px rgba(59, 130, 246, 0.5);
}

/* Transitions for smoother sidebar toggle */
aside {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Sidebar overlay fade */
div[role="complementary"][v-if="isSidebarOpen"] {
  transition: opacity 0.3s ease-in-out;
}

/* Truncate with ellipsis that works across breakpoints */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
