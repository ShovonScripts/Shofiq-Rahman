<script setup>
import { ref } from 'vue'
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { Link, router, Head } from '@inertiajs/vue3'

// ✅ Attach layout
defineOptions({ layout: AdminLayout })

// ✅ Props
defineProps({
  posts: Object,
})

// ✅ Reactive state
const busyId = ref(null)

// ✅ Toggle publish/unpublish
const onToggle = (id) => {
  if (busyId.value) return
  busyId.value = id
  router.patch(`/dashboard/posts/${id}/toggle`, {}, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => (busyId.value = null),
  })
}

// ✅ Delete post
const onDelete = (id) => {
  if (busyId.value) return
  if (!confirm('Are you sure you want to delete this post?')) return
  busyId.value = id
  router.delete(`/dashboard/posts/${id}`, {
    preserveScroll: true,
    preserveState: true,
    onFinish: () => (busyId.value = null),
  })
}

// ✅ Format date
function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// ✅ Get status text
function getStatusText(status) {
  const statusMap = {
    'published': 'Published',
    'scheduled': 'Scheduled',
    'draft': 'Draft'
  }
  return statusMap[status] || status
}
</script>

<template>
  <div class="py-6">
    <Head title="My Posts" />

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-serif font-bold text-ink mb-2">
          My Posts
        </h1>
        <p class="text-muted font-sans">
          List of all blog posts and articles you have created
        </p>
      </div>
      <Link 
        href="/dashboard/posts/create" 
        class="btn-bangla-primary flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Post
      </Link>
    </div>

    <!-- Flash Message -->
    <div
      v-if="$page.props.flash?.success"
      class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 font-sans"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.success }}
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!posts?.data?.length" class="card-bangla">
      <div class="p-12 text-center">
        <div class="flex flex-col items-center justify-center text-gray-500">
          <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          <h3 class="text-lg font-sans font-medium text-gray-900 mb-2">No Posts</h3>
          <p class="text-sm font-sans text-gray-600 mb-6">You haven't created any posts yet</p>
          <Link href="/dashboard/posts/create" class="btn-bangla-primary">
            Create Your First Post
          </Link>
        </div>
      </div>
    </div>

    <!-- Posts Table -->
    <div v-else class="card-bangla overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-sans font-medium text-muted uppercase tracking-wider">
                Title
              </th>
              <th class="px-6 py-4 text-left text-xs font-sans font-medium text-muted uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-4 text-left text-xs font-sans font-medium text-muted uppercase tracking-wider">
                Published Date
              </th>
              <th class="px-6 py-4 text-right text-xs font-sans font-medium text-muted uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="p in posts.data" 
              :key="p.id" 
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4">
                <div class="flex items-start space-x-3">
                  <div class="bg-blue-50 rounded-lg p-2 flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h4 class="font-sans font-semibold text-ink text-lg leading-tight mb-1">
                      {{ p.title }}
                    </h4>
                    <div class="flex items-center space-x-2 text-sm text-muted font-sans">
                      <span>#{{ p.id }}</span>
                      <span>•</span>
                      <span>{{ p.categories || 'No categories' }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-sans font-medium capitalize"
                  :class="{
                    'bg-green-100 text-green-800 border border-green-200': p.status === 'published',
                    'bg-blue-100 text-blue-800 border border-blue-200': p.status === 'scheduled',
                    'bg-gray-100 text-gray-800 border border-gray-200': p.status === 'draft'
                  }"
                >
                  <span 
                    class="w-2 h-2 rounded-full mr-2"
                    :class="{
                      'bg-green-500': p.status === 'published',
                      'bg-blue-500': p.status === 'scheduled',
                      'bg-gray-500': p.status === 'draft'
                    }"
                  ></span>
                  {{ getStatusText(p.status) }}
                </span>
              </td>

              <td class="px-6 py-4">
                <span class="text-ink font-sans">
                  {{ formatDate(p.published_at) }}
                </span>
              </td>

              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <Link
                    :href="`/dashboard/posts/${p.id}/edit`"
                    class="btn-bangla-secondary flex items-center gap-2 px-3 py-2 text-sm"
                    :class="{ 'opacity-50 pointer-events-none': !!busyId }"
                    title="Edit post"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                  </Link>

                  <button
                    class="btn-bangla bg-yellow-50 text-yellow-700 hover:bg-yellow-100 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all duration-200 font-sans disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="onToggle(p.id)"
                    :disabled="busyId === p.id"
                    :title="p.status === 'published' ? 'Unpublish' : 'Publish'"
                  >
                    <svg v-if="busyId === p.id" class="animate-spin -ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="p.status === 'published'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ p.status === 'published' ? 'Unpublish' : 'Publish' }}
                  </button>

                  <button
                    class="btn-bangla bg-red-50 text-red-700 hover:bg-red-100 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-all duration-200 font-sans disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="onDelete(p.id)"
                    :disabled="busyId === p.id"
                    title="Delete post"
                  >
                    <svg v-if="busyId === p.id" class="animate-spin -ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

      <!-- Pagination -->
      <div
        v-if="posts.prev_page_url || posts.next_page_url"
        class="px-6 py-4 border-t border-gray-200"
      >
        <div class="flex items-center justify-between">
          <div class="text-sm text-muted font-sans">
            Showing {{ posts.from }}-{{ posts.to }} of {{ posts.total }} posts
          </div>
          
          <div class="flex items-center gap-4">
            <span class="text-sm text-muted font-sans">
              Page {{ posts.current_page }} / {{ posts.last_page }}
            </span>
            
            <div class="flex gap-2">
              <Link
                v-if="posts.prev_page_url"
                :href="posts.prev_page_url"
                class="btn-bangla-secondary flex items-center gap-2 px-4 py-2 text-sm"
                rel="prev"
                preserve-scroll
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous Page
              </Link>

              <Link
                v-if="posts.next_page_url"
                :href="posts.next_page_url"
                class="btn-bangla-secondary flex items-center gap-2 px-4 py-2 text-sm"
                rel="next"
                preserve-scroll
              >
                Next Page
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div v-if="posts?.data?.length" class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-sans text-blue-600 mb-1">Total Posts</p>
            <p class="text-2xl font-serif font-bold text-blue-900">{{ posts.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-green-50 rounded-lg p-4 border border-green-200">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-sans text-green-600 mb-1">Published</p>
            <p class="text-2xl font-serif font-bold text-green-900">
              {{ posts.data.filter(p => p.status === 'published').length }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <div class="flex items-center">
          <div class="bg-gray-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-sans text-gray-600 mb-1">Draft</p>
            <p class="text-2xl font-serif font-bold text-gray-900">
              {{ posts.data.filter(p => p.status === 'draft').length }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-sans text-purple-600 mb-1">Scheduled</p>
            <p class="text-2xl font-serif font-bold text-purple-900">
              {{ posts.data.filter(p => p.status === 'scheduled').length }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-bangla {
  @apply bg-white rounded-xl shadow-sm border border-gray-200;
}

.btn-bangla-primary {
  @apply bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-sans font-medium;
}

.btn-bangla-secondary {
  @apply bg-gray-100 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-sans font-medium;
}

.btn-bangla {
  @apply px-4 py-2 rounded-lg transition-all duration-200 font-sans font-medium;
}

/* Custom table styles */
.table-responsive {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.table-responsive::-webkit-scrollbar {
  height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>