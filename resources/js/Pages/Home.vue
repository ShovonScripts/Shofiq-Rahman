<!-- resources/js/Pages/Home.vue -->
<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it'
import DOMPurify from 'dompurify'
import PostCard from '@/Components/PostCard.vue'
import BookCard from '@/Components/BookCard.vue'

const md = new MarkdownIt({ html:false, linkify:true, breaks:true })

export default {
  name: 'Home',
  layout: AppLayout,
  components: { Head, Link, PostCard, BookCard },
  props: {
    banners: Array,
    latest_posts: Array,
    latest_books: Array,
    author: Object,
    genres: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
  },
  data() {
    return {
      currentSlide: 0,
      autoPlay: null,
      currentTickerIndex: 0,
      tickerInterval: null
    }
  },
  computed: {
    bioHtml() {
      return DOMPurify.sanitize(md.render(this.author?.bio || ''))
    },
    authorDetails() {
      return this.author || {}
    },
    tickerItems() {
      const genres = (this.genres || []).map(g => ({
        type: 'genre',
        ...g,
        url: g.url || `/blog?genre=${g.slug}`
      }))
      const categories = (this.categories || []).map(c => ({
        type: 'category',
        ...c,
        url: c.url || `/category/${c.slug}`
      }))
      return [...genres, ...categories]
    },
    currentTickerItem() {
      if (this.tickerItems.length === 0) return null
      return this.tickerItems[this.currentTickerIndex]
    }
  },
  methods: {
    nextSlide() {
      if (!this.banners || this.banners.length === 0) return
      this.currentSlide = (this.currentSlide + 1) % this.banners.length
    },
    prevSlide() {
      if (!this.banners || this.banners.length === 0) return
      this.currentSlide = (this.currentSlide - 1 + this.banners.length) % this.banners.length
    },
    goToSlide(index) {
      this.currentSlide = index
    },
    startAutoPlay() {
      if (this.banners && this.banners.length > 1) {
        this.autoPlay = setInterval(() => {
          this.nextSlide()
        }, 5000)
      }
    },
    stopAutoPlay() {
      if (this.autoPlay) {
        clearInterval(this.autoPlay)
        this.autoPlay = null
      }
    },
    nextTickerItem() {
      if (this.tickerItems.length === 0) return
      this.currentTickerIndex = (this.currentTickerIndex + 1) % this.tickerItems.length
    },
    startTicker() {
      if (this.tickerItems.length > 0) {
        this.tickerInterval = setInterval(() => {
          this.nextTickerItem()
        }, 3000)
      }
    },
    stopTicker() {
      if (this.tickerInterval) {
        clearInterval(this.tickerInterval)
        this.tickerInterval = null
      }
    }
  },
  mounted() {
    this.startAutoPlay()
    this.startTicker()
  },
  beforeUnmount() {
    this.stopAutoPlay()
    this.stopTicker()
  }
}
</script>

