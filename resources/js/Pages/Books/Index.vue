<!-- resources/js/Pages/BooksIndex.vue -->
<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

export default {
  name: 'BooksIndex',
  layout: AppLayout,
  components: { Head, Link },

  props: {
    filters:   { type: Object, default: () => ({}) },
    books:     { type: Object, required: true },
    allGenres: { type: Array,  default: () => [] },
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
    submit(pageUrl = '/books') {
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
      <title>বই</title>
      <meta name="description" content="কাইজার চৌধুরীর প্রকাশিত বইগুলোর তালিকা" />
    </Head>

    <!-- Header (edge-to-edge on mobile) -->
    <header class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mb-6 sm:mb-8">
      <div class="px-3 sm:px-0 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 sm:gap-6">
        <div>
          <h1 class="heading-bangla text-2xl sm:text-3xl md:text-4xl mb-1.5 sm:mb-2">বইয়ের তালিকা</h1>
          <p class="text-base sm:text-lg text-muted font-sans">কাইজার চৌধুরীর প্রকাশিত বই</p>
        </div>

        <!-- Search -->
        <form class="flex gap-2 sm:gap-3 w-full lg:w-auto" @submit.prevent="submit()" role="search" aria-label="বই সার্চ">
          <div class="relative flex-1 lg:w-80">
            <input
              v-model.trim="q"
              type="search"
              name="q"
              inputmode="search"
              autocomplete="off"
              spellcheck="false"
              aria-label="বই খুঁজুন"
              :aria-busy="submitting ? 'true' : 'false'"
              class="input-bangla pl-10"
              placeholder="বই খুঁজুন..."
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
            class="btn-bangla btn-bangla-primary whitespace-nowrap px-5 sm:px-6"
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

    <!-- Genres (edge-to-edge on mobile) -->
    <section v-if="allGenres?.length" class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mb-6 sm:mb-8">
      <div class="px-3 sm:px-0 flex flex-wrap gap-2.5 sm:gap-3" role="list" aria-label="জেনার ফিল্টার">
        <!-- All -->
        <button
          type="button"
          class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 focus-bangla font-sans"
          :class="pickedGenres.length === 0 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          @click="pickedGenres.length ? clearFilters() : null"
          aria-pressed="pickedGenres.length === 0"
        >
          সব
        </button>

        <!-- Each Genre -->
        <button
          v-for="g in allGenres"
          :key="g.slug"
          type="button"
          class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 focus-bangla font-sans"
          :class="pickedGenres.includes(g.slug) ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          @click="toggleGenre(g.slug)"
          :aria-pressed="pickedGenres.includes(g.slug)"
        >
          {{ g.name }}
        </button>
      </div>

      <!-- Clear -->
      <div v-if="hasFilters" class="px-3 sm:px-0 mt-3 sm:mt-4">
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
    </section>

    <!-- Books Count (edge-to-edge on mobile) -->
    <section v-if="books?.total" class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mb-6 sm:mb-8">
      <div class="px-3 sm:px-0">
        <div class="badge-bangla badge-bangla-primary text-base sm:text-lg">
          মোট {{ books.total }}টি বই
        </div>
      </div>
    </section>

    <!-- Books Grid (edge-to-edge on mobile) -->
    <section class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
      <div v-if="books?.data?.length" class="px-3 sm:px-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-6">
        <article
          v-for="b in books.data"
          :key="b.id"
          class="group flex flex-col overflow-hidden rounded-md bg-white border border-gray-100 shadow-sm"
        >
          <!-- Cover (3:4 ratio with subtle depth) -->
          <div class="book-cover-container overflow-hidden">
            <img
              v-if="b.cover_url"
              :src="b.cover_url"
              class="w-full h-full object-cover group-hover:scale-105 transition-smooth"
              :alt="b.title"
              loading="lazy"
              decoding="async"
            />
            <div v-else class="w-full h-full bg-gradient-to-br from-amber-100 to-orange-100 grid place-items-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>

            <!-- Spine & Year -->
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-r from-black/15 to-transparent"></div>
            <div v-if="b.release_year" class="absolute top-2 right-2">
              <span class="bg-black/70 text-white text-[11px] sm:text-xs px-2 py-0.5 rounded-full font-sans">
                {{ b.release_year }}
              </span>
            </div>
          </div>

          <!-- Body -->
          <div class="p-3.5 sm:p-6 flex flex-col flex-1">
            <h2 class="heading-bangla text-base sm:text-lg mb-1.5 sm:mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
              <Link :href="`/books/${b.slug}`" class="hover:no-underline focus-bangla rounded">
                {{ b.title }}
              </Link>
            </h2>

            <!-- Year (duplicate removed when badge shown; keep if you still want inline meta) -->
            <div v-if="!b.release_year && b.year" class="flex items-center text-xs sm:text-sm text-muted mb-2 sm:mb-3 font-sans">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ b.year }}</span>
            </div>

            <!-- Genres -->
            <div v-if="b.genres?.length" class="flex flex-wrap gap-1.5 sm:gap-2 mt-auto">
              <button
                v-for="g in b.genres"
                :key="g.slug"
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
              :href="`/books/${b.slug}`"
              class="inline-flex items-center mt-3 sm:mt-4 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors duration-200 group/readmore font-sans focus-bangla rounded"
            >
              বিস্তারিত দেখুন
              <svg class="w-4 h-4 ml-1 transform group-hover/readmore:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </div>
        </article>
      </div>

      <!-- Empty -->
      <div v-else class="px-3 sm:px-0 text-center py-16">
        <div class="max-w-md mx-auto">
          <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253" />
          </svg>
          <h3 class="heading-bangla heading-bangla-3 text-gray-900 mb-2">কোনো বই পাওয়া যায়নি</h3>
          <p class="text-muted mb-6 font-sans">
            {{ hasFilters ? 'আপনার ফিল্টারের সাথে মিলছে না। ফিল্টার সরান বা অন্য কিছু খুঁজুন।' : 'এখনও কোনো বই প্রকাশিত হয়নি।' }}
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

    <!-- Pagination -->
    <nav
      v-if="books?.links?.length"
      class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mt-10 sm:mt-12"
      role="navigation"
      aria-label="পৃষ্ঠা নেভিগেশন"
    >
      <div class="px-3 sm:px-0 flex justify-center">
        <div class="flex items-center space-x-1 bg-white rounded-xl shadow-sm border border-gray-200 p-2">
          <button
            v-for="(link, i) in books.links"
            :key="i"
            type="button"
            class="min-w-[2.5rem] h-10 flex items-center justify-center rounded-lg text-sm font-medium transition-all duration-200 focus-bangla font-sans"
            :class="[
              link.active
                ? 'bg-blue-600 text-white shadow-sm'
                : !link.url
                  ? 'text-gray-300 cursor-not-allowed'
                  : 'text-gray-700 hover:bg-gray-100',
              link.label.includes('Previous') || link.label.includes('Next') ? 'px-3' : 'px-2'
            ]"
            :disabled="!link.url || link.active || submitting"
            :aria-current="link.active ? 'page' : undefined"
            v-html="link.label"
            @click.prevent="go(link)"
          />
        </div>
      </div>
    </nav>

    <!-- CTA -->
    <section v-if="books?.data?.length" class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 mt-10 sm:mt-12 mb-12">
      <div class="px-3 sm:px-0 card-bangla bg-gradient-to-r from-amber-50 to-orange-50 border-amber-200">
        <div class="p-6 text-center">
          <h3 class="heading-bangla heading-bangla-2 mb-2">আরও বই এক্সপ্লোর করুন</h3>
          <p class="text-muted mb-6 font-sans">কাইজার চৌধুরীর সাহিত্যকর্ম সম্পর্কে আরও জানুন</p>
          <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <Link href="/about" class="btn-bangla btn-bangla-primary">লেখক সম্পর্কে</Link>
            <Link href="/blog"  class="btn-bangla btn-bangla-secondary">ব্লগ পড়ুন</Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Book title clamp */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
