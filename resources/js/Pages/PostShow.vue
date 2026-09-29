<!-- resources/js/Pages/PostShow.vue -->
<script>
import AppLayout from '../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it'
import DOMPurify from 'dompurify'
import Comments from '../Components/Comments.vue'
import LikeButton from '../Components/LikeButton.vue'

// Markdown parser
const md = new MarkdownIt({
  html: false,
  linkify: true,
  breaks: true,
})

export default {
  name: 'PostShow',
  layout: AppLayout,
  components: { Head, Link, Comments, LikeButton },
  props: {
    post: {
      type: Object,
      required: true,
      default: () => ({}),
    },
  },

  data() {
    return { progress: 0, _onScroll: null, localComments: this.post?.comments || [] }
  },

  computed: {
    /** Markdown → safe HTML */
    htmlBody() {
      return DOMPurify.sanitize(md.render(this.post?.body || ''))
    },

    /** Meta info for SEO */
    metaTitle() {
      return (
        this.post?.meta_json?.title ||
        this.post?.meta?.title ||
        this.post?.title ||
        'Post'
      )
    },
    metaDescription() {
      return (
        this.post?.meta_json?.description ||
        this.post?.meta?.description ||
        this.post?.excerpt ||
        ''
      )
    },
    canonicalUrl() {
      const base = this.$page?.props?.app?.url || window.location.origin
      return `${base}/lekha/${this.post?.slug || ''}`
    },
    coverUrl() {
      return this.post?.cover_url || this.post?.coverUrl || null
    },
  },

  mounted() {
    const onScroll = () => {
      const doc = document.documentElement
      const body = document.body
      const scrollTop = doc.scrollTop || body.scrollTop
      const scrollHeight = (doc.scrollHeight || body.scrollHeight) - doc.clientHeight
      this.progress = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 0
    }
    this._onScroll = onScroll
    onScroll()
    window.addEventListener('scroll', this._onScroll, { passive: true })
  },

  beforeUnmount() {
    if (this._onScroll) {
      window.removeEventListener('scroll', this._onScroll)
      this._onScroll = null
    }
  },
}
</script>

