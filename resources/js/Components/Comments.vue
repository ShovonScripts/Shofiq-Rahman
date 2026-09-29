<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  items: Array,
  parentId: Number,
  type: String,
  canDeleteIds: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['deleted'])

const form = reactive({ body: '', website: '' })
const submitting = ref(false)
const deleting = ref(null)
const errors = reactive({ body: '', submit: '', delete: '' })

function submit() {
  if (form.website !== '') return
  if (!form.body.trim()) {
    errors.body = 'মন্তব্য লিখুন'
    return
  }
  errors.body = ''
  errors.submit = ''
  submitting.value = true

  router.post('/comments', { type: props.type, id: props.parentId, body: form.body }, {
    preserveScroll: true,
    onSuccess: () => {
      // Pending comments stay private; the server's flash confirms submission.
      form.body = ''
    },
    onError: () => {
      errors.submit = 'মন্তব্য পোস্ট করতে সমস্যা হয়েছে!'
    },
    onFinish: () => { submitting.value = false },
  })
}

function destroy(id) {
  if (!confirm('মুছে ফেলবেন?')) return
  errors.delete = ''
  deleting.value = id

  router.delete(`/comments/${id}`, {
    preserveScroll: true,
    onSuccess: () => emit('deleted', id),
    onError: () => {
      errors.delete = 'মুছতে সমস্যা হয়েছে!'
    },
    onFinish: () => { deleting.value = null },
  })
}
</script>

<template>
  <section class="mt-6">
    <h3 class="text-base font-semibold text-gray-900 mb-3">মন্তব্য</h3>

    <form v-if="$page.props.auth?.user?.email_verified_at" @submit.prevent="submit" class="mb-4">
      <textarea
        v-model="form.body"
        rows="3"
        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        :class="{ 'border-red-400': errors.body }"
        placeholder="আপনার মতামত…"
        aria-label="মন্তব্য লিখুন"
      />
      <p v-if="errors.body" class="text-sm text-red-600 mt-1">{{ errors.body }}</p>
      <input type="text" v-model="form.website" aria-hidden="true" class="absolute -left-[9999px] -top-[9999px]" tabindex="-1" autocomplete="off" />
      <div class="mt-2 flex items-center gap-2">
        <button
          type="submit"
          class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition-colors"
          :disabled="submitting"
        >
          <svg v-if="submitting" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0a12 12 0 00-8 20.784L6 18.137A7.96 7.96 0 014 12z" />
          </svg>
          পাঠান
        </button>
      </div>
      <p v-if="errors.submit" class="text-sm text-red-600 mt-1">{{ errors.submit }}</p>
    </form>
    <p v-else-if="$page.props.auth?.user" class="text-sm text-gray-500">মন্তব্য করতে আগে <a href="/verify-email" class="text-blue-600 underline">ইমেইল যাচাই</a> করুন।</p>
    <p v-else class="text-sm text-gray-500">কমেন্ট করতে <a href="/login" class="text-blue-600 hover:text-blue-800 underline">লগইন</a> করুন।</p>

    <div v-if="items?.length" class="space-y-3">
      <div v-for="c in items" :key="c.id" class="border border-gray-200 rounded-lg p-3">
        <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
          <span>{{ c.author || 'অজানা' }} • {{ c.time }}</span>
          <button
            v-if="$page.props.auth?.user && ($page.props.auth.user.id === c.user_id || props.canDeleteIds.includes(c.user_id))"
            class="text-red-600 hover:text-red-800 text-sm font-medium underline disabled:opacity-50"
            :disabled="deleting === c.id"
            @click="destroy(c.id)"
            :aria-label="`মন্তব্য মুছুন`"
          >
            <span v-if="deleting === c.id">মুছছে…</span>
            <span v-else>মুছুন</span>
          </button>
        </div>
        <div class="text-gray-800 text-sm">{{ c.body }}</div>
      </div>
    </div>
    <div v-else class="text-sm text-gray-500">এখনও কোনো মন্তব্য নেই।</div>
    <p v-if="errors.delete" class="text-sm text-red-600 mt-2">{{ errors.delete }}</p>
  </section>
</template>
