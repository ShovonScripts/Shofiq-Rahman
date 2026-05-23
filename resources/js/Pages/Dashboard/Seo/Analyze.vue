<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

defineProps({
  missingMetaPosts: { type: Array, default: () => [] },
  missingMetaBooks: { type: Array, default: () => [] },
  shortPosts: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ totalPosts: 0, totalBooks: 0, goodMetaPosts: 0, goodMetaBooks: 0 }) },
})
</script>

<template>
  <Head title="SEO Analysis" />
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 heading-bangla">SEO Analysis</h1>
        <p class="text-muted text-sm mt-1">Overview of your site's SEO health</p>
      </div>
      <Link href="/dashboard/seo" class="btn-bangla btn-bangla-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        SEO Settings
      </Link>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <p class="text-xs text-gray-500 font-sans">Total Posts</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalPosts }}</p>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <p class="text-xs text-gray-500 font-sans">Total Books</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalBooks }}</p>
      </div>
      <div class="bg-green-50 rounded-xl border border-green-200 p-4 shadow-sm">
        <p class="text-xs text-green-600 font-sans font-medium">Posts with Meta</p>
        <p class="text-3xl font-bold text-green-900 mt-1">{{ stats.goodMetaPosts }}</p>
      </div>
      <div class="bg-green-50 rounded-xl border border-green-200 p-4 shadow-sm">
        <p class="text-xs text-green-600 font-sans font-medium">Books with Meta</p>
        <p class="text-3xl font-bold text-green-900 mt-1">{{ stats.goodMetaBooks }}</p>
      </div>
    </div>

    <!-- Missing Meta: Posts -->
    <div v-if="missingMetaPosts.length" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-red-50 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div>
          <h3 class="font-semibold text-red-800">Posts Missing SEO Metadata</h3>
          <p class="text-xs text-red-600">These published posts have empty meta title or description</p>
        </div>
      </div>
      <div class="divide-y divide-gray-100">
        <div v-for="p in missingMetaPosts" :key="p.id" class="px-4 sm:px-6 py-3 flex items-center justify-between">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900 truncate">{{ p.title }}</p>
            <p class="text-xs text-gray-500">/lekha/{{ p.slug }}</p>
          </div>
          <Link :href="`/dashboard/posts/${p.id}/edit`" class="btn-bangla btn-bangla-sm btn-bangla-ghost text-blue-600 shrink-0 ml-3">
            Edit
          </Link>
        </div>
      </div>
    </div>

    <!-- Missing Meta: Books -->
    <div v-if="missingMetaBooks.length" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-red-50 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <div>
          <h3 class="font-semibold text-red-800">Books Missing SEO Metadata</h3>
          <p class="text-xs text-red-600">These published books have empty meta title or description</p>
        </div>
      </div>
      <div class="divide-y divide-gray-100">
        <div v-for="b in missingMetaBooks" :key="b.id" class="px-4 sm:px-6 py-3 flex items-center justify-between">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900 truncate">{{ b.title }}</p>
            <p class="text-xs text-gray-500">/books/{{ b.slug }}</p>
          </div>
          <Link :href="`/dashboard/books/${b.id}/edit`" class="btn-bangla btn-bangla-sm btn-bangla-ghost text-blue-600 shrink-0 ml-3">
            Edit
          </Link>
        </div>
      </div>
    </div>

    <!-- Short Content -->
    <div v-if="shortPosts.length" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-yellow-50 flex items-center gap-3">
        <svg class="w-5 h-5 text-yellow-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <h3 class="font-semibold text-yellow-800">Thin Content</h3>
          <p class="text-xs text-yellow-600">Posts with very short body text (&lt;300 chars) - may rank poorly</p>
        </div>
      </div>
      <div class="divide-y divide-gray-100">
        <div v-for="p in shortPosts" :key="p.id" class="px-4 sm:px-6 py-3 flex items-center justify-between">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900 truncate">{{ p.title }}</p>
            <p class="text-xs text-gray-500">/lekha/{{ p.slug }}</p>
          </div>
          <Link :href="`/dashboard/posts/${p.id}/edit`" class="btn-bangla btn-bangla-sm btn-bangla-ghost text-blue-600 shrink-0 ml-3">
            Edit
          </Link>
        </div>
      </div>
    </div>

    <!-- All Good -->
    <div v-if="!missingMetaPosts.length && !missingMetaBooks.length && !shortPosts.length" class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
      <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-1">Looking Good!</h3>
      <p class="text-sm text-muted">All published content has proper SEO metadata set.</p>
    </div>
  </div>
</template>
