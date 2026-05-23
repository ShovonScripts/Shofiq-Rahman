<!-- resources/js/Pages/Blog.vue -->
<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

export default {
  name: 'Blog',
  layout: AppLayout,
  components: { Head, Link },

  props: {
    posts: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    allGenres: { type: Array, default: () => [] },
  },

  data() {
    return {
      q: this.filters?.q || '',
      pickedGenres: Array.isArray(this.filters?.genre) ? [...this.filters.genre] : [],
      submitting: false,
    }
  },

  computed: {
    hasFilters() {
      return (this.q && this.q.length > 0) || (this.pickedGenres?.length > 0)
    },
  },

  methods: {
    toggleGenre(slug) {
      const i = this.pickedGenres.indexOf(slug)
      if (i === -1) this.pickedGenres.push(slug)
      else this.pickedGenres.splice(i, 1)
      this.submit()
    },
    clearFilters() {
      this.q = ''
      this.pickedGenres = []
      this.submit()
    },
    submit(pageUrl = '/blog') {
      if (this.submitting) return
      this.submitting = true
      const params = {}
      if (this.q) params.q = this.q
      if (this.pickedGenres.length) params.genre = this.pickedGenres
      router.get(pageUrl, params, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onFinish: () => (this.submitting = false),
      })
    },
    go(link) {
      if (!link?.url || link?.active) return
      this.submit(link.url)
    },
  },
}
</script>

