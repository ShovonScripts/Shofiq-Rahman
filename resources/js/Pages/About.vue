<script setup>
import { computed } from 'vue'
import { Head, usePage, Link } from '@inertiajs/vue3'
import { marked } from 'marked'
import DOMPurify from 'dompurify'

const page = usePage()
const profile = computed(() => page.props?.profile || null)
const md = (t) => (t ? DOMPurify.sanitize(marked.parse(t)) : '')

/* Bengali date formatter (handles ISO/YYYY) */
const formatBnDate = (val) => {
  if (!val) return ''
  const s = String(val).trim()
  if (/^\d{4}$/.test(s)) {
    try { return new Intl.NumberFormat('bn-BD').format(Number(s)) } catch { return s }
  }
  const d = new Date(s)
  if (isNaN(d)) return s.replace(/T00:00:00\.0+Z?$/i, '')
  try {
    return new Intl.DateTimeFormat('bn-BD', { year:'numeric', month:'long', day:'numeric' }).format(d)
  } catch { return s.slice(0,10) }
}
</script>

<template>
  <div class="overflow-x-hidden">
    <Head>
      <title>{{ profile?.name || $page.props.settings?.site_name || 'About' }}</title>
      <meta name="description" :content="`লেখকের জীবনী, অর্জন এবং সাহিত্যকর্ম সম্পর্কে জানুন`" />
    </Head>

    <!-- ===== Cover (fixed 16:9), avatar UNDER it (no overlap) ===== -->
    <section v-if="profile" class="w-full bg-white">
      <!-- 16:9 cover -->
      <div class="relative aspect-w-16 aspect-h-9 bg-gray-200">
        <img
          v-if="profile.cover_url"
          :src="profile.cover_url"
          alt="লেখকের কভার ছবি"
          class="absolute inset-0 w-full h-full object-cover object-center"
          loading="lazy"
          decoding="async"
        />
        <div v-else class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600" />
      </div>

      <!-- Avatar directly under the cover -->
      <div class="w-full py-4 sm:py-6">
        <div class="mx-auto grid place-items-center">
          <img
            v-if="profile.avatar_url"
            :src="profile.avatar_url"
            alt="লেখকের ছবি"
            class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-full object-cover ring-4 ring-white shadow-xl"
            loading="lazy"
            decoding="async"
          />
          <div
            v-else
            class="w-24 h-24 sm:w-28 sm:h-28 md:w-32 md:h-32 rounded-full grid place-items-center bg-gradient-to-r from-amber-400 to-orange-500 ring-4 ring-white shadow-xl"
          >
            <span class="text-white font-bold text-xl sm:text-2xl">কা</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== Content ===== -->
    <section class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 pb-8">
      <div v-if="profile" class="space-y-6 sm:space-y-8">
        <!-- Name + chips -->
        <div class="text-center">
          <h1 class="heading-bangla font-extrabold tracking-tight text-3xl sm:text-4xl leading-tight break-words">
            {{ profile.name }}
          </h1>

          <div class="mt-3 flex flex-wrap justify-center gap-2">
            <span
              v-if="profile.pen_name"
              class="inline-flex items-center bg-blue-50 text-blue-800 px-3 py-1.5 rounded-full text-sm font-sans"
            >
              <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16.862 4.487l1.414 1.414-9.9 9.9-2.829.707.707-2.829 9.9-9.9zM19 7l-2-2" />
              </svg>
              {{ profile.pen_name }}
            </span>

            <span
              v-if="profile.birthdate || profile.birthday || profile.dob"
              class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1.5 rounded-full text-sm font-sans"
            >
              {{ formatBnDate(profile.birthdate || profile.birthday || profile.dob) }}
            </span>

            <span
              v-if="profile.location"
              class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1.5 rounded-full text-sm font-sans"
            >
              <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19.5 8c0 7.5-7.5 12-7.5 12S4.5 15.5 4.5 8a7.5 7.5 0 1115 0z" />
              </svg>
              {{ profile.location }}
            </span>
          </div>
        </div>

        <!-- Bio -->
        <article v-if="profile.bio_md" class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-3 sm:px-8 py-6 sm:py-8">
            <h2 class="heading-bangla heading-bangla-2 mb-4 sm:mb-6">পরিচিতি</h2>
            <div class="prose max-w-none" v-html="md(profile.bio_md)"></div>
          </div>
        </article>

        <!-- Achievements summary -->
        <article v-if="profile.achievements_summary_md" class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-3 sm:px-8 py-6 sm:py-8">
            <h2 class="heading-bangla heading-bangla-2 mb-4 sm:mb-6">অর্জনের সারসংক্ষেপ</h2>
            <div class="prose max-w-none" v-html="md(profile.achievements_summary_md)"></div>
          </div>
        </article>

        <!-- Achievements list -->
        <section v-if="profile.achievements?.length" class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-3 sm:px-8 py-6 sm:py-8">
            <h2 class="heading-bangla heading-bangla-2 mb-4 sm:mb-6">অর্জনসমূহ</h2>
            <ul class="space-y-6">
              <li
                v-for="a in profile.achievements"
                :key="a.id"
                class="p-5 sm:p-6 rounded-xl border border-gray-200 hover:shadow-md transition-all duration-200 card-bangla-hover"
              >
                <div class="flex items-start justify-between gap-4 mb-3 sm:mb-4">
                  <h3 class="heading-bangla text-xl font-semibold text-ink leading-snug">{{ a.title }}</h3>
                  <span v-if="a.year" class="badge-bangla badge-bangla-primary whitespace-nowrap">
                    {{ formatBnDate(a.year) }}
                  </span>
                </div>

                <div v-if="a.image_path" class="mb-4">
                  <div class="aspect-w-16 aspect-h-9 relative rounded-xl overflow-hidden shadow-sm">
                    <img
                      :src="`/storage/${a.image_path}`"
                      :alt="a.title"
                      class="absolute inset-0 w-full h-full object-cover"
                      loading="lazy"
                      decoding="async"
                    />
                  </div>
                </div>

                <div v-if="a.description_md" class="prose max-w-none" v-html="md(a.description_md)"></div>
              </li>
            </ul>
          </div>
        </section>

        <!-- Life events -->
        <section v-if="profile.events?.length" class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-3 sm:px-8 py-6 sm:py-8">
            <h2 class="heading-bangla heading-bangla-2 mb-4 sm:mb-6">জীবনী</h2>

            <ol class="relative border-s border-gray-200 ps-6 md:ps-8">
              <li v-for="e in profile.events" :key="e.id" class="mb-8 ms-6">
                <span class="absolute -start-3 grid h-6 w-6 place-items-center rounded-full bg-blue-600 ring-4 ring-white">
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </span>

                <div class="flex flex-wrap items-center gap-3 text-sm text-muted mb-2 font-sans">
                  <span v-if="e.happened_at" class="badge-bangla badge-bangla-muted">{{ formatBnDate(e.happened_at) }}</span>
                  <span v-if="e.location" class="badge-bangla badge-bangla-muted">{{ e.location }}</span>
                </div>

                <h3 class="heading-bangla text-xl font-semibold text-ink mb-3">{{ e.headline }}</h3>

                <div v-if="e.image_path" class="mb-4">
                  <div class="aspect-w-16 aspect-h-9 relative rounded-xl overflow-hidden shadow-sm">
                    <img
                      :src="`/storage/${e.image_path}`"
                      :alt="e.headline"
                      class="absolute inset-0 w-full h-full object-cover"
                      loading="lazy"
                      decoding="async"
                    />
                  </div>
                </div>

                <div v-if="e.description_md" class="prose max-w-none mt-4" v-html="md(e.description_md)"></div>
              </li>
            </ol>
          </div>
        </section>
      </div>

      <!-- Empty state -->
      <div v-else class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm text-center py-16 px-3 sm:px-8">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <h2 class="heading-bangla heading-bangla-2 text-gray-900 mb-3">প্রোফাইল এখনো কনফিগার করা হয়নি</h2>
        <p class="text-muted mb-6 font-sans">অ্যাডমিন প্যানেল থেকে লেখকের প্রোফাইল সেটআপ করুন।</p>
        <Link href="/" class="btn-bangla btn-bangla-primary">হোমপেজে ফিরে যান</Link>
      </div>

      <!-- CTA -->
      <section v-if="profile" class="mt-10 sm:mt-12">
        <div class="rounded-md sm:rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-sm">
          <div class="px-3 sm:px-8 py-6 sm:py-8 text-center">
            <h3 class="heading-bangla text-xl sm:text-2xl mb-3 sm:mb-4">লেখকের সাহিত্যকর্ম এক্সপ্লোর করুন</h3>
            <p class="text-muted mb-5 sm:mb-6 font-sans text-base sm:text-lg">বই এবং লেখা পড়ুন</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
              <Link href="/books" class="btn-bangla btn-bangla-primary">বই দেখুন</Link>
              <Link href="/blog" class="btn-bangla btn-bangla-secondary">ব্লগ পড়ুন</Link>
              <Link href="/contact" class="btn-bangla btn-bangla-ghost">যোগাযোগ করুন</Link>
            </div>
          </div>
        </div>
      </section>
    </section>
  </div>
</template>

<style scoped>
/* Aspect helpers (no plugin) */
.aspect-w-16 { position: relative; }
.aspect-h-9::before { content: ''; display: block; padding-bottom: 56.25%; }
.aspect-h-9 > * { position: absolute; inset: 0; width: 100%; height: 100%; }

/* Keep images tidy */
img { max-width: 100%; height: auto; object-fit: cover; }

/* Timeline border helper */
.relative.border-s { border-left-width: 2px; }
</style>
