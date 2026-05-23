<script>
import { Link } from '@inertiajs/vue3'

export default {
  name: 'BookCard',
  components: { Link },
  props: {
    book: { type: Object, required: true },
  },
}
</script>

<template>
  <article class="group">
    <div class="h-full flex flex-col overflow-hidden rounded-md bg-white border border-gray-100 shadow-sm">
      <div class="book-cover-container overflow-hidden">
        <img
          v-if="book.cover_url"
          :src="book.cover_url"
          :alt="book.title"
          class="w-full h-full object-cover group-hover:scale-105 transition-smooth"
          loading="lazy"
          decoding="async"
        />
        <div
          v-else
          class="w-full h-full bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center"
        >
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>

        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-r from-black/20 to-transparent"></div>

        <div v-if="book.published_year" class="absolute top-2 right-2">
          <span class="bg-black/70 text-white text-[11px] sm:text-xs px-2 py-0.5 rounded-full font-sans">
            {{ book.published_year }}
          </span>
        </div>
      </div>

      <div class="p-3 sm:p-4 flex-1">
        <h3 class="heading-bangla text-sm sm:text-base mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
          <Link :href="`/books/${book.slug}`" class="hover:no-underline focus-bangla rounded">
            {{ book.title }}
          </Link>
        </h3>

        <p
          v-if="book.subtitle"
          class="text-[12px] sm:text-xs text-muted mt-0.5 line-clamp-2 font-sans"
        >
          {{ book.subtitle }}
        </p>

        <div class="mt-2 space-y-1">
          <div v-if="book.genres && book.genres.length" class="flex flex-wrap gap-1">
            <span
              v-for="genre in book.genres.slice(0, 2)"
              :key="genre.id"
              class="inline-block bg-blue-100 text-blue-800 text-[11px] sm:text-xs px-2 py-0.5 rounded font-sans"
            >
              {{ genre.name }}
            </span>
            <span
              v-if="book.genres.length > 2"
              class="inline-block bg-gray-100 text-gray-600 text-[11px] sm:text-xs px-2 py-0.5 rounded font-sans"
            >
              +{{ book.genres.length - 2 }}
            </span>
          </div>

          <div v-if="book.price" class="flex items-center justify-between">
            <span class="text-green-600 font-bold text-sm font-sans">
              ৳{{ book.price }}
            </span>
            <span v-if="book.is_available" class="text-green-500 text-[11px] sm:text-xs font-sans">
              Available
            </span>
            <span v-else class="text-red-500 text-[11px] sm:text-xs font-sans">
              Out of Stock
            </span>
          </div>

          <div v-if="book.pages" class="flex items-center text-[11px] sm:text-xs text-gray-500 font-sans">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            {{ book.pages }} পৃষ্ঠা
          </div>

          <div v-if="book.rating" class="flex items-center">
            <div class="flex items-center">
              <svg
                v-for="star in 5"
                :key="star"
                class="w-3 h-3"
                :class="star <= Math.round(book.rating) ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </div>
            <span class="text-[11px] sm:text-xs text-gray-600 ml-1 font-sans">{{ book.rating }}</span>
          </div>
        </div>

        <div class="mt-3 flex gap-2">
          <Link
            :href="`/books/${book.slug}`"
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-[12px] sm:text-xs py-2 px-3 rounded text-center transition-colors duration-200 font-sans"
          >
            বিস্তারিত
          </Link>
          <button
            v-if="book.preview_url"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-[12px] sm:text-xs py-2 px-3 rounded transition-colors duration-200 font-sans"
          >
            Preview
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.transition-smooth { transition: all 0.3s ease; }

.book-cover-container {
  position: relative;
  aspect-ratio: 3/4;
  background: linear-gradient(145deg, #f0f0f0, #e0e0e0);
  box-shadow:
    2px 2px 8px rgba(0,0,0,0.08),
    inset 1px 1px 0 rgba(255,255,255,0.5);
}
.book-cover-container::after {
  content: '';
  position: absolute;
  bottom: -4px; left: 4px; right: 4px;
  height: 8px;
  background: linear-gradient(to bottom, rgba(0,0,0,0.1), transparent);
  border-radius: 50%;
  filter: blur(4px);
  z-index: -1;
}
.group:hover .book-cover-container {
  transform: translateY(-2px);
  box-shadow:
    4px 4px 16px rgba(0,0,0,0.14),
    inset 1px 1px 0 rgba(255,255,255,0.5);
}
.group:hover .book-cover-container::after {
  bottom: -6px; height: 12px; filter: blur(6px);
}

@media (max-width: 640px) {
  .book-cover-container { aspect-ratio: 2/3; }
}
</style>
