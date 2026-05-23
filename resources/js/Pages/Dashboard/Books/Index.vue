<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const deleting = ref(null)

const props = defineProps({
  books: { type: Object, default: () => ({ data: [], total: 0, from: 0, to: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }) },
})

const destroy = (id) => {
  if (confirm('Are you sure you want to delete this book?')) {
    router.delete(`/dashboard/books/${id}`, { preserveScroll: true })
  }
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'published': return 'badge-bangla badge-bangla-success'
    case 'draft': return 'badge-bangla badge-bangla-warning'
    default: return 'badge-bangla badge-bangla-muted'
  }
}
</script>

<template>
  <Head title="Books Management" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Books Management</h1>
      <p class="text-muted text-sm mt-1">Manage your published and draft books</p>
    </div>
    <Link href="/dashboard/books/create" class="btn-bangla btn-bangla-primary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Add New Book
    </Link>
  </div>

  <!-- Flash -->
  <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Total Books</p>
          <p class="text-2xl font-bold text-gray-900">{{ books.total }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Published</p>
          <p class="text-2xl font-bold text-gray-900">{{ books.data.filter(b => b.status === 'published').length }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Draft</p>
          <p class="text-2xl font-bold text-gray-900">{{ books.data.filter(b => b.status === 'draft').length }}</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card-bangla overflow-hidden">
    <div class="overflow-x-auto">
      <table class="table-bangla">
        <thead>
          <tr>
            <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase">Book Title</th>
            <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase">Genre</th>
            <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase">Published Date</th>
            <th class="px-3 py-3 sm:px-6 sm:py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!books.data.length">
            <td colspan="5" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-lg font-semibold text-gray-900 mb-1">No books found</p>
                <p class="text-sm text-muted">Get started by creating your first book</p>
              </div>
            </td>
          </tr>
          <tr v-for="book in books.data" :key="book.id" class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-3 py-3 sm:px-6 sm:py-4">
              <Link :href="`/dashboard/books/${book.id}/edit`" class="text-gray-900 hover:text-blue-600 font-semibold text-lg leading-tight block">
                {{ book.title }}
              </Link>
              <p v-if="book.subtitle" class="text-sm text-muted mt-1">{{ book.subtitle }}</p>
            </td>
            <td class="px-3 py-3 sm:px-6 sm:py-4">
              <span v-if="book.genre" class="text-gray-700">{{ book.genre.name }}</span>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-3 py-3 sm:px-6 sm:py-4">
              <span :class="getStatusBadgeClass(book.status)">{{ book.status }}</span>
            </td>
            <td class="px-3 py-3 sm:px-6 sm:py-4">
              <span class="text-gray-700">{{ formatDate(book.published_at) }}</span>
            </td>
            <td class="px-3 py-3 sm:px-6 sm:py-4">
              <div class="flex justify-end gap-2">
                <Link :href="`/dashboard/books/${book.id}/edit`" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  Edit
                </Link>
                <button @click="destroy(book.id)" class="btn-bangla btn-bangla-ghost text-sm text-red-600 flex items-center gap-1" :disabled="deleting === book.id">
                  <svg v-if="deleting === book.id" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0a12 12 0 00-8 20.784L6 18.137A7.96 7.96 0 014 12z"/></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  <span v-if="deleting === book.id">Deleting...</span>
                  <span v-else>Delete</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <div v-if="books.prev_page_url || books.next_page_url" class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="text-sm text-muted">Showing {{ books.from }} to {{ books.to }} of {{ books.total }} results</div>
    <div class="flex gap-2">
      <Link v-if="books.prev_page_url" :href="books.prev_page_url" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1" preserve-scroll>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Previous
      </Link>
      <Link v-if="books.next_page_url" :href="books.next_page_url" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1" preserve-scroll>
        Next
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </Link>
    </div>
  </div>
</template>