<template>
  <div>
    <Head>
      <title>লেখালেখি</title>
      <meta name="description" content="সকল বাংলা ব্লগ পোস্ট ব্রাউজ করুন। সার্চ এবং জেনার ফিল্টার ব্যবহার করুন।" />
    </Head>

    <!-- =========================
         Header (edge-to-edge on mobile)
         ========================= -->
    <header class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mb-6 sm:mb-8">
      <div class="px-3 sm:px-0 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 sm:gap-6">
        <div>
          <h1 class="heading-bangla text-2xl sm:text-3xl md:text-4xl mb-1.5 sm:mb-2">শব্দে শব্দে জীবনবোধ</h1>
          <p class="text-base sm:text-lg text-muted font-sans">সমকালীন প্রসঙ্গ, শাশ্বত বোধ</p>
        </div>

        <!-- Search -->
        <form class="flex gap-2 sm:gap-3 w-full lg:w-auto" @submit.prevent="submit()" role="search" aria-label="পোস্ট সার্চ">
          <div class="relative flex-1 lg:w-80">
            <input
              v-model.trim="q"
              type="search"
              name="q"
              inputmode="search"
              autocomplete="off"
              spellcheck="false"
              aria-label="পোস্ট খুঁজুন"
              :aria-busy="submitting ? 'true' : 'false'"
              class="input-bangla pl-10 py-2.5"
              placeholder="পোস্ট খুঁজুন..."
              :disabled="submitting"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <button
            type="submit"
            class="btn-bangla btn-bangla-primary whitespace-nowrap px-4 sm:px-6"
            :disabled="submitting"
            :aria-disabled="submitting ? 'true' : 'false'"
          >
            <span v-if="submitting" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              খুঁজছি...
            </span>
            <span v-else>খুঁজুন</span>
          </button>
        </form>
      </div>
    </header>

    <!-- =========================
         Genres (compact, mobile-friendly)
         ========================= -->
    <section v-if="allGenres?.length" class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mb-6 sm:mb-8">
      <div class="px-3 sm:px-0">
        <div class="flex flex-wrap gap-2.5 sm:gap-3" role="list" aria-label="জেনার ফিল্টার">
          <!-- All -->
          <button
            type="button"
            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[12px] sm:text-sm font-medium transition-all duration-200 focus-bangla font-sans"
            :class="pickedGenres.length === 0 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            @click="pickedGenres.length ? clearFilters() : null"
            :aria-pressed="pickedGenres.length === 0"
          >
            সব
          </button>

          <!-- Each Genre -->
          <button
            v-for="g in allGenres"
            :key="g.slug"
            type="button"
            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-[12px] sm:text-sm font-medium transition-all duration-200 focus-bangla font-sans"
            :class="pickedGenres.includes(g.slug) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            @click="toggleGenre(g.slug)"
            :aria-pressed="pickedGenres.includes(g.slug)"
          >
            {{ g.name }}
          </button>
        </div>

        <!-- Clear -->
        <div v-if="hasFilters" class="mt-3 sm:mt-4">
          <button
            type="button"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200 flex items-center gap-1 font-sans"
            @click="clearFilters"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            ফিল্টার সরান
          </button>
        </div>
      </div>
    </section>

    <!-- =========================
         Posts (compact grid, edge-to-edge on mobile)
         ========================= -->
    <section class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
      <div v-if="posts?.data?.length" class="px-3 sm:px-0 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5">
        <article
          v-for="p in posts.data"
          :key="p.id"
          class="group flex flex-col overflow-hidden rounded-md border border-gray-100 shadow-sm bg-white"
        >
          <!-- Media 16:9 -->
          <div class="relative aspect-w-16 aspect-h-9 overflow-hidden">
            <img
              v-if="p.cover_url"
              :src="p.cover_url"
              class="w-full h-full object-cover group-hover:scale-105 transition-smooth"
              :alt="p.title"
              loading="lazy"
              decoding="async"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-blue-100 to-purple-100 grid place-items-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </div>
          </div>

          <!-- Body -->
          <div class="p-3 sm:p-5 flex flex-col flex-1">
            <h3 class="heading-bangla text-base sm:text-lg mb-1.5 line-clamp-2 group-hover:text-blue-600 transition-colors">
              <Link :href="`/lekha/${p.slug}`" class="hover:no-underline focus-bangla rounded">
                {{ p.title }}
              </Link>
            </h3>

            <!-- Meta -->
            <div class="flex items-center text-xs sm:text-sm text-muted mb-2 sm:mb-3 gap-3 sm:gap-4 font-sans">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ p.published_at }}</span>
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ p.reading_time }} মিনিট</span>
              </div>
            </div>

            <!-- Excerpt -->
            <p class="text-muted text-[13px] sm:text-sm leading-relaxed-bangla line-clamp-3 mb-3 sm:mb-4 flex-1 font-sans">
              {{ p.excerpt }}
            </p>

            <!-- Chips -->
            <div class="flex flex-wrap gap-1.5 sm:gap-2 mt-auto">
              <!-- Categories -->
              <span
                v-for="c in p.categories || []"
                :key="`c-${p.id}-${c.slug}`"
                class="badge-bangla badge-bangla-muted"
              >
                #{{ c.name }}
              </span>

              <!-- Genres (click to filter) -->
              <button
                v-for="g in p.genres || []"
                :key="`g-${p.id}-${g.slug}`"
                type="button"
                class="badge-bangla transition-all duration-200 focus-bangla"
                :class="pickedGenres.includes(g.slug) ? 'badge-bangla-primary' : 'badge-bangla-muted hover:bg-gray-200'"
                @click.prevent="toggleGenre(g.slug)"
                :aria-pressed="pickedGenres.includes(g.slug)"
              >
                {{ g.name }}
              </button>
            </div>

            <!-- Read More -->
            <Link
              :href="`/lekha/${p.slug}`"
              class="inline-flex items-center mt-3 sm:mt-4 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors duration-200 group/readmore font-sans focus-bangla rounded"
            >
              সম্পূর্ণ পড়ুন
              <svg class="w-4 h-4 ml-1 transform group-hover/readmore:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </div>
        </article>
      </div>

      <!-- Empty -->
      <div v-else class="text-center py-14 sm:py-16 px-3">
        <div class="max-w-md mx-auto">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="heading-bangla text-xl sm:text-2xl text-gray-900 mb-2">কোনো পোস্ট পাওয়া যায়নি</h3>
          <p class="text-muted mb-6 font-sans text-sm sm:text-base">
            {{ hasFilters ? 'আপনার ফিল্টারের সাথে মিলছে না। ফিল্টার সরান বা অন্য কিছু খুঁজুন।' : 'এখনও কোনো পোস্ট প্রকাশিত হয়নি।' }}
          </p>
          <button
            v-if="hasFilters"
            type="button"
            class="btn-bangla btn-bangla-primary"
            @click="clearFilters"
          >
            ফিল্টার সরান
          </button>
        </div>
      </div>
    </section>

    <!-- =========================
         Pagination (compact)
         ========================= -->
    <nav
      v-if="posts?.links?.length"
      class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mt-8 sm:mt-12"
      role="navigation"
      aria-label="পৃষ্ঠা নেভিগেশন"
    >
      <div class="px-3 sm:px-0 flex justify-center">
        <div class="flex items-center space-x-1 bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-200 p-1.5 sm:p-2">
          <button
            v-for="(link, i) in posts.links"
            :key="i"
            type="button"
            class="min-w-[2.25rem] sm:min-w-[2.5rem] h-9 sm:h-10 flex items-center justify-center rounded-md sm:rounded-lg text-sm font-medium transition-all duration-200 focus-bangla font-sans"
            :class="[
              link.active
                ? 'bg-blue-600 text-white shadow-sm'
                : !link.url
                  ? 'text-gray-300 cursor-not-allowed'
                  : 'text-gray-700 hover:bg-gray-100',
              link.label.includes('Previous') || link.label.includes('Next') ? 'px-2.5 sm:px-3' : 'px-2'
            ]"
            :disabled="!link.url || link.active || submitting"
            :aria-current="link.active ? 'page' : undefined"
            @click.prevent="go(link)"
          >{{ link.label }}</button>
        </div>
      </div>
    </nav>

    <!-- =========================
         CTA (compact)
         ========================= -->
    <section v-if="posts?.data?.length" class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mt-8 sm:mt-12 mb-10 sm:mb-16">
      <div class="px-3 sm:px-0 rounded-md sm:rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 sm:border-blue-200 shadow-sm">
        <div class="px-4 sm:px-8 py-6 sm:py-8 text-center">
          <h3 class="heading-bangla text-xl sm:text-2xl mb-2 sm:mb-3">আরও কন্টেন্ট এক্সপ্লোর করুন</h3>
          <p class="text-muted mb-5 sm:mb-6 font-sans text-sm sm:text-base">সাহিত্যকর্ম সম্পর্কে আরও জানুন</p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <Link href="/books" class="btn-bangla btn-bangla-primary">বই দেখুন</Link>
            <Link href="/about" class="btn-bangla btn-bangla-secondary">লেখক সম্পর্কে</Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Line clamps (keep consistent with Home) */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* 16:9 aspect ratio without plugin */
.aspect-w-16 { position: relative; }
.aspect-h-9::before { content: ''; display: block; padding-bottom: 56.25%; }
.aspect-h-9 > * {
  position: absolute; inset: 0; width: 100%; height: 100%;
}

/* Smooth transitions */
.transition-smooth { transition: all 0.3s ease; }
</style>
