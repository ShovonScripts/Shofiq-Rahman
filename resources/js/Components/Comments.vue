<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  items: Array,          // [{id, body, author, user_id, time}]
  parentId: Number,      // post.id বা book.id
  type: String,          // 'post' | 'book'
  canDeleteIds: {        // optional admin or special users
    type: Array,
    default: () => [],
  },
})

// Form state
const form = reactive({ body: '', website: '' }) // honeypot

// Submit new comment
function submit() {
  if (form.website !== '') return // Honeypot check
  if (!form.body.trim()) return alert('Comment empty!')

  router.post('/comments', { type: props.type, id: props.parentId, body: form.body }, {
    preserveScroll: true,
    onSuccess: (response) => {
      // Laravel থেকে JSON response হিসেবে comment আসবে
      if (response.props?.comment) {
        props.items.unshift(response.props.comment)
      }
      form.body = ''
    },
    onError: () => alert('Comment post করতে সমস্যা হয়েছে!'),
  })
}

// Delete comment
function destroy(id) {
  if (!confirm('মুছে ফেলবেন?')) return

  router.delete(`/comments/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      const index = props.items.findIndex(c => c.id === id)
      if (index !== -1) props.items.splice(index, 1)
    },
    onError: () => alert('Comment delete করতে সমস্যা হয়েছে!'),
  })
}
</script>

<template>
  <section class="mt-4">
    <h3 class="h6 mb-2">মন্তব্য</h3>

    <!-- New comment (শুধু লগইন হলে) -->
    <form v-if="$page.props.auth?.user" @submit.prevent="submit" class="mb-3">
      <textarea v-model="form.body" rows="3" class="form-control" placeholder="আপনার মতামত…"></textarea>
      <!-- Honeypot hidden input -->
      <input type="text" v-model="form.website" style="display:none;" />
      <div class="mt-2 d-flex gap-2">
        <button class="btn btn-primary btn-sm">পাঠান</button>
      </div>
    </form>
    <p v-else class="text-muted small">কমেন্ট করতে <a href="/login">লগইন</a> করুন।</p>

    <!-- List of comments -->
    <div v-if="items?.length" class="vstack gap-2">
      <div v-for="c in items" :key="c.id" class="border rounded p-2">
        <div class="small text-muted d-flex justify-content-between">
          <span>{{ c.author || 'অজানা' }} • {{ c.time }}</span>
          <button
            v-if="$page.props.auth?.user && ($page.props.auth.user.id === c.user_id || props.canDeleteIds.includes(c.user_id))"
            class="btn btn-link btn-sm text-danger p-0"
            @click="destroy(c.id)">
            মুছুন
          </button>
        </div>
        <div class="mt-1">{{ c.body }}</div>
      </div>
    </div>
    <div v-else class="text-muted small">এখনও কোনো মন্তব্য নেই।</div>
  </section>
</template>
