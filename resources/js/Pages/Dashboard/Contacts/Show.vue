<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
defineOptions({ layout: AdminLayout })

const props = defineProps({ message: Object })

const form = useForm({ 
  reply_subject: props.message.reply_subject || '', 
  reply_body: props.message.reply_body || '' 
})

const sendReply = () => {
  form.post(`/dashboard/contacts/${props.message.id}/reply`, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}

const deleteMsg = () => {
  if (confirm('Are you sure you want to delete this message?')) {
    router.delete(`/dashboard/contacts/${props.message.id}`)
  }
}

// ✅ Format date
function formatDate(date) {
  if (!date) return '—'
  
  // Handle invalid dates
  const dateObj = new Date(date)
  if (isNaN(dateObj.getTime())) return '—'
  
  return dateObj.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// ✅ Get status text
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

// ✅ Get status color
function getStatusColor(status) {
  const statusMap = {
    'new': { bg: 'bg-blue-100', text: 'text-blue-800', border: 'border-blue-200', dot: 'bg-blue-500' },
    'unread': { bg: 'bg-blue-100', text: 'text-blue-800', border: 'border-blue-200', dot: 'bg-blue-500' },
    'read': { bg: 'bg-green-100', text: 'text-green-800', border: 'border-green-200', dot: 'bg-green-500' },
    'replied': { bg: 'bg-purple-100', text: 'text-purple-800', border: 'border-purple-200', dot: 'bg-purple-500' },
    'archived': { bg: 'bg-gray-100', text: 'text-gray-800', border: 'border-gray-200', dot: 'bg-gray-500' }
  }
  return statusMap[status] || statusMap.unread
}

// ✅ Check if message has been replied to
const hasReply = (message) => {
  return message.reply_subject || message.reply_body
}

// ✅ Format reply date if exists
function formatReplyDate(date) {
  if (!date) return '—'
  
  const dateObj = new Date(date)
  if (isNaN(dateObj.getTime())) return '—'
  
  return dateObj.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <div class="py-6">
    <Head :title="`Message from ${message.name}`" />
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div class="flex-1">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
          Message from {{ message.name }}
        </h1>
        <div class="flex flex-wrap items-center gap-3 text-gray-600">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ formatDate(message.created_at) }}
          </span>
          <span class="hidden sm:block">•</span>
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            {{ message.email }}
          </span>
          <span class="hidden sm:block">•</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium capitalize"
            :class="[getStatusColor(message.status).bg, getStatusColor(message.status).text, getStatusColor(message.status).border]">
            <span class="w-1.5 h-1.5 rounded-full mr-1" :class="getStatusColor(message.status).dot"></span>
            {{ getStatusText(message.status) }}
          </span>
        </div>
      </div>
      <button
        class="inline-flex items-center px-4 py-2.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 font-medium gap-2 flex-shrink-0"
        @click="deleteMsg"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Delete Message
      </button>
    </div>

    <!-- Flash Message -->
    <div
      v-if="$page.props.flash?.success"
      class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ $page.props.flash.success }}
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="form.hasErrors"
      class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span>Sorry, something went wrong. Please try again.</span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:p-6">
      <!-- Message Details -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full">
        <div class="p-4 sm:p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Message Details</h2>
          
          <!-- Sender Information -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-start space-x-3">
              <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-semibold text-gray-900 text-lg">{{ message.name }}</h3>
                <p class="text-gray-600">{{ message.email }}</p>
                <p v-if="message.phone" class="text-blue-600 mt-1 flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  {{ message.phone }}
                </p>
              </div>
            </div>
          </div>

          <!-- Subject -->
          <div class="mb-6">
            <h4 class="font-medium text-gray-900 mb-2">Subject</h4>
            <p class="text-gray-900 bg-white border border-gray-200 rounded-lg px-4 py-3">
              {{ message.subject || 'No Subject' }}
            </p>
          </div>

          <!-- Message Content -->
          <div>
            <h4 class="font-medium text-gray-900 mb-2">Message</h4>
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 whitespace-pre-wrap leading-relaxed text-gray-900 min-h-32">
              {{ message.message }}
            </div>
          </div>

          <!-- Meta Information -->
          <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
              <div>
                <span class="font-medium text-gray-700">Received:</span>
                <div class="mt-1">{{ formatDate(message.created_at) }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">IP Address:</span>
                <div class="mt-1">{{ message.ip_address || 'Unknown' }}</div>
              </div>
              <div v-if="message.replied_at" class="sm:col-span-2">
                <span class="font-medium text-gray-700">Replied At:</span>
                <div class="mt-1">{{ formatReplyDate(message.replied_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reply Form -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full">
        <div class="p-4 sm:p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Send Reply</h2>
          
          <form @submit.prevent="sendReply" class="space-y-4">
            <!-- Subject -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2" for="reply_subject">
                Reply Subject *
              </label>
              <input
                id="reply_subject"
                type="text"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-gray-900 bg-white"
                v-model="form.reply_subject"
                placeholder="Enter reply subject"
                required
                :disabled="form.processing"
                :class="{ 'border-red-500 ring-red-500': form.errors.reply_subject }"
              />
              <div class="text-red-600 text-sm mt-1" v-if="form.errors.reply_subject">
                {{ form.errors.reply_subject }}
              </div>
            </div>

            <!-- Message Body -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2" for="reply_body">
                Reply Message *
              </label>
              <textarea
                id="reply_body"
                rows="10"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 text-gray-900 bg-white resize-none"
                v-model="form.reply_body"
                placeholder="Type your reply message here..."
                required
                :disabled="form.processing"
                :class="{ 'border-red-500 ring-red-500': form.errors.reply_body }"
              ></textarea>
              <div class="text-red-600 text-sm mt-1" v-if="form.errors.reply_body">
                {{ form.errors.reply_body }}
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
              <button 
                type="submit"
                class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 flex-1"
                :disabled="form.processing"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ form.processing ? 'Sending...' : 'Send Reply' }}
              </button>
              
              <button
                type="button"
                class="px-4 py-2.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 flex-1 sm:flex-none"
                @click="deleteMsg"
                :disabled="form.processing"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
              </button>
            </div>
          </form>

          <!-- Previous Reply (if exists) -->
          <div v-if="hasReply(message)" class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold text-gray-900">Previous Reply</h3>
              <span class="text-sm text-gray-500">
                {{ formatReplyDate(message.replied_at) }}
              </span>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="mb-3">
                <strong class="text-gray-900">Subject:</strong>
                <span class="text-gray-700 ml-2">{{ message.reply_subject }}</span>
              </div>
              <div class="whitespace-pre-wrap leading-relaxed text-gray-700">
                {{ message.reply_body }}
              </div>
            </div>
          </div>

          <!-- No Reply Yet -->
          <div v-else-if="message.status !== 'replied'" class="mt-6 pt-6 border-t border-gray-200">
            <div class="text-center text-gray-500 py-4">
              <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <p>This message hasn't been replied to yet</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles for disabled form controls */
.w-full:disabled {
  @apply bg-gray-100 cursor-not-allowed opacity-70;
}

.whitespace-pre-wrap {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>