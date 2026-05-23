<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

defineProps({
  categories: { type: Array, default: () => [] },
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Head>
      <title>সকল ক্যাটেগরি</title>
      <meta name="description" content="লেখার সকল ক্যাটেগরির তালিকা" />
      <link rel="canonical" href="/categories" />
    </Head>

    <div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <header class="px-3 sm:px-0 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <h1 class="heading-bangla heading-bangla-1 mb-2">সকল ক্যাটেগরি</h1>
            <p class="text-lg text-muted font-sans">বিষয় অনুযায়ী লেখা ব্রাউজ করুন</p>
          </div>
          <div v-if="categories.length" class="badge-bangla badge-bangla-primary">
            মোট {{ categories.length }} টি ক্যাটেগরি
          </div>
        </div>
      </header>

      <!-- Empty State -->
      <section v-if="!categories.length" class="px-3 sm:px-0">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 sm:px-8 py-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="heading-bangla heading-bangla-3 text-gray-900 mb-2">কোনো ক্যাটেগরি নেই</h3>
            <p class="text-muted mb-6 font-sans">এখনো কোনো ক্যাটেগরি তৈরি হয়নি</p>
            <Link href="/blog" class="btn-bangla btn-bangla-primary">সব লেখা দেখুন</Link>
          </div>
        </div>
      </section>

      <!-- Category Grid -->
      <section v-else class="px-3 sm:px-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
          <Link
            v-for="cat in categories"
            :key="cat.id"
            :href="`/category/${cat.slug}`"
            class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden card-bangla-hover group block p-5 sm:p-6"
          >
            <div class="flex items-center justify-between gap-4">
              <h2 class="heading-bangla text-lg sm:text-xl font-semibold text-ink group-hover:text-blue-600 transition-colors">
                {{ cat.name }}
              </h2>
              <span class="badge-bangla badge-bangla-muted whitespace-nowrap">
                {{ cat.post_count }} টি লেখা
              </span>
            </div>
          </Link>
        </div>
      </section>

      <!-- CTA -->
      <section v-if="categories.length" class="px-3 sm:px-0 mt-12">
        <div class="rounded-md sm:rounded-xl bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 shadow-sm">
          <div class="px-4 sm:px-8 py-6 text-center">
            <h3 class="heading-bangla heading-bangla-3 mb-2">লেখা পড়ুন</h3>
            <p class="text-muted mb-6 font-sans">সকল লেখা ব্রাউজ করুন</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
              <Link href="/blog" class="btn-bangla btn-bangla-primary">সব লেখা দেখুন</Link>
              <Link href="/books" class="btn-bangla btn-bangla-secondary">সব বই দেখুন</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
