<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 flex items-center justify-center px-4 py-8">
    <div class="w-full" style="max-width: 440px;">
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex flex-col items-center no-underline group">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-purple-700 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200 group-hover:shadow-xl group-hover:shadow-blue-300 transition-shadow mb-3">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
          </div>
          <h1 class="text-xl font-bold text-gray-900 heading-bangla">{{ $page.props.settings?.site_name || 'Writer' }}</h1>
          <p v-if="$page.props.settings?.tagline" class="text-sm text-gray-500 mt-0.5">{{ $page.props.settings.tagline }}</p>
        </Link>
      </div>

      <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="px-8 py-8">
          <slot />
        </div>
      </div>

      <div class="text-center mt-6 flex items-center justify-center gap-3 text-sm">
        <Link href="/login" class="text-gray-500 hover:text-blue-600 transition-colors font-medium">Login</Link>
        <span class="text-gray-300">·</span>
        <Link href="/register" class="text-gray-500 hover:text-blue-600 transition-colors font-medium">Register</Link>
      </div>
    </div>
  </div>
</template>
