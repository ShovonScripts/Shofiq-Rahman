<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({ 
  messages: { type: Object, required: true }
})

// ✅ Delete Message
function destroy(id) {
  if (confirm('Are you sure you want to delete this message?')) {
    router.delete(`/dashboard/contacts/${id}`, { preserveScroll: true })
  }
}

// ✅ Mark as read/unread - Handle both 'new' and 'unread' statuses
function toggleStatus(id, currentStatus) {
  let newStatus;
  if (currentStatus === 'read' || currentStatus === 'replied') {
    newStatus = 'unread';
  } else {
    newStatus = 'read';
  }
  
  router.patch(`/dashboard/contacts/${id}`, { 
    status: newStatus 
  }, { 
    preserveScroll: true,
    onSuccess: () => {
      // Refresh the page to get updated data
      router.reload({ only: ['messages'] })
    }
  })
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

// ✅ Get status color - Handle both 'new' and 'unread' statuses
function getStatusColor(status) {
  const statusMap = {
    'new': { bg: 'bg-blue-100', text: 'text-blue-800', dot: 'bg-blue-500' },
    'unread': { bg: 'bg-blue-100', text: 'text-blue-800', dot: 'bg-blue-500' },
    'read': { bg: 'bg-green-100', text: 'text-green-800', dot: 'bg-green-500' },
    'replied': { bg: 'bg-purple-100', text: 'text-purple-800', dot: 'bg-purple-500' },
    'archived': { bg: 'bg-gray-100', text: 'text-gray-800', dot: 'bg-gray-500' }
  }
  return statusMap[status] || statusMap.unread
}

// ✅ Get status display text
function getStatusText(status) {
  const statusMap = {
    'new': 'Unread',
    'unread': 'Unread',
    'read': 'Read',
    'replied': 'Replied',
    'archived': 'Archived'
  }
  return statusMap[status] || status
}

// ✅ Get status counts - Count both 'new' and 'unread' as unread
const getUnreadCount = () => {
  return props.messages.data.filter(m => m.status === 'new' || m.status === 'unread').length
}

const getReadCount = () => {
  return props.messages.data.filter(m => m.status === 'read').length
}

const getRepliedCount = () => {
  return props.messages.data.filter(m => m.status === 'replied').length
}

// ✅ Check if message has reply
const hasReply = (message) => {
  return message.reply_subject || message.reply_body
}
</script>

<template>
  <div>
    <Head title="Contact Messages" />
    
    <!-- ✅ Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl font-bold text-gray-900">Contact Messages</h1>
        <p class="text-gray-600 mt-1">Manage messages from website visitors</p>
      </div>
      <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
        Total {{ messages.total }} Messages
      </div>
    </div>

    <!-- ✅ Flash Message -->
    <div
      v-if="$page.props.flash?.success"
      class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
      </div>
    </div>

    <!-- ✅ Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Total Messages</p>
            <p class="text-2xl font-bold text-gray-900">{{ messages.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Unread</p>
            <p class="text-2xl font-bold text-gray-900">{{ getUnreadCount() }}</p>
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
            <p class="text-sm font-medium text-gray-600">Read</p>
            <p class="text-2xl font-bold text-gray-900">{{ getReadCount() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Replied</p>
            <p class="text-2xl font-bold text-gray-900">{{ getRepliedCount() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Messages Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Contact Info
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Message
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Received
              </th>
              <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Empty State -->
            <tr v-if="!messages.data.length">
              <td colspan="5" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center text-gray-500">
                  <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  <p class="text-lg font-medium text-gray-900 mb-1">No messages found</p>
                  <p class="text-sm">When visitors use the contact form, messages will appear here</p>
                </div>
              </td>
            </tr>

            <!-- Messages List -->
            <tr 
              v-for="message in messages.data" 
              :key="message.id" 
              class="hover:bg-gray-50 transition-colors duration-150"
              :class="(message.status === 'unread' || message.status === 'new') ? 'bg-blue-50' : ''"
            >
              <td class="px-6 py-4">
                <div class="flex items-start">
                  <div class="bg-gray-100 rounded-full w-10 h-10 flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">
                      {{ message.name }}
                    </p>
                    <p class="text-sm text-gray-600 truncate">
                      {{ message.email }}
                    </p>
                    <p v-if="message.phone" class="text-sm text-blue-600">
                      {{ message.phone }}
                    </p>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="max-w-xs">
                  <p class="font-medium text-gray-900 leading-relaxed">
                    {{ message.subject || 'No Subject' }}
                  </p>
                  <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                    {{ message.message?.substring(0, 80) }}{{ message.message?.length > 80 ? '...' : '' }}
                  </p>
                  <div v-if="hasReply(message)" class="mt-2">
                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                      </svg>
                      Replied
                    </span>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize cursor-pointer"
                  :class="[getStatusColor(message.status).bg, getStatusColor(message.status).text]"
                  @click="toggleStatus(message.id, message.status)"
                  :title="(message.status === 'unread' || message.status === 'new') ? 'Mark as read' : 'Mark as unread'"
                >
                  <span 
                    class="w-2 h-2 rounded-full mr-2"
                    :class="getStatusColor(message.status).dot"
                  ></span>
                  {{ getStatusText(message.status) }}
                </span>
              </td>

              <td class="px-6 py-4">
                <span class="text-gray-700 text-sm">
                  {{ formatDate(message.created_at) }}
                </span>
              </td>

              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <Link
                    :href="`/dashboard/contacts/${message.id}`"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View
                  </Link>
                  <button
                    @click="destroy(message.id)"
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
        v-if="messages.prev_page_url || messages.next_page_url"
        class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div class="text-sm text-gray-700">
          Showing {{ messages.from }} to {{ messages.to }} of {{ messages.total }} results
        </div>
        
        <div class="flex gap-2">
          <Link
            v-if="messages.prev_page_url"
            :href="messages.prev_page_url"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            preserve-scroll
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Previous
          </Link>

          <Link
            v-if="messages.next_page_url"
            :href="messages.next_page_url"
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

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>