.transition-smooth { transition: all 0.3s ease; }

/* Book cover 3:4 with subtle depth */
.book-cover-container {
  position: relative;
  aspect-ratio: 3 / 4;
  background: linear-gradient(145deg, #f0f0f0, #e0e0e0);
  box-shadow:
    2px 2px 8px rgba(0,0,0,0.08),
    inset 1px 1px 0 rgba(255,255,255,0.5);
}
.book-cover-container::after {
  content: '';
  position: absolute;
  bottom: -4px; left: 4px; right: 4px;
  height: 8px;
  background: linear-gradient(to bottom, rgba(0,0,0,0.1), transparent);
  border-radius: 50%;
  filter: blur(4px);
  z-index: -1;
}
.group:hover .book-cover-container {
  transform: translateY(-2px);
  box-shadow:
    4px 4px 16px rgba(0,0,0,0.14),
    inset 1px 1px 0 rgba(255,255,255,0.5);
}
.group:hover .book-cover-container::after {
  bottom: -6px; height: 12px; filter: blur(6px);
}

/* Fallback for older browsers without aspect-ratio */
@supports not (aspect-ratio: 3 / 4) {
  .book-cover-container::before { content: ''; display: block; padding-bottom: 133.33%; }
  .book-cover-container > * { position: absolute; inset: 0; width: 100%; height: 100%; }
}

/* Images */
img { max-width: 100%; height: auto; object-fit: cover; }
</style>