<template>
  <div>
    <Head>
      <title>{{ $page.props.settings?.site_name || 'Writer' }}</title>
      <meta name="description" :content="`${$page.props.settings?.site_name || 'Writer'} — বাংলা সাহিত্যের সমৃদ্ধ সংগ্রহ। শিশুসাহিত্য, প্রবন্ধ ও সমকালীন রচনা।`" />
    </Head>

    <!-- =========================
         Hero Carousel (True Full-Bleed)
         ========================= -->
    <section
      v-if="banners && banners.length > 0"
      class="full-bleed bg-gray-50 border-b border-gray-200"
    >
      <div class="relative overflow-hidden shadow md:shadow-lg">
        <div class="relative banner-aspect overflow-hidden rounded-none md:rounded-2xl">
          <div
            v-for="(banner, index) in banners"
            :key="banner.id"
            class="absolute inset-0 w-full h-full transition-opacity duration-500 ease-in-out"
            :class="{
              'opacity-100 z-10': currentSlide === index,
              'opacity-0 z-0': currentSlide !== index
            }"
          >
            <a
              :href="banner.link || '#'"
              class="block w-full h-full"
              @mouseenter="stopAutoPlay"
              @mouseleave="startAutoPlay"
            >
              <img
                :src="banner.image_url"
                :alt="banner.title || 'Banner'"
                class="w-full h-full object-cover object-center"
                loading="eager"
                fetchpriority="high"
                decoding="async"
              />
              <div
                v-if="banner.title"
                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 sm:p-6"
              >
                <h2 class="text-lg sm:text-2xl font-bold text-white heading-bangla">
                  {{ banner.title }}
                </h2>
              </div>
            </a>
          </div>
        </div>

        <!-- Arrows -->
        <button
          v-if="banners.length > 1"
          @click="prevSlide"
          @mouseenter="stopAutoPlay"
          @mouseleave="startAutoPlay"
          class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-black/35 hover:bg-black/50 text-white rounded-full p-1.5 sm:p-2 transition-all focus-bangla"
          aria-label="Previous slide"
        >
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <button
          v-if="banners.length > 1"
          @click="nextSlide"
          @mouseenter="stopAutoPlay"
          @mouseleave="startAutoPlay"
          class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-black/35 hover:bg-black/50 text-white rounded-full p-1.5 sm:p-2 transition-all focus-bangla"
          aria-label="Next slide"
        >
          <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <!-- Dots -->
        <div
          v-if="banners.length > 1"
          class="absolute bottom-3 left-1/2 -translate-x-1/2 flex space-x-2"
        >
          <button
            v-for="(banner, index) in banners"
            :key="banner.id"
            @click="goToSlide(index)"
            @mouseenter="stopAutoPlay"
            @mouseleave="startAutoPlay"
            class="w-2.5 h-2.5 rounded-full transition-all focus-bangla"
            :class="currentSlide === index ? 'bg-white' : 'bg-white/50 hover:bg-white/70'"
            :aria-label="`Go to slide ${index + 1}`"
          />
        </div>
      </div>
    </section>

    <!-- =========================
         Ticker
         ========================= -->
    <section
      v-if="tickerItems.length > 0"
      class="bg-gradient-to-r from-blue-600 to-purple-700 text-white"
      @mouseenter="stopTicker"
      @mouseleave="startTicker"
    >
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center py-2.5 sm:py-3">
          <div class="flex items-center gap-2 shrink-0 mr-3 sm:mr-6 bg-red-600 px-2.5 py-1 rounded">
            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
            </svg>
            <span class="text-xs sm:text-sm font-bold uppercase tracking-wide font-sans">বিষয়সমূহ</span>
          </div>

          <div class="flex-1 overflow-hidden">
            <transition name="ticker-fade" mode="out-in">
              <div :key="currentTickerIndex" class="ticker-content">
                <Link
                  v-if="currentTickerItem"
                  :href="currentTickerItem.url"
                  class="inline-flex items-center gap-2 sm:gap-3 hover:text-yellow-200 transition-colors duration-300 focus-bangla rounded"
                >
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold border border-white/30 font-sans"
                    :class="currentTickerItem.type === 'genre'
                      ? 'bg-purple-500/20 text-purple-200'
                      : 'bg-emerald-500/20 text-emerald-200'"
                  >
                    {{ currentTickerItem.type === 'genre' ? 'ধরন' : 'বিভাগ' }}
                  </span>
                  <span class="text-base sm:text-lg font-medium tracking-wide font-sans">{{ currentTickerItem.name }}</span>
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-300 ml-1 sm:ml-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                  </svg>
                </Link>
              </div>
            </transition>
          </div>

          <div class="hidden sm:flex items-center gap-2 ml-4">
            <button
              @click="nextTickerItem"
              class="p-1 rounded-full hover:bg-white/20 transition-colors duration-200 focus-bangla"
              aria-label="Next item"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
            <div class="hidden md:flex items-center gap-1">
              <div
                v-for="(item, index) in tickerItems"
                :key="item.id"
                class="w-2 h-2 rounded-full transition-all duration-300"
                :class="currentTickerIndex === index ? 'bg-yellow-400' : 'bg-white/40'"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Latest Blog Posts -->
    <section class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-5 sm:mb-8 px-3 sm:px-0">
        <h2 class="heading-bangla text-xl sm:text-2xl">সর্বশেষ রচনা</h2>
        <Link
          href="/blog"
          class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 group font-sans focus-bangla rounded px-3 sm:px-0"
        >
          সব রচনা দেখুন
          <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Link>
      </div>

      <div v-if="latest_posts?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5 px-0">
        <PostCard
          v-for="post in latest_posts"
          :key="post.id"
          :post="post"
        />
      </div>
      <div v-else class="text-center py-10 px-3">
        <p class="text-gray-500 text-sm">এখনও কোনো পোস্ট প্রকাশিত হয়নি।</p>
      </div>
    </section>

    <!-- Latest Books -->
    <section class="bg-gray-50 border-t border-b border-gray-200 py-8 sm:py-12">
      <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-5 sm:mb-8 px-3 sm:px-0">
          <h2 class="heading-bangla text-xl sm:text-2xl">প্রকাশিত বইসমূহ</h2>
          <Link
            href="/books"
            class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 group font-sans focus-bangla rounded"
          >
            সব বই দেখুন
            <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </Link>
        </div>

        <div v-if="latest_books?.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-5 px-0">
          <BookCard
            v-for="book in latest_books"
            :key="book.id"
            :book="book"
          />
        </div>
        <div v-else class="text-center py-10 px-3">
          <p class="text-gray-500 text-sm">এখনও কোনো বই প্রকাশিত হয়নি।</p>
        </div>
      </div>
    </section>

    <!-- Author Section -->
    <section
      v-if="authorDetails && authorDetails.name"
      class="w-full py-8 sm:py-12 bg-white"
    >
      <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 sm:gap-6 items-start px-0">
          <!-- Author Image -->
          <div class="lg:col-span-1">
            <div class="overflow-hidden bg-white rounded-none sm:rounded-lg border-0 sm:border sm:border-gray-100 shadow-none sm:shadow-sm">
              <img
                v-if="authorDetails?.photo"
                :src="authorDetails.photo"
                :alt="authorDetails.name"
                class="w-full aspect-square object-cover"
                loading="lazy"
              >
              <div
                v-else
                class="w-full aspect-square bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center"
              >
                <div class="text-center p-6">
                  <div
                    class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xl sm:text-2xl font-bold mx-auto mb-3"
                  >
                    {{ authorDetails.name.charAt(0) }}
                  </div>
                  <h3 class="text-base sm:text-lg font-bold text-ink heading-bangla">
                    {{ authorDetails.name }}
                  </h3>
                </div>
              </div>
            </div>
          </div>

          <!-- Author Bio -->
          <div class="lg:col-span-3">
            <div class="bg-white rounded-none sm:rounded-lg border-0 sm:border sm:border-gray-100 shadow-none sm:shadow-sm">
              <header class="px-3 py-3 sm:px-6 sm:py-4 border-b border-gray-100">
                <h2 class="heading-bangla text-xl sm:text-2xl">
                  {{ authorDetails.name }}
                </h2>
              </header>

              <article
                v-if="bioHtml"
                class="prose max-w-none text-ink leading-relaxed-bangla px-3 sm:px-6 py-3 sm:py-4"
                v-html="bioHtml"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12">
      <div class="rounded-md sm:rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 sm:border-blue-200 shadow-sm">
        <div class="px-4 sm:px-8 py-6 sm:py-8 text-center">
          <h3 class="heading-bangla text-xl sm:text-2xl mb-3 sm:mb-4">আরও সাহিত্যকর্ম এক্সপ্লোর করুন</h3>
          <p class="text-muted mb-5 sm:mb-6 font-sans text-base sm:text-lg">বই, ব্লগ এবং লেখা পড়ুন</p>
          <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center px-3 sm:px-0">
            <Link href="/books" class="btn-bangla btn-bangla-primary">বই দেখুন</Link>
            <Link href="/blog" class="btn-bangla btn-bangla-secondary">ব্লগ পড়ুন</Link>
            <Link href="/about" class="btn-bangla btn-bangla-ghost">লেখক সম্পর্কে</Link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* ---------- Full-bleed utility for true edge-to-edge ---------- */
.full-bleed {
  position: relative;
  width: 100vw;
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
}

/* Ticker Animation */
.ticker-fade-enter-active,
.ticker-fade-leave-active { transition: all 0.5s ease; }
.ticker-fade-enter-from { opacity: 0; transform: translateX(30px); }
.ticker-fade-leave-to { opacity: 0; transform: translateX(-30px); }
.ticker-content { white-space: nowrap; }

/* Banner responsive aspect ratio:
   Mobile: 16:9
   Desktop (md+): 8:3 */
.banner-aspect {
  position: relative;
  aspect-ratio: 16 / 9;
}

@media (min-width: 768px) {
  .banner-aspect {
    aspect-ratio: 8 / 3;
  }
}

@supports not (aspect-ratio: 16 / 9) {
  .banner-aspect::before { content: ''; display: block; padding-bottom: 56.25%; }
  .banner-aspect > * {
    position: absolute; height: 100%; width: 100%;
    top: 0; right: 0; bottom: 0; left: 0;
  }

  @media (min-width: 768px) {
    .banner-aspect::before { padding-bottom: 37.5%; }
  }
}

/* Images */
.banner-aspect img { width: 100%; height: 100%; object-fit: cover; }
</style>
