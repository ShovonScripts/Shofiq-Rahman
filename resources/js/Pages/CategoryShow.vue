<!-- resources/js/Pages/CategoryShow.vue -->
<script>
import AppLayout from '../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

export default {
  name: 'CategoryShow',
  layout: AppLayout,
  components: { Head, Link },
  props: {
    category: { type: Object, required: true },
    posts:    { type: Object, required: true },
  },
  computed: {
    metaTitle() {
      return `ক্যাটেগরি: ${this.category?.name ?? ''}`
    },
    metaDesc() {
      return `ক্যাটেগরি "${this.category?.name}"-এর প্রকাশিত লেখাগুলোর তালিকা`
    },
    canonical() {
      try { return `${window.location.origin}/category/${this.category?.slug ?? ''}` }
      catch { return `/category/${this.category?.slug ?? ''}` }
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
      <link rel="canonical" :href="canonical" />
      <link v-if="posts?.prev_page_url" rel="prev" :href="posts.prev_page_url" />
      <link v-if="posts?.next_page_url" rel="next" :href="posts.next_page_url" />
    </Head>

    <!-- Outer container: edge-to-edge on mobile, boxed from sm+ -->
    <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <header class="px-3 sm:px-0 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <h1 class="heading-bangla heading-bangla-1 mb-2">
              ক্যাটেগরি: {{ category.name }}
            </h1>
            <p class="text-lg text-muted font-sans">
              {{ category.description || `${category.name} ক্যাটেগরির সকল লেখা` }}
            </p>
          </div>

          <div v-if="posts?.total" class="badge-bangla badge-bangla-primary">
            মোট {{ posts.total }} টি লেখা
          </div>
        </div>
      </header>

      <!-- Empty State -->
      <section v-if="!posts?.data?.length" class="px-3 sm:px-0">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 sm:px-8 py-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            <h3 class="heading-bangla heading-bangla-3 text-gray-900 mb-2">কোনো লেখা নেই</h3>
            <p class="text-muted mb-6 font-sans">এই ক্যাটেগরিতে এখনো কোনো লেখা প্রকাশিত হয়নি</p>
            <Link href="/blog" class="btn-bangla btn-bangla-primary">সব লেখা দেখুন</Link>
          </div>
        </div>
      </section>

      <!-- Posts list (blog-standard card spacing) -->
      <section v-else class="space-y-6 px-3 sm:px-0">
        <article
          v-for="p in posts.data"
          :key="p.id"
          class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden card-bangla-hover group"
          itemscope
          itemtype="https://schema.org/Article"
        >
          <div class="px-4 sm:px-8 py-6">
            <!-- Title + Reading time -->
            <div class="flex items-start justify-between gap-4 mb-4">
              <h2
                class="heading-bangla text-xl sm:text-2xl font-semibold text-ink flex-1 group-hover:text-blue-600 transition-colors"
                itemprop="headline"
              >
                <Link :href="`/lekha/${p.slug}`" class="hover:no-underline focus-bangla rounded" itemprop="url">
                  {{ p.title }}
                </Link>
              </h2>

              <div class="flex items-center text-sm text-muted bg-gray-100 px-3 py-1 rounded-full whitespace-nowrap font-sans">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span aria-label="পড়ার সময়">{{ p.reading_time }} মিনিট</span>
              </div>
            </div>

            <!-- Excerpt -->
            <p class="text-muted leading-relaxed-bangla mb-4 line-clamp-3 font-sans" itemprop="description">
              {{ p.excerpt || 'বিস্তারিত পড়তে ক্লিক করুন…' }}
            </p>

            <!-- Footer meta -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-100">
              <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted font-sans">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <time :datetime="p.published_at" itemprop="datePublished">{{ p.published_at }}</time>
                </div>

                <div v-if="p.categories?.length" class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="c in p.categories"
                      :key="`c-${p.id}-${c.slug}`"
                      class="badge-bangla badge-bangla-muted"
                    >
                      {{ c.name }}
                    </span>
                  </div>
                </div>
              </div>

              <Link
                :href="`/lekha/${p.slug}`"
                class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors duration-200 group/readmore focus-bangla rounded font-sans"
              >
                পড়ুন
                <svg class="w-4 h-4 ml-1 transform group-hover/readmore:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </Link>
            </div>
          </div>
        </article>
      </section>

      <!-- Pagination (blog-style spacing) -->
      <nav
        v-if="posts?.prev_page_url || posts?.next_page_url"
        class="px-3 sm:px-0 mt-12 flex justify-center"
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

          <div v-if="posts?.current_page" class="text-sm text-muted px-3 py-2 font-sans">
            পেজ {{ posts.current_page }} / {{ posts.last_page }}
          </div>

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

      <!-- CTA -->
      <section v-if="posts?.data?.length" class="px-3 sm:px-0 mt-12">
        <div class="rounded-md sm:rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 shadow-sm">
          <div class="px-4 sm:px-8 py-6 text-center">
            <h3 class="heading-bangla heading-bangla-3 mb-2">আরও ক্যাটেগরি এক্সপ্লোর করুন</h3>
            <p class="text-muted mb-6 font-sans">বিভিন্ন বিষয়ের উপর আরও লেখা পড়ুন</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
              <Link href="/blog" class="btn-bangla btn-bangla-primary">সব লেখা দেখুন</Link>
              <Link href="/categories" class="btn-bangla btn-bangla-secondary">সব ক্যাটেগরি</Link>
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
