<script>
import { ref } from 'vue'

export default {
  name: 'LikeButton',
  props: {
    type: { type: String, required: true },
    id: { type: Number, required: true },
    initialCount: { type: Number, default: 0 },
    initiallyLiked: { type: Boolean, default: false },
  },
  setup(props){
    const count = ref(props.initialCount)
    const liked = ref(props.initiallyLiked)
    const loading = ref(false)

    const toggle = async () => {
      if (loading.value) return
      loading.value = true
      try {
        const res = await fetch('/likes/toggle', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: JSON.stringify({ type: props.type, id: props.id }),
        })
        const data = await res.json()
        liked.value = data.liked
        count.value = data.count
      } catch (e) {
        console.error(e)
        alert('লাইক করতে সমস্যা হয়েছে!')
      } finally {
        loading.value = false
      }
    }

    return { count, liked, toggle, loading }
  }
}
</script>

<template>
  <button class="btn btn-sm" :class="liked ? 'btn-danger' : 'btn-outline-danger'" @click="toggle" :disabled="loading">
    ♥ {{ count }}
  </button>
</template>
