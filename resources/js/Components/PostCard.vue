<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'PostCard',
  components: { Link },
  props: {
    post: { type: Object, required: true },
  },
}
</script>

<template>
  <article
    class="group flex flex-col overflow-hidden rounded-md border border-gray-100 shadow-sm bg-white"
  >
    <div class="aspect-w-16 aspect-h-9 overflow-hidden">
      <img
        v-if="post.cover_url"
        :src="post.cover_url"
        :alt="post.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-smooth"
        loading="lazy"
        decoding="async"
      />
      <div
        v-else
        class="w-full h-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center"
      >
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </div>
    </div>

    <div class="p-3 sm:p-5 flex flex-col flex-1">
      <h3 class="heading-bangla text-base sm:text-lg mb-1.5 line-clamp-2 group-hover:text-blue-600 transition-colors">
        <Link :href="`/lekha/${post.slug}`" class="hover:no-underline focus-bangla rounded">
          {{ post.title }}
        </Link>
      </h3>

      <div class="flex items-center text-xs sm:text-sm text-muted mb-2 sm:mb-3 font-sans">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>{{ post.date }}</span>
      </div>

      <p class="text-xs sm:text-sm text-muted leading-relaxed-bangla line-clamp-3 font-sans">
        {{ post.excerpt }}
      </p>

      <Link
        :href="`/lekha/${post.slug}`"
        class="inline-flex items-center mt-3 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors duration-200 group/readmore font-sans focus-bangla rounded"
      >
        পড়ুন
        <svg class="w-4 h-4 ml-1 transform group-hover/readmore:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </Link>
    </div>
  </article>
</template>

<style scoped>
.aspect-w-16 { position: relative; }
.aspect-h-9::before { content: ''; display: block; padding-bottom: 56.25%; }
.aspect-h-9 > * {
  position: absolute; height: 100%; width: 100%;
  top: 0; right: 0; bottom: 0; left: 0;
}
.line-clamp-2 {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.line-clamp-3 {
  display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}
.transition-smooth { transition: all 0.3s ease; }
</style>
