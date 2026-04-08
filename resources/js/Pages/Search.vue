<!-- resources/js/Pages/Search.vue -->
<script>
import AppLayout from '../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

export default {
  name: 'Search',
  layout: AppLayout,
  components: { Head, Link },
  props: {
    q: { type: String, default: '' },
    posts: { type: Object, required: true },
  },
  computed: {
    metaTitle() {
      return this.q ? `"${this.q}" — সার্চ ফলাফল` : 'সার্চ — কাইজার চৌধুরী সাহিত্য'
    },
    metaDesc() {
      return this.q ? `কীওয়ার্ড "${this.q}" এর সার্চ ফলাফল` : 'লেখা খোঁজার পেজ'
    },
  },
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- SEO -->
    <Head>
      <title>{{ metaTitle }}</title>
      <meta name="description" :content="metaDesc" />
      <link v-if="posts?.prev_page_url" rel="prev" :href="posts.prev_page_url" />
      <link v-if="posts?.next_page_url" rel="next" :href="posts.next_page_url" />
    </Head>

    <!-- Outer container: edge-to-edge on mobile, boxed from sm+ -->
    <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <header class="px-3 sm:px-0 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
          <div>
            <h1 class="heading-bangla heading-bangla-1 mb-1">সার্চ ফলাফল</h1>
            <div v-if="q && posts?.total" class="text-lg text-muted font-sans">
              “<span class="font-semibold text-ink">{{ q }}</span>” — মোট {{ posts.total }} টি ফলাফল
            </div>
            <div v-else-if="q" class="text-lg text-muted font-sans">
              কীওয়ার্ড: “<span class="font-semibold text-ink">{{ q }}</span>”
            </div>
          </div>
        </div>
      </header>

      <!-- Search Form -->
      <section class="px-3 sm:px-0 mb-8">
        <form class="flex flex-col sm:flex-row gap-4" action="/search" method="get" role="search" aria-label="পোস্ট সার্চ">
          <div class="flex-1">
            <label for="q" class="sr-only">খুঁজুন</label>
            <div class="relative">
              <input
                id="q"
                class="input-bangla pl-12 w-full"
                type="search"
                name="q"
                :value="q"
                placeholder="কীওয়ার্ড লিখে খুঁজুন…"
                aria-label="পোস্ট খুঁজুন"
                autocomplete="off"
              />
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
            </div>
          </div>
          <div>
            <button class="btn-bangla btn-bangla-primary px-8 py-3 whitespace-nowrap w-full sm:w-auto" type="submit">
              খুঁজুন
            </button>
          </div>
        </form>
      </section>

      <!-- No-query state -->
      <section v-if="!q" class="px-3 sm:px-0">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 sm:px-8 py-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="heading-bangla heading-bangla-3 text-gray-900 mb-2">কীওয়ার্ড লিখুন</h3>
            <p class="text-muted font-sans">সার্চ করতে উপরের বক্সে আপনার পছন্দের কীওয়ার্ড লিখুন</p>
          </div>
        </div>
      </section>

      <!-- Empty results -->
      <section v-else-if="!posts?.data?.length" class="px-3 sm:px-0">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 sm:px-8 py-12 text-center">
            <svg class="w-16 h-16 text-amber-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="heading-bangla heading-bangla-3 text-gray-900 mb-2">কোনো ফলাফল নেই</h3>
            <p class="text-muted mb-6 font-sans">
              “<span class="font-semibold text-ink">{{ q }}</span>” দিয়ে কোনো পোস্ট পাওয়া যায়নি
            </p>
            <div class="space-y-3">
              <p class="text-sm text-muted font-sans">পরামর্শ:</p>
              <ul class="text-sm text-muted space-y-1 text-left max-w-xs mx-auto font-sans">
                <li class="flex items-center"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-2"></span>বানান পরীক্ষা করুন</li>
                <li class="flex items-center"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-2"></span>অন্য কীওয়ার্ড ব্যবহার করুন</li>
                <li class="flex items-center"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-2"></span>সার্চ শব্দ ছোট করুন</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- Results list -->
      <section v-else class="space-y-6 px-3 sm:px-0">
        <article
          v-for="p in posts.data"
          :key="p.id"
          class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden card-bangla-hover group"
          itemscope
          itemtype="https://schema.org/Article"
        >
          <div class="px-4 sm:px-8 py-6">
            <h2
              class="heading-bangla text-xl sm:text-2xl font-semibold text-ink mb-3 group-hover:text-blue-600 transition-colors"
              itemprop="headline"
            >
              <Link :href="`/lekha/${p.slug}`" class="hover:no-underline focus-bangla rounded" itemprop="url">
                {{ p.title }}
              </Link>
            </h2>

            <p class="text-muted leading-relaxed-bangla mb-4 line-clamp-3 font-sans" itemprop="description">
              {{ p.excerpt || 'বিস্তারিত পড়তে ক্লিক করুন…' }}
            </p>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted font-sans">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span aria-label="পড়ার সময়">{{ p.reading_time }} মিনিট</span>
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <time :datetime="p.published_at" itemprop="datePublished">
                  {{ p.published_at || 'তারিখ নেই' }}
                </time>
              </div>
            </div>

            <Link
              :href="`/lekha/${p.slug}`"
              class="inline-flex items-center mt-4 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors duration-200 group/readmore focus-bangla rounded font-sans"
            >
              সম্পূর্ণ পড়ুন
              <svg class="w-4 h-4 ml-1 transform group-hover/readmore:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Link>
          </div>
        </article>
      </section>

      <!-- Pagination -->
      <nav
        v-if="posts?.prev_page_url || posts?.next_page_url"
        class="px-3 sm:px-0 mt-10 flex justify-center"
        aria-label="পেজ নেভিগেশন"
      >
        <div class="flex items-center space-x-4">
          <Link
            v-if="posts?.prev_page_url"
            :href="posts.prev_page_url"
            class="btn-bangla btn-bangla-secondary flex items-center space-x-2"
            rel="prev"
            preserve-scroll
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>আগের পেজ</span>
          </Link>

          <Link
            v-if="posts?.next_page_url"
            :href="posts.next_page_url"
            class="btn-bangla btn-bangla-secondary flex items-center space-x-2"
            rel="next"
            preserve-scroll
          >
            <span>পরের পেজ</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>
      </nav>

      <!-- Tips CTA -->
      <section v-if="posts?.data?.length" class="px-3 sm:px-0 mt-12">
        <div class="rounded-md sm:rounded-xl bg-blue-50 border border-blue-200 shadow-sm">
          <div class="px-4 sm:px-8 py-6">
            <h3 class="heading-bangla font-bold text-ink mb-4 flex items-center">
              <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              সার্চ টিপস
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-muted font-sans">
              <div class="flex items-start space-x-3">
                <span class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                <div>
                  <p class="font-medium text-ink">স্পেস ব্যবহার করুন</p>
                  <p>একাধিক শব্দ লিখতে স্পেস ব্যবহার করুন</p>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <span class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></span>
                <div>
                  <p class="font-medium text-ink">সঠিক বানান</p>
                  <p>সঠিক বানানে লিখলে ভাল ফলাফল পাবেন</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-3{
  display:-webkit-box;
  -webkit-line-clamp:3;
  -webkit-box-orient:vertical;
  overflow:hidden;
}
</style>
