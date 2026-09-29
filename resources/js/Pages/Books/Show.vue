<!-- resources/js/Pages/Books/Show.vue -->
<script>
import AppLayout from '../../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it'
import DOMPurify from 'dompurify'
import Comments from '../../Components/Comments.vue'
import LikeButton from '../../Components/LikeButton.vue'

const md = new MarkdownIt({ html:false, linkify:true, breaks:true })

export default {
  name: 'BooksShow',
  layout: AppLayout,
  components: { Head, Link, Comments, LikeButton },
  props: {
    book: {
      type: Object,
      required: true,
      default: () => ({}),
    },
  },
  data() {
      return { localComments: this.book?.comments || [] }
    },

  computed: {
    htmlBody() {
      return DOMPurify.sanitize(md.render(this.book?.description || ''))
    },
    metaTitle() {
      return this.book?.meta?.title || this.book?.meta_json?.title || this.book?.title || 'বই'
    },
    metaDesc() {
      return this.book?.meta?.description || this.book?.meta_json?.description || this.book?.subtitle || 'বইয়ের বিবরণ'
    },
    canonical() {
      const base = this.$page?.props?.app?.url || window.location.origin
      return `${base.replace(/\/$/, '')}/books/${encodeURIComponent(this.book?.slug || '')}`
    },
    coverUrl() {
      return this.book?.cover_url || null
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
      <meta property="og:type" content="book" />
      <meta property="og:title" :content="metaTitle" />
      <meta property="og:description" :content="metaDesc" />
      <meta property="og:url" :content="canonical" />
      <meta v-if="coverUrl" property="og:image" :content="coverUrl" />
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" :content="metaTitle" />
      <meta name="twitter:description" :content="metaDesc" />
      <meta v-if="coverUrl" name="twitter:image" :content="coverUrl" />
    </Head>

    <!-- Outer container: edge-to-edge on mobile -->
    <div class="max-w-6xl mx-auto px-0 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Breadcrumb -->
      <nav class="px-3 sm:px-0 mb-4 sm:mb-6">
        <Link
          href="/books"
          class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 group font-sans focus-bangla rounded"
        >
          <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          সব বই
        </Link>
      </nav>

      <div class="px-3 sm:px-0 grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-8">
        <!-- Sidebar -->
        <aside class="lg:col-span-1">
          <!-- Cover card -->
          <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="book-cover-container">
              <img
                v-if="coverUrl"
                :src="coverUrl"
                class="w-full h-full object-cover"
                :alt="book.title"
                loading="eager"
                fetchpriority="high"
                decoding="async"
              />
              <div v-else class="w-full h-full bg-gradient-to-br from-amber-100 to-orange-100 grid place-items-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-r from-black/15 to-transparent"></div>
            </div>

            <div class="p-4 sm:p-6 space-y-4">
              <div class="flex justify-center">
                <LikeButton
                  :type="'book'"
                  :id="book.id"
                  :initialCount="book.likes_count || 0"
                  :initiallyLiked="book.liked_by_me || false"
                />
              </div>

              <div v-if="book.links?.length" class="space-y-2">
                <h3 class="font-semibold text-ink text-center heading-bangla mb-2">কিনুন / পড়ুন</h3>
                <a
                  v-for="ln in book.links"
                  :key="ln.id"
                  class="w-full flex items-center justify-center px-4 py-3 rounded-xl font-medium transition-all duration-200 focus-bangla font-sans"
                  :class="ln.is_primary
                    ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm'
                    : 'bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-300'"
                  :href="`/go/book-link/${ln.id}`"
                  target="_blank"
                  rel="noopener"
                >
                  <span>{{ ln.label }}</span>
                  <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Details -->
          <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden mt-4 sm:mt-6">
            <div class="p-4 sm:p-6">
              <h3 class="heading-bangla heading-bangla-3 mb-4">বইয়ের তথ্য</h3>
              <div class="space-y-4">
                <div v-if="book.published_at" class="flex items-start gap-3">
                  <div class="w-5 h-5 text-blue-600 mt-0.5">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-ink font-sans">প্রকাশের তারিখ</p>
                    <p class="text-sm text-muted font-sans">{{ book.published_at }}</p>
                  </div>
                </div>

                <div v-if="book.genres?.length" class="flex items-start gap-3">
                  <div class="w-5 h-5 text-green-600 mt-0.5">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-ink font-sans">ধরন</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                      <span v-for="g in book.genres" :key="g.slug" class="badge-bangla badge-bangla-success">
                        {{ g.name }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="pt-4 border-top border-gray-200">
                  <p class="text-sm font-medium text-ink mb-3 font-sans">শেয়ার করুন</p>
                  <div class="flex gap-3">
                    <button class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors duration-200 focus-bangla" aria-label="Share on Facebook">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </button>
                    <button class="p-2 bg-blue-100 text-blue-400 rounded-lg hover:bg-blue-200 transition-colors duration-200 focus-bangla" aria-label="Share on Twitter/X">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Main content -->
        <main class="lg:col-span-3">
          <!-- NOTE: px-3 on mobile so text meets the green line; roomier from sm+ -->
          <article class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-3 sm:px-8 py-6 sm:py-8">
              <!-- Header (no extra horizontal padding so it lines up perfectly) -->
              <header class="mb-6 sm:mb-8 pb-5 sm:pb-6 border-b border-gray-200">
                <h1 class="heading-bangla text-2xl sm:text-3xl md:text-4xl mb-3">
                  {{ book.title }}
                </h1>
                <p v-if="book.subtitle" class="text-base sm:text-xl text-muted leading-relaxed-bangla mb-5 font-sans">
                  {{ book.subtitle }}
                </p>
                <div class="flex items-center gap-4 p-3 sm:p-4 bg-blue-50 rounded-xl">
                  <div>
                    <p class="font-semibold text-ink font-sans">{{ $page.props.settings?.site_name || 'Writer' }}</p>
                  </div>
                </div>
              </header>

              <!-- Description (flush to the same px on mobile) -->
              <section class="prose sm:prose-lg max-w-none">
                <h2 class="heading-bangla heading-bangla-2 mb-4 sm:mb-6">বই সম্পর্কে</h2>

                <article
                  v-if="book.description"
                  class="text-ink leading-relaxed-bangla space-y-4"
                  v-html="htmlBody"
                />

                <div v-else class="text-center py-8 text-muted">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                  </svg>
                  <p class="font-sans">বইয়ের বিবরণ শীঘ্রই যুক্ত করা হবে</p>
                </div>
              </section>
            </div>
          </article>

          <!-- Comments -->
          <section class="mt-6 sm:mt-8 px-3 sm:px-0">
            <Comments
              :type="'book'"
              :parentId="book.id"
              :items="localComments"
              :canPost="!!$page.props.auth?.user?.email_verified_at"
              @deleted="localComments = localComments.filter(c => c.id !== $event)"
            />
          </section>
        </main>
      </div>

      <!-- CTA -->
      <section class="px-3 sm:px-0 mt-10 sm:mt-12 mb-12">
        <div class="rounded-md sm:rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 shadow-sm">
          <div class="px-4 sm:px-8 py-6 sm:py-8 text-center">
            <h3 class="heading-bangla text-xl sm:text-2xl mb-3 sm:mb-4">আরও বই পড়ুন</h3>
            <p class="text-muted mb-5 sm:mb-6 font-sans text-base sm:text-lg">অন্যান্য বইগুলোও দেখুন</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
              <Link href="/books" class="btn-bangla btn-bangla-primary">সব বই দেখুন</Link>
              <Link href="/about" class="btn-bangla btn-bangla-secondary">লেখক সম্পর্কে</Link>
              <Link href="/blog" class="btn-bangla btn-bangla-ghost">ব্লগ পড়ুন</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
/* Prose */
.prose { color: inherit; }
.prose h1,.prose h2,.prose h3,.prose h4,.prose h5,.prose h6{
  font-family: var(--font-serif);
  font-weight: 700;
  color: var(--ink);
  margin-top: 2rem;
  margin-bottom: 1rem;
}
.prose p{ line-height: 1.8; margin-bottom: 1.25rem; }
.prose a{ color:#3b82f6; text-decoration: underline; }
.prose a:hover{ color:#1d4ed8; }
.prose img{ max-width:100%; height:auto; border-radius:.5rem; margin:1.5rem 0; }
.prose blockquote{ border-left:4px solid #e5e7eb; padding-left:1rem; font-style:italic; color:#6b7280; margin:1.5rem 0; }
.prose ul,.prose ol{ margin-bottom:1.25rem; padding-left:1.625rem; }
.prose li{ margin-bottom:.5rem; }

/* Book cover 3:4 with subtle depth */
.book-cover-container{
  position: relative;
  aspect-ratio: 3/4;
  background: linear-gradient(145deg,#f0f0f0,#e0e0e0);
  box-shadow: 2px 2px 8px rgba(0,0,0,.08), inset 1px 1px 0 rgba(255,255,255,.5);
  overflow: hidden;
}
.book-cover-container::after{
  content:'';
  position:absolute; bottom:-4px; left:4px; right:4px; height:8px;
  background: linear-gradient(to bottom, rgba(0,0,0,.1), transparent);
  border-radius:50%; filter: blur(4px); z-index:-1;
}

/* Fallback */
@supports not (aspect-ratio: 3 / 4) {
  .book-cover-container::before { content:''; display:block; padding-bottom:133.33%; }
  .book-cover-container > * { position:absolute; inset:0; width:100%; height:100%; }
}

/* Generic */
img { max-width:100%; height:auto; object-fit:cover; }
.transition-smooth { transition: all .3s ease; }
</style>
