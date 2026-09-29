<script>
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

export default {
  name: 'LikeButton',
  props: {
    type: { type: String, required: true },
    id: { type: Number, required: true },
    initialCount: { type: Number, default: 0 },
    initiallyLiked: { type: Boolean, default: false },
  },
  setup(props) {
    const page = usePage()
    const count = ref(props.initialCount)
    const liked = ref(props.initiallyLiked)
    const loading = ref(false)
    const error = ref('')

    watch(() => [props.initialCount, props.initiallyLiked], ([nextCount, nextLiked]) => {
      count.value = nextCount
      liked.value = nextLiked
    })

    const toggle = async () => {
      if (loading.value) return
      if (!page.props.auth?.user) {
        router.visit('/login')
        return
      }
      if (!page.props.auth.user.email_verified_at) {
        router.visit('/verify-email')
        return
      }

      loading.value = true
      error.value = ''
      try {
        // This endpoint returns JSON, not an Inertia page.
        const { data } = await axios.post('/likes/toggle', {
          type: props.type,
          id: props.id,
        }, { headers: { Accept: 'application/json' } })
        liked.value = data.liked
        count.value = data.count
      } catch (failure) {
        if (failure.response?.status === 401) {
          router.visit('/login')
        } else if (failure.response?.status === 403) {
          router.visit('/verify-email')
        } else {
          error.value = 'লাইক করতে সমস্যা হয়েছে! আবার চেষ্টা করুন।'
        }
      } finally {
        loading.value = false
      }
    }

    return { count, liked, toggle, loading, error }
  },
}
</script>

<template>
  <div class="inline-flex items-center gap-2">
    <button
      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      :class="liked ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100' : 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-100'"
      @click="toggle"
      :disabled="loading"
      :aria-label="`${liked ? 'Unlike' : 'Like'} this ${type} (${count} likes)`"
    >
      <svg v-if="loading" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0a12 12 0 00-8 20.784L6 18.137A7.96 7.96 0 014 12z" />
      </svg>
      <svg v-else class="w-4 h-4" :class="liked ? 'fill-current' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
      <span>{{ count }}</span>
    </button>
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>