<template>
  <div>
    <!-- SEO Head -->
    <Head>
      <title>{{ metaTitle }}</title>
      <meta name="description" :content="metaDescription" />
      <link rel="canonical" :href="canonicalUrl" />
      <meta property="og:type" content="article" />
      <meta property="og:title" :content="metaTitle" />
      <meta property="og:description" :content="metaDescription" />
      <meta property="og:url" :content="canonicalUrl" />
      <meta v-if="coverUrl" property="og:image" :content="coverUrl" />
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" :content="metaTitle" />
      <meta name="twitter:description" :content="metaDescription" />
      <meta v-if="coverUrl" name="twitter:image" :content="coverUrl" />
    </Head>

    <!-- Reading Progress -->
    <div class="fixed top-0 left-0 w-full z-[60]">
      <div class="bg-blue-600" :style="{ height: '3px', width: progress + '%', transition: 'width .1s linear' }" />
    </div>

    <!-- Back Button -->
    <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 pt-8">
      <Link
        href="/blog"
        class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 mb-4 sm:mb-6 font-sans focus-bangla rounded"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ব্লগে ফিরে যান
      </Link>
    </div>

    <!-- Full-bleed Cover (edge-to-edge on mobile; boxed from sm+) -->
    <div v-if="coverUrl" class="w-full">
      <div class="px-0 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
          <div class="relative aspect-w-16 aspect-h-9 overflow-hidden rounded-none sm:rounded-2xl shadow sm:shadow-md bg-gray-100">
            <img
              :src="coverUrl"
              :alt="post.title"
              class="absolute inset-0 w-full h-full object-cover"
              loading="eager"
              fetchpriority="high"
              decoding="async"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <article class="max-w-4xl mx-auto px-0 sm:px-6 lg:px-8 py-6 sm:py-8">
      <div class="rounded-none sm:rounded-xl bg-white border-0 sm:border sm:border-gray-200 shadow-none sm:shadow-sm overflow-hidden">
        <!-- Header -->
        <header class="px-3 sm:px-8 pt-6 sm:pt-8 pb-4">
          <h1 class="heading-bangla text-2xl sm:text-3xl md:text-4xl mb-3 sm:mb-4">
            {{ post.title }}
          </h1>

          <!-- Meta info -->
          <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs sm:text-sm text-muted mb-4 sm:mb-6 font-sans">
            <!-- Author -->
            <div v-if="post.author" class="flex items-center">
              <svg class="w-4 h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>{{ post.author }}</span>
            </div>

            <!-- Date -->
            <div v-if="post.published_at" class="flex items-center">
              <svg class="w-4 h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ post.published_at }}</span>
            </div>

            <!-- Reading Time -->
            <div class="flex items-center">
              <svg class="w-4 h-4 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ post.reading_time }} মিনিট পড়া</span>
            </div>
          </div>

          <!-- Categories & Genres -->
          <div class="flex flex-wrap gap-1.5 sm:gap-2">
            <!-- Categories -->
            <Link
              v-for="c in post.categories || []"
              :key="`c-${c.slug}`"
              :href="`/category/${c.slug}`"
              class="badge-bangla badge-bangla-muted hover:bg-gray-200 transition-colors focus-bangla"
            >
              #{{ c.name }}
            </Link>

            <!-- Genres -->
            <Link
              v-for="g in post.genres || []"
              :key="`g-${g.slug}`"
              :href="`/genre/${g.slug}`"
              class="badge-bangla badge-bangla-primary hover:bg-blue-700 transition-colors focus-bangla"
            >
              {{ g.name }}
            </Link>
          </div>
        </header>

        <!-- Article Content -->
        <!-- px-0 on mobile so text touches left/right edge (green line),
             with comfy gutters from sm+ -->
        <div class="px-0 sm:px-8 pb-6 sm:pb-8">
          <!-- Markdown Content (neutralized horizontal margins) -->
          <div
            class="prose max-w-none sm:prose-lg m-0 p-0
                   prose-p:m-0 prose-headings:m-0 prose-ul:my-0 prose-ol:my-0 prose-blockquote:m-0
                   prose-img:mx-0 prose-table:mx-0"
            v-html="htmlBody"
          ></div>

          <!-- Like Button -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <LikeButton
              :type="'post'"
              :id="post.id"
              :initialCount="post.likes_count || 0"
              :initiallyLiked="post.liked_by_me || false"
            />
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <section class="mt-8 sm:mt-10">
        <Comments
          :type="'post'"
          :parentId="post.id"
          :items="localComments"
          :canPost="!!$page.props.auth?.user?.email_verified_at"
          @deleted="localComments = localComments.filter(c => c.id !== $event)"
        />
      </section>

      <!-- CTA Section -->
      <section class="mt-10 sm:mt-12 mb-12">
        <div class="rounded-md sm:rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 sm:border-blue-200 shadow-sm">
          <div class="px-4 sm:px-8 py-6 sm:py-8 text-center">
            <h3 class="heading-bangla text-xl sm:text-2xl mb-3 sm:mb-4">আরও লেখা পড়ুন</h3>
            <p class="text-muted mb-5 sm:mb-6 font-sans text-base sm:text-lg">আরও লেখা এক্সপ্লোর করুন</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
              <Link href="/blog" class="btn-bangla btn-bangla-primary">সকল ব্লগ পোস্ট</Link>
              <Link href="/books" class="btn-bangla btn-bangla-secondary">বই দেখুন</Link>
              <Link href="/about" class="btn-bangla btn-bangla-ghost">লেখক সম্পর্কে</Link>
            </div>
          </div>
        </div>
      </section>
    </article>
  </div>
</template>

<style scoped>
/* 16:9 aspect ratio without plugin */
.aspect-w-16 { position: relative; }
.aspect-h-9::before { content: ''; display: block; padding-bottom: 56.25%; }
.aspect-h-9 > * { position: absolute; inset: 0; width: 100%; height: 100%; }

/* Improve anchor scroll positions for headings */
.prose :where(h1,h2,h3,h4){ scroll-margin-top: 80px; }

/* Kill horizontal margins/padding from Typography children to meet the green edge */
.prose :where(p, h1, h2, h3, h4, h5, h6, ul, ol, blockquote, pre, table, img){
  margin-left: 0 !important;
  margin-right: 0 !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

/* Progress bar helpers (scoped) */
.fixed { position: fixed; }
.top-0 { top: 0; }
.left-0 { left: 0; }
</style>
