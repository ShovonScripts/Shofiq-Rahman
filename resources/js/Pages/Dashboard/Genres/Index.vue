<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const deleting = ref(null)

const props = defineProps({ genres: { type: Object, default: () => ({ data: [], total: 0, from: 0, to: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }) } })

const destroyGenre = (id) => {
  if (confirm('Are you sure you want to delete this genre?')) {
    router.delete(`/dashboard/genres/${id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <Head title="Genres Management" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Genres Management</h1>
      <p class="text-muted text-sm mt-1">Organize and manage your book genres</p>
    </div>
    <Link href="/dashboard/genres/create" class="btn-bangla btn-bangla-primary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Add New Genre
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
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Total Genres</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.genres.total }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Active Genres</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.genres.total }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Last Added</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.genres.data[0]?.name || '—' }}</p>
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
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Genre Name</th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Books Count</th>
            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!props.genres.data.length">
            <td colspan="4" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <p class="text-lg font-semibold text-gray-900 mb-1">No genres found</p>
                <p class="text-sm text-muted">Get started by creating your first genre</p>
              </div>
            </td>
          </tr>
          <tr v-for="genre in props.genres.data" :key="genre.id" class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <span class="text-gray-900 font-semibold">{{ genre.name }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <code class="text-sm bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ genre.slug }}</code>
            </td>
            <td class="px-6 py-4">
              <span class="badge-bangla badge-bangla-muted">{{ genre.books_count || 0 }} books</span>
            </td>
            <td class="px-6 py-4">
              <div class="flex justify-end gap-2">
                <Link :href="`/dashboard/genres/${genre.id}/edit`" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  Edit
                </Link>
                <button @click="destroyGenre(genre.id)" class="btn-bangla btn-bangla-ghost text-sm text-red-600 flex items-center gap-1" :disabled="deleting === genre.id">
                  <svg v-if="deleting === genre.id" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0a12 12 0 00-8 20.784L6 18.137A7.96 7.96 0 014 12z"/></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  <span v-if="deleting === genre.id">Deleting...</span>
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
  <div v-if="props.genres.prev_page_url || props.genres.next_page_url" class="mt-6 px-3 sm:px-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="text-sm text-muted">Showing {{ props.genres.from }} to {{ props.genres.to }} of {{ props.genres.total }} results</div>
    <div class="flex gap-2">
      <Link v-if="props.genres.prev_page_url" :href="props.genres.prev_page_url" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1" preserve-scroll>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Previous
      </Link>
      <Link v-if="props.genres.next_page_url" :href="props.genres.next_page_url" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1" preserve-scroll>
        Next
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </Link>
    </div>
  </div>
</template>

<style scoped>
code {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.875rem;
}
</style>
