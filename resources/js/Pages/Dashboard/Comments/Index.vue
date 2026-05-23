<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

// ✅ Attach Admin Layout
defineOptions({ layout: AdminLayout })

const busyId = ref(null)

const { props: pageProps } = usePage()
const flash = pageProps.flash || {}

const props = defineProps({
  comments: { type: Object, default: () => ({ data: [], total: 0, from: 0, to: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }) },
})

// ✅ Get status counts
const getApprovedCount = () => {
  return props.comments.data.filter(c => c.is_approved).length
}

const getPendingCount = () => {
  return props.comments.data.filter(c => !c.is_approved).length
}

const getUniqueContentCount = () => {
  return new Set(props.comments.data.map(c => c.commentable_type)).size
}

// ✅ Approve Comment
function approve(id) {
  router.patch(`/dashboard/comments/${id}/approve`, {}, { preserveScroll: true })
}

// ✅ Unapprove Comment
function unapprove(id) {
  router.patch(`/dashboard/comments/${id}/unapprove`, {}, { preserveScroll: true })
}

// ✅ Delete Comment
function destroy(id) {
  if (confirm('Are you sure you want to delete this comment?')) {
    router.delete(`/dashboard/comments/${id}`, { preserveScroll: true })
  }
}

// ✅ Format date
function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// ✅ Truncate text
function truncateText(text, length = 100) {
  if (!text) return '—'
  return text.length > length ? text.substring(0, length) + '...' : text
}
</script>

<template>
  <div>
    <!-- ✅ Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl font-bold text-gray-900">Comments Management</h1>
        <p class="text-gray-600 mt-1">Manage and moderate user comments</p>
      </div>
      <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
        Total {{ comments.total }} Comments
      </div>
    </div>

    <!-- ✅ Flash Message -->
    <div
      v-if="flash.success"
      class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-green-800 font-medium">{{ flash.success }}</span>
      </div>
    </div>

    <!-- ✅ Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Total Comments</p>
            <p class="text-2xl font-bold text-gray-900">{{ comments.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Approved</p>
            <p class="text-2xl font-bold text-gray-900">{{ getApprovedCount() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-yellow-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-gray-900">{{ getPendingCount() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Content Types</p>
            <p class="text-2xl font-bold text-gray-900">{{ getUniqueContentCount() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Comments Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Author
              </th>
              <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Comment
              </th>
              <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Content
              </th>
              <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-3 py-3 sm:px-6 sm:py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-3 py-3 sm:px-6 sm:py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Empty State -->
            <tr v-if="!comments.data.length">
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center text-gray-500">
                  <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                  </svg>
                  <p class="text-lg font-medium text-gray-900 mb-1">No comments found</p>
                  <p class="text-sm">When readers post comments, they will appear here</p>
                </div>
              </td>
            </tr>

            <!-- Comments List -->
            <tr 
              v-for="comment in comments.data" 
              :key="comment.id" 
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-3 py-3 sm:px-6 sm:py-4">
                <div class="flex items-center">
                  <div class="bg-gray-100 rounded-full w-10 h-10 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ comment.author?.name || 'Anonymous' }}</p>
                    <p v-if="comment.author?.email" class="text-sm text-gray-600">{{ comment.author.email }}</p>
                  </div>
                </div>
              </td>

              <td class="px-3 py-3 sm:px-6 sm:py-4">
                <div class="max-w-xs">
                  <p class="text-gray-900 leading-relaxed">
                    {{ truncateText(comment.body, 120) }}
                  </p>
                </div>
              </td>

              <td class="px-3 py-3 sm:px-6 sm:py-4">
                <div class="max-w-xs">
                  <p class="font-medium text-gray-900">
                    {{ comment.commentable?.title || '—' }}
                  </p>
                  <p class="text-sm text-gray-600 capitalize">
                    {{ comment.commentable_type?.toLowerCase() || 'Content' }}
                  </p>
                </div>
              </td>

              <td class="px-3 py-3 sm:px-6 sm:py-4">
                <span class="text-gray-700 text-sm">
                  {{ formatDate(comment.created_at) }}
                </span>
              </td>

              <td class="px-3 py-3 sm:px-6 sm:py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize"
                  :class="comment.is_approved
                    ? 'bg-green-100 text-green-800'
                    : 'bg-yellow-100 text-yellow-800'"
                >
                  <span 
                    class="w-2 h-2 rounded-full mr-2"
                    :class="comment.is_approved ? 'bg-green-500' : 'bg-yellow-500'"
                  ></span>
                  {{ comment.is_approved ? 'Approved' : 'Pending' }}
                </span>
              </td>

              <td class="px-3 py-3 sm:px-6 sm:py-4">
                <div class="flex justify-end gap-2">
                  <button
                    v-if="!comment.is_approved"
                    @click="approve(comment.id)" :disabled="busyId === comment.id"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve
                  </button>
                  
                  <button
                    v-if="comment.is_approved"
                    @click="unapprove(comment.id)" :disabled="busyId === comment.id"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Unapprove
                  </button>
                  
                  <button
                    @click="destroy(comment.id)" :disabled="busyId === comment.id"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ✅ Pagination -->
      <div
        v-if="comments.prev_page_url || comments.next_page_url"
        class="px-3 py-3 sm:px-6 sm:py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div class="text-sm text-gray-700">
          Showing {{ comments.from }} to {{ comments.to }} of {{ comments.total }} results
        </div>
        
        <div class="flex gap-2">
          <Link
            v-if="comments.prev_page_url"
            :href="comments.prev_page_url"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            preserve-scroll
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Previous
          </Link>

          <Link
            v-if="comments.next_page_url"
            :href="comments.next_page_url"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            preserve-scroll
          >
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